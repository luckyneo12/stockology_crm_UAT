<?php

namespace App\Listeners;

use App\Events\CreateMetaWebhook;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MetaWebhookListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CreateMetaWebhook $event): void
    {
        $payload = $event->payload;
        Log::info('MetaWebhookListener received payload: ' . json_encode($payload));

        if (!isset($payload['object']) || $payload['object'] !== 'page' || !isset($payload['entry'])) {
            return;
        }

        // Retrieve Facebook Lead Settings rules across all workspaces
        $settings = \App\Models\Setting::where('key', 'facebook_lead_integration_settings')->get();
        if ($settings->isEmpty()) {
            Log::info('MetaWebhookListener: No Facebook Lead configurations found.');
            return;
        }

        foreach ($payload['entry'] as $entry) {
            if (!isset($entry['changes'])) {
                continue;
            }

            foreach ($entry['changes'] as $change) {
                if (!isset($change['field']) || $change['field'] !== 'leadgen' || !isset($change['value'])) {
                    continue;
                }

                $value = $change['value'];
                $pageId = $value['page_id'] ?? null;
                $formId = $value['form_id'] ?? null;
                $leadgenId = $value['leadgen_id'] ?? null;

                if (!$leadgenId || !$pageId) {
                    Log::warning('MetaWebhookListener: Missing leadgen_id or page_id in webhook change payload.');
                    continue;
                }

                // Match against our rules
                foreach ($settings as $setting) {
                    $rules = json_decode($setting->value, true) ?: [];
                    foreach ($rules as $rule) {
                        // Match Page ID
                        if (trim($rule['page_id']) !== trim($pageId)) {
                            continue;
                        }

                        // Match Form ID if form_id is configured in the rule
                        if (!empty($rule['form_id']) && trim($rule['form_id']) !== trim($formId)) {
                            continue;
                        }

                        $accessToken = $rule['page_access_token'] ?? null;
                        if (!$accessToken) {
                            Log::warning("MetaWebhookListener: Access token not set for Page ID {$pageId} in workspace {$setting->workspace}");
                            continue;
                        }

                        $workspaceId = $setting->workspace;
                        $createdBy = $setting->created_by ?: 1;

                        // Check if already exists and is converted or skipped
                        $alreadyProcessed = \Workdo\Lead\Entities\FacebookLeadData::where('leadgen_id', $leadgenId)
                            ->where('workspace_id', $workspaceId)
                            ->whereIn('status', ['converted', 'skipped'])
                            ->exists();
                        if ($alreadyProcessed) {
                            Log::info("MetaWebhookListener: Lead {$leadgenId} already processed (converted/skipped). Skipping.");
                            continue;
                        }

                        // Create or Update Log record early in case subsequent Graph API request fails
                        $log = \Workdo\Lead\Entities\FacebookLeadData::updateOrCreate([
                            'leadgen_id' => $leadgenId,
                            'workspace_id' => $workspaceId,
                        ], [
                            'rule_id' => $rule['id'],
                            'page_id' => $pageId,
                            'form_id' => $formId,
                            'payload' => ['id' => $leadgenId],
                            'status' => 'pending',
                            'assigned_user_id' => $rule['user_id'] ?? null,
                        ]);

                        Log::info("MetaWebhookListener: Matched integration rule. Fetching lead details for Leadgen ID {$leadgenId}");

                        // Fetch lead details from Facebook Graph API
                        $graphUrl = "https://graph.facebook.com/v20.0/{$leadgenId}";
                        try {
                            $response = Http::get($graphUrl, [
                                'access_token' => $accessToken
                            ]);

                            if (!$response->successful()) {
                                $errorBody = $response->body();
                                Log::error("MetaWebhookListener Graph API Error for Lead {$leadgenId}: " . $errorBody);
                                $log->status = 'failed';
                                $log->error_reason = 'Facebook API Error: ' . $errorBody;
                                $log->save();
                                continue;
                            }

                            $leadData = $response->json();
                            $fieldData = $leadData['field_data'] ?? [];

                            // Build flat payload for logging and mapping
                            $flatPayload = [];
                            foreach ($fieldData as $field) {
                                $fieldName = $field['name'];
                                $fieldValue = implode(', ', $field['values'] ?? []);
                                $flatPayload[$fieldName] = $fieldValue;
                            }
                            $flatPayload['id'] = $leadgenId;
                            $flatPayload['created_time'] = $leadData['created_time'] ?? date('Y-m-d H:i:s');

                            // Update log record payload
                            $log->payload = $flatPayload;
                            $log->save();

                            // Extract fields using mapping
                            $mapping = $rule['field_mapping'] ?? [];
                            $leadName = $flatPayload[$mapping['name'] ?? ''] ?? $flatPayload['full_name'] ?? $flatPayload['name'] ?? 'Facebook Lead ' . $leadgenId;
                            $leadEmail = $flatPayload[$mapping['email'] ?? ''] ?? $flatPayload['email'] ?? '';
                            $phone = $flatPayload[$mapping['phone'] ?? ''] ?? $flatPayload['phone'] ?? $flatPayload['phone_number'] ?? '';
                            $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);
                            $leadSubject = $flatPayload[$mapping['subject'] ?? ''] ?? 'Meta Lead: ' . $leadName;

                            // Validations
                            $validationFailed = false;
                            $errorMsg = '';

                            if (empty($cleanedPhone)) {
                                $validationFailed = true;
                                $errorMsg = 'Missing phone number.';
                            }

                            if ($validationFailed) {
                                $log->status = 'failed';
                                $log->error_reason = $errorMsg;
                                $log->save();
                                Log::info("MetaWebhookListener: Validation failed for Facebook Lead {$leadgenId}: {$errorMsg}");
                                continue;
                            }

                            // Check if lead already exists in CRM (update if exists, otherwise create)
                            $existingLead = null;
                            if (!empty($cleanedPhone)) {
                                $existingLead = \Workdo\Lead\Entities\Lead::where('phone', $cleanedPhone)
                                    ->where('workspace_id', $workspaceId)
                                    ->first();
                            }
                            if (!$existingLead && !empty($leadEmail)) {
                                $existingLead = \Workdo\Lead\Entities\Lead::where('email', $leadEmail)
                                    ->where('workspace_id', $workspaceId)
                                    ->first();
                            }

                            if ($existingLead) {
                                // Update existing Lead
                                $existingLead->name = $leadName;
                                if (!empty($leadEmail)) {
                                    $existingLead->email = $leadEmail;
                                }
                                if (!empty($cleanedPhone)) {
                                    $existingLead->phone = $cleanedPhone;
                                }
                                $existingLead->subject = $leadSubject;
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

                                // Update Custom Fields
                                if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                                    foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                                        if (!empty($jsonKey) && isset($flatPayload[$jsonKey])) {
                                            \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate([
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
                                continue;
                            }

                            // Resolve Lead Source
                            $sourceId = $rule['source_id'] ?? null;
                            if (empty($sourceId)) {
                                $source = \Workdo\Lead\Entities\Source::firstOrCreate(
                                    ['name' => 'Facebook', 'workspace_id' => $workspaceId],
                                    ['created_by' => $createdBy]
                                );
                                $sourceId = $source->id;
                            }

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

                            // Create Lead
                            $lead = \Workdo\Lead\Entities\Lead::create([
                                'subject' => $leadSubject,
                                'name' => $leadName,
                                'user_id' => $rule['user_id'] ?? $createdBy,
                                'email' => $leadEmail,
                                'phone' => $cleanedPhone,
                                'pipeline_id' => $rule['pipeline_id'],
                                'stage_id' => $rule['stage_id'],
                                'sources' => $sourceId,
                                'notes' => $notes,
                                'created_by' => $createdBy,
                                'workspace_id' => $workspaceId,
                                'date' => date('Y-m-d')
                            ]);

                            // Map Custom Fields
                            if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                                foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                                    if (!empty($jsonKey) && isset($flatPayload[$jsonKey])) {
                                        \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate([
                                            'lead_id' => $lead->id,
                                            'field_id' => $fieldId,
                                        ], [
                                            'value' => $flatPayload[$jsonKey],
                                        ]);
                                    }
                                }
                            }

                            // Assign User
                            \Workdo\Lead\Entities\UserLead::firstOrCreate([
                                'user_id' => $rule['user_id'] ?? $createdBy,
                                'lead_id' => $lead->id
                            ]);

                            // Log activity
                            $lead->activities()->create([
                                'user_id' => $rule['user_id'] ?? $createdBy,
                                'log_type' => 'Lead Created',
                                'remark' => json_encode(['message' => __('Lead imported via Facebook Lead Ad integration.')]),
                            ]);

                            // Update log status
                            $log->status = 'converted';
                            $log->save();

                            Log::info("MetaWebhookListener: Successfully created Lead ID {$lead->id} in Workspace {$workspaceId} mapped to Pipeline {$rule['pipeline_id']}, Stage {$rule['stage_id']}");

                        } catch (\Exception $ex) {
                            Log::error("MetaWebhookListener error processing lead retrieval: " . $ex->getMessage());
                            if (isset($log)) {
                                $log->status = 'failed';
                                $log->error_reason = 'Listener error: ' . $ex->getMessage();
                                $log->save();
                            }
                        }
                    }
                }
            }
        }
    }
}
