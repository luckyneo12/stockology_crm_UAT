<?php

namespace Workdo\Lead\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use Workdo\Lead\Entities\FacebookLeadData;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Entities\LeadCustomFieldValue;

class FacebookFetchLeadsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'facebook:fetch-leads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically fetch new leads from Facebook Lead Ads for all configured forms/pages';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Facebook Leads Auto Fetch...');

        // Retrieve Facebook Lead Settings rules across all workspaces
        $settings = Setting::where('key', 'facebook_lead_integration_settings')->get();
        if ($settings->isEmpty()) {
            $this->info('No Facebook Lead configurations found.');
            return 0;
        }

        foreach ($settings as $setting) {
            $workspaceId = $setting->workspace;
            $createdBy = $setting->created_by ?: 1;
            $rules = json_decode($setting->value, true) ?: [];

            foreach ($rules as $rule) {
                $ruleId = $rule['id'] ?? null;
                $pageId = $rule['page_id'] ?? null;
                $formId = $rule['form_id'] ?? null;
                $accessToken = $rule['page_access_token'] ?? null;
                $autoFetch = !empty($rule['auto_fetch']);

                if (!$autoFetch) {
                    $this->info("Skipping rule ID: " . ($ruleId ?? 'unknown') . " (Auto-Fetch not enabled)");
                    continue;
                }

                if (empty($ruleId) || empty($pageId) || empty($formId) || empty($accessToken)) {
                    $this->warn("Skipping incomplete rule ID: " . ($ruleId ?? 'unknown') . " in workspace: " . $workspaceId);
                    continue;
                }

                $this->info("Fetching leads for Page: " . ($rule['page_name'] ?? $pageId) . " | Form: " . $formId);

                try {
                    $nextUrl = "https://graph.facebook.com/v20.0/{$formId}/leads?fields=id,created_time,field_data&access_token=" . urlencode($accessToken) . "&limit=100";

                    $response = Http::get($nextUrl);
                    if (!$response->successful()) {
                        $errorData = $response->json();
                        $errorMsg = $errorData['error']['message'] ?? 'Failed to download leads from Facebook API.';
                        $this->error("API Error: " . $errorMsg);
                        Log::error("FacebookFetchLeadsCommand API Error: " . $errorMsg);
                        continue;
                    }

                    $leadsData = $response->json();
                    $pageLeads = $leadsData['data'] ?? [];

                    $syncedCount = 0;
                    $skippedCount = 0;
                    $alreadyLoggedCount = 0;
                    $failedCount = 0;

                    foreach ($pageLeads as $fbLead) {
                        $leadgenId = $fbLead['id'];

                        // Check if already processed in logs
                        $alreadyProcessed = FacebookLeadData::where('leadgen_id', $leadgenId)
                            ->where('workspace_id', $workspaceId)
                            ->whereIn('status', ['converted', 'skipped'])
                            ->exists();

                        if ($alreadyProcessed) {
                            $alreadyLoggedCount++;
                            continue;
                        }

                        // Parse field_data array into flat key-value pairs
                        $fieldData = $fbLead['field_data'] ?? [];
                        $flatPayload = [];
                        foreach ($fieldData as $field) {
                            $fieldName = $field['name'];
                            $fieldValue = implode(', ', $field['values'] ?? []);
                            $flatPayload[$fieldName] = $fieldValue;
                        }
                        $flatPayload['id'] = $leadgenId;
                        $flatPayload['created_time'] = $fbLead['created_time'] ?? date('Y-m-d H:i:s');

                        // Create or Update Log record
                        $log = FacebookLeadData::updateOrCreate([
                            'leadgen_id' => $leadgenId,
                            'workspace_id' => $workspaceId,
                        ], [
                            'rule_id' => $ruleId,
                            'page_id' => $pageId,
                            'form_id' => $formId,
                            'payload' => $flatPayload,
                            'status' => 'pending',
                            'assigned_user_id' => $rule['user_id'] ?? null,
                        ]);

                        // Extract Fields using Mapping
                        $mapping = $rule['field_mapping'] ?? [];
                        $leadName = $flatPayload[$mapping['name'] ?? ''] ?? $flatPayload['full_name'] ?? $flatPayload['name'] ?? 'Facebook Lead ' . $leadgenId;
                        $leadEmail = $flatPayload[$mapping['email'] ?? ''] ?? $flatPayload['email'] ?? '';
                        $phone = $flatPayload[$mapping['phone'] ?? ''] ?? $flatPayload['phone'] ?? $flatPayload['phone_number'] ?? '';
                        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);

                        // Validations
                        $validationFailed = false;
                        $errorMsg = '';

                        // Validate phone
                        if (empty($cleanedPhone)) {
                            $validationFailed = true;
                            $errorMsg = 'Missing phone number.';
                        }

                        if ($validationFailed) {
                            $log->status = 'failed';
                            $log->error_reason = $errorMsg;
                            $log->save();
                            $failedCount++;
                            continue;
                        }

                        // Check if lead already exists in CRM
                        $existingLead = null;
                        if (!empty($cleanedPhone)) {
                            $existingLead = Lead::where('phone', $cleanedPhone)
                                ->where('workspace_id', $workspaceId)
                                ->first();
                        }
                        if (!$existingLead && !empty($leadEmail)) {
                            $existingLead = Lead::where('email', $leadEmail)
                                ->where('workspace_id', $workspaceId)
                                ->first();
                        }

                        if ($existingLead) {
                            try {
                                // Update existing Lead
                                $existingLead->name = $leadName;
                                if (!empty($leadEmail)) {
                                    $existingLead->email = $leadEmail;
                                }
                                if (!empty($cleanedPhone)) {
                                    $existingLead->phone = $cleanedPhone;
                                }
                                $existingLead->subject = $flatPayload[$mapping['subject'] ?? ''] ?? 'Meta Lead: ' . $leadName;
                                $existingLead->pipeline_id = $rule['pipeline_id'];
                                $existingLead->stage_id = $rule['stage_id'];
                                
                                // Append Notes
                                $notes = "\n\n[Updated via Facebook Lead Ad - " . date('Y-m-d H:i:s') . "]\n";
                                $notes .= "Lead ID: " . $leadgenId . "\n";
                                $notes .= "Form ID: " . $formId . "\n";
                                foreach ($flatPayload as $k => $v) {
                                    if ($k !== 'id' && $k !== 'created_time') {
                                        $notes .= "- " . ucwords(str_replace('_', ' ', $k)) . ": " . $v . "\n";
                                    }
                                }
                                $existingLead->notes .= $notes;
                                $existingLead->save();

                                // Map Custom Fields
                                if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                                    foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                                        if (!empty($jsonKey) && isset($flatPayload[$jsonKey])) {
                                            LeadCustomFieldValue::updateOrCreate([
                                                'lead_id' => $existingLead->id,
                                                'field_id' => $fieldId,
                                            ], [
                                                'value' => $flatPayload[$jsonKey],
                                            ]);
                                        }
                                    }
                                }

                                // Log activity
                                $existingLead->activities()->create([
                                    'user_id' => $rule['user_id'] ?? $createdBy,
                                    'log_type' => 'Lead Updated',
                                    'remark' => json_encode(['message' => __('Lead details updated via Facebook integration.')]),
                                ]);

                                // Update log status to skipped since it already exists in CRM
                                $log->status = 'skipped';
                                $log->error_reason = __('Lead already exists in CRM (Details updated).');
                                $log->save();

                                event(new \Workdo\Lead\Events\CreateLead(request(), $existingLead));
                                $skippedCount++;

                            } catch (\Exception $ex) {
                                $log->status = 'failed';
                                $log->error_reason = 'Update error: ' . $ex->getMessage();
                                $log->save();
                                $failedCount++;
                            }
                            continue;
                        }

                        // Create Lead
                        try {
                            $lead = new Lead();
                            $lead->name = $leadName;
                            $lead->email = $leadEmail;
                            $lead->phone = $cleanedPhone;
                            $lead->subject = $flatPayload[$mapping['subject'] ?? ''] ?? 'Meta Lead: ' . $leadName;
                            $lead->pipeline_id = $rule['pipeline_id'];
                            $lead->stage_id = $rule['stage_id'];
                            
                            $lead->created_by = $rule['user_id'] ?? $createdBy;
                            $lead->date = date('Y-m-d');
                            $lead->workspace_id = $workspaceId;

                            // Resolve Source
                            $sourceId = $rule['source_id'] ?? null;
                            if (empty($sourceId)) {
                                $source = \Workdo\Lead\Entities\Source::firstOrCreate(
                                    ['name' => 'Facebook', 'workspace_id' => $workspaceId],
                                    ['created_by' => $lead->created_by]
                                );
                                $sourceId = $source->id;
                            }
                            $lead->sources = $sourceId;

                            // Compile Notes
                            $notes = "Facebook Lead Ad Details:\n";
                            $notes .= "Lead ID: " . $leadgenId . "\n";
                            $notes .= "Form ID: " . $formId . "\n";
                            $notes .= "Page ID: " . $pageId . "\n\n";
                            foreach ($flatPayload as $k => $v) {
                                if ($k !== 'id' && $k !== 'created_time') {
                                    $notes .= "- " . ucwords(str_replace('_', ' ', $k)) . ": " . $v . "\n";
                                }
                            }
                            $lead->notes = $notes;
                            $lead->save();

                            // Map Custom Fields
                            if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                                foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                                    if (!empty($jsonKey) && isset($flatPayload[$jsonKey])) {
                                        LeadCustomFieldValue::updateOrCreate([
                                            'lead_id' => $lead->id,
                                            'field_id' => $fieldId,
                                        ], [
                                            'value' => $flatPayload[$jsonKey],
                                        ]);
                                    }
                                }
                            }

                            // Assign User
                            UserLead::create([
                                'user_id' => $lead->created_by,
                                'lead_id' => $lead->id,
                            ]);

                            // Log activity
                            $lead->activities()->create([
                                'user_id' => $lead->created_by,
                                'log_type' => 'Lead Created',
                                'remark' => json_encode(['message' => __('Lead automatically imported via Facebook schedule.')]),
                            ]);

                            // Update log status
                            $log->status = 'converted';
                            $log->save();

                            // Fire Laravel Lead event
                            event(new \Workdo\Lead\Events\CreateLead(request(), $lead));
                            $syncedCount++;

                        } catch (\Exception $ex) {
                            $log->status = 'failed';
                            $log->error_reason = 'Creation error: ' . $ex->getMessage();
                            $log->save();
                            $failedCount++;
                        }
                    }

                    $this->info("Completed. Synced: {$syncedCount}, Already in logs: {$alreadyLoggedCount}, Skipped: {$skippedCount}, Failed: {$failedCount}");

                } catch (\Exception $ex) {
                    $this->error("Error syncing historical: " . $ex->getMessage());
                    Log::error("FacebookFetchLeadsCommand Exception: " . $ex->getMessage());
                }
            }
        }

        $this->info('Facebook Leads Auto Fetch completed.');
        return 0;
    }
}
