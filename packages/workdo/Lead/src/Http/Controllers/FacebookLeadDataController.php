<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\FacebookLeadData;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Entities\LeadCustomField;
use Workdo\Lead\Entities\LeadCustomFieldValue;
use App\Models\User;
use App\Models\Setting;
use Workdo\Lead\Events\CreateLead;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookLeadDataController extends Controller
{
    private function isCompany()
    {
        $user = Auth::user();
        return $user->type == 'company' || $user->type == 'super admin';
    }

    private function getRulesMap()
    {
        $setting = Setting::where('key', 'facebook_lead_integration_settings')
            ->where('workspace', getActiveWorkSpace())
            ->first();
        $rules = $setting ? json_decode($setting->value, true) : [];
        
        $map = [];
        if (is_array($rules)) {
            foreach ($rules as $r) {
                if (isset($r['id'])) {
                    $map[$r['id']] = $r;
                }
            }
        }
        return $map;
    }

    public function index(Request $request)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return redirect()->route('dashboard')->with('error', __('Permission denied.'));
        }

        $query = FacebookLeadData::where('workspace_id', getActiveWorkSpace());

        // Scope query by user hierarchy/accessibility if not company or admin
        if (Auth::user()->type !== 'company' && Auth::user()->type !== 'super admin') {
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            $query->where(function($q) use ($accessibleUserIds) {
                $q->whereIn('assigned_user_id', $accessibleUserIds)
                  ->orWhereNull('assigned_user_id');
            });
        }

        // Apply filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('rule_id')) {
            $query->where('rule_id', $request->rule_id);
        }

        $logs = $query->orderBy('id', 'DESC')->paginate(15);

        $rules = $this->getRulesMap();
        $ruleOptions = [];
        foreach ($rules as $id => $rule) {
            $ruleOptions[$id] = $rule['page_name'] ?? ('Page ID: ' . $rule['page_id']);
        }

        return view('lead::facebook_lead_data.index', compact('logs', 'ruleOptions', 'rules'));
    }

    public function payload($id)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return response()->json(['error' => __('Permission denied.')], 401);
        }

        $log = FacebookLeadData::find($id);
        if ($log && $log->workspace_id == getActiveWorkSpace()) {
            if (Auth::user()->type !== 'company' && Auth::user()->type !== 'super admin') {
                $accessibleUserIds = Auth::user()->getAccessibleUserIds();
                if (!in_array($log->assigned_user_id, $accessibleUserIds)) {
                    return response()->json(['error' => __('Permission denied.')], 403);
                }
            }
            return view('lead::facebook_lead_data.payload', compact('log'));
        }
        return response()->json(['error' => __('Log entry not found.')], 404);
    }

    public function convertToLead(Request $request, $id)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $log = FacebookLeadData::find($id);
        if (!$log || $log->workspace_id != getActiveWorkSpace()) {
            return redirect()->back()->with('error', __('Log entry not found.'));
        }

        if (Auth::user()->type !== 'company' && Auth::user()->type !== 'super admin') {
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            if (!in_array($log->assigned_user_id, $accessibleUserIds)) {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        }

        $rules = $this->getRulesMap();
        $rule = $rules[$log->rule_id] ?? null;

        if (!$rule) {
            return redirect()->back()->with('error', __('Corresponding Facebook Feed Configuration was not found.'));
        }

        $payload = $log->payload;
        $mapping = $rule['field_mapping'] ?? [];

        try {
            $lead = new Lead();

            // Map standard fields or fallback
            $lead->name = $payload[$mapping['name'] ?? ''] ?? $payload['full_name'] ?? $payload['name'] ?? 'Facebook Lead ' . $log->leadgen_id;
            $lead->email = $payload[$mapping['email'] ?? ''] ?? $payload['email'] ?? '';
            
            $phone = $payload[$mapping['phone'] ?? ''] ?? $payload['phone'] ?? $payload['phone_number'] ?? '';
            $lead->phone = preg_replace('/[^0-9]/', '', $phone);

            $lead->subject = $payload[$mapping['subject'] ?? ''] ?? 'Meta Lead: ' . $lead->name;

            $lead->pipeline_id = $rule['pipeline_id'];
            $lead->stage_id = $rule['stage_id'];
            
            $createdBy = Auth::user()->id;
            $lead->created_by = $createdBy;
            $lead->date = date('Y-m-d');
            $lead->workspace_id = getActiveWorkSpace();

            // Resolve Lead Source
            $sourceId = $rule['source_id'] ?? null;
            if (empty($sourceId)) {
                $source = \Workdo\Lead\Entities\Source::firstOrCreate(
                    ['name' => 'Facebook', 'workspace_id' => getActiveWorkSpace()],
                    ['created_by' => $createdBy]
                );
                $sourceId = $source->id;
            }
            $lead->sources = $sourceId;

            // Compile Notes
            $notes = "Facebook Lead Ad Details:\n";
            $notes .= "Lead ID: " . $log->leadgen_id . "\n";
            $notes .= "Form ID: " . $log->form_id . "\n";
            $notes .= "Page ID: " . $log->page_id . "\n\n";
            foreach ($payload as $k => $v) {
                if ($k !== 'id' && $k !== 'created_time') {
                    $notes .= "- " . ucwords(str_replace('_', ' ', $k)) . ": " . $v . "\n";
                }
            }
            $lead->notes = $notes;
            $lead->save();

            // Handle Custom Fields Mapping
            if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                    if (!empty($jsonKey) && isset($payload[$jsonKey])) {
                        LeadCustomFieldValue::updateOrCreate([
                            'lead_id' => $lead->id,
                            'field_id' => $fieldId,
                        ], [
                            'value' => $payload[$jsonKey],
                        ]);
                    }
                }
            }

            // Assign User to Lead
            $assignToId = $rule['user_id'] ?? $createdBy;
            UserLead::create([
                'user_id' => $assignToId,
                'lead_id' => $lead->id,
            ]);

            // Log activity
            $lead->activities()->create([
                'user_id' => $createdBy,
                'log_type' => 'Lead Created',
                'remark' => json_encode(['message' => __('Lead manually imported from Facebook Lead Log.')]),
            ]);

            // Update log status
            $log->status = 'converted';
            $log->error_reason = null;
            $log->save();

            event(new CreateLead($request, $lead));

            return redirect()->back()->with('success', __('Lead successfully created from Facebook lead log!'));

        } catch (\Exception $e) {
            $log->status = 'failed';
            $log->error_reason = 'Manual conversion error: ' . $e->getMessage();
            $log->save();

            return redirect()->back()->with('error', __('Manual conversion failed: ') . $e->getMessage());
        }
    }

    public function syncHistorical(Request $request, $ruleId)
    {
        if (!Auth::user()->isAbleTo('crm manage')) {
            return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
        }

        $rules = $this->getRulesMap();
        $rule = $rules[$ruleId] ?? null;

        if (!$rule) {
            return response()->json(['success' => false, 'message' => __('Integration rule not found.')], 404);
        }

        if (Auth::user()->type !== 'company' && Auth::user()->type !== 'super admin') {
            $accessibleUserIds = Auth::user()->getAccessibleUserIds();
            if (!in_array($rule['user_id'], $accessibleUserIds)) {
                return response()->json(['success' => false, 'message' => __('Permission denied.')], 403);
            }
        }

        $pageId = $rule['page_id'];
        $formId = $rule['form_id'];
        $accessToken = $rule['page_access_token'];

        if (empty($formId)) {
            return response()->json(['success' => false, 'message' => __('Form ID is required for historical sync. Please configure a Form ID in mapping.')], 400);
        }

        // Disable php execution time limit to allow complete sync of all historical leads
        @set_time_limit(0);

        try {
            $leadsList = [];
            $nextUrl = "https://graph.facebook.com/v20.0/{$formId}/leads?fields=id,created_time,field_data&access_token=" . urlencode($accessToken) . "&limit=100";

            while (!empty($nextUrl)) {
                $response = Http::get($nextUrl);
                if (!$response->successful()) {
                    $errorData = $response->json();
                    $errorMsg = $errorData['error']['message'] ?? __('Failed to download leads from Facebook API.');
                    return response()->json(['success' => false, 'message' => $errorMsg], 400);
                }

                $leadsData = $response->json();
                $pageLeads = $leadsData['data'] ?? [];
                $leadsList = array_merge($leadsList, $pageLeads);

                $nextUrl = $leadsData['paging']['next'] ?? null;
            }

            $syncedCount = 0;
            $skippedCount = 0;
            $alreadyLoggedCount = 0;
            $failedCount = 0;

            foreach ($leadsList as $fbLead) {
                $leadgenId = $fbLead['id'];

                // Check if already exists in logs and is converted or skipped
                $alreadyProcessed = FacebookLeadData::where('leadgen_id', $leadgenId)
                    ->where('workspace_id', getActiveWorkSpace())
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
                    'workspace_id' => getActiveWorkSpace(),
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
                        ->where('workspace_id', getActiveWorkSpace())
                        ->first();
                }
                if (!$existingLead && !empty($leadEmail)) {
                    $existingLead = Lead::where('email', $leadEmail)
                        ->where('workspace_id', getActiveWorkSpace())
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
                            'user_id' => Auth::user()->id,
                            'log_type' => 'Lead Updated',
                            'remark' => json_encode(['message' => __('Lead details updated via Facebook integration.')]),
                        ]);

                        // Update log status to skipped since it already exists in CRM
                        $log->status = 'skipped';
                        $log->error_reason = __('Lead already exists in CRM (Details updated).');
                        $log->save();

                        event(new CreateLead($request, $existingLead));
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
                    
                    $createdBy = $rule['user_id'] ?? Auth::user()->id;
                    $lead->created_by = $createdBy;
                    $lead->date = date('Y-m-d');
                    $lead->workspace_id = getActiveWorkSpace();

                    // Resolve Source
                    $sourceId = $rule['source_id'] ?? null;
                    if (empty($sourceId)) {
                        $source = \Workdo\Lead\Entities\Source::firstOrCreate(
                            ['name' => 'Facebook', 'workspace_id' => getActiveWorkSpace()],
                            ['created_by' => $createdBy]
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
                        'user_id' => $createdBy,
                        'lead_id' => $lead->id,
                    ]);

                    // Log activity
                    $lead->activities()->create([
                        'user_id' => Auth::user()->id,
                        'log_type' => 'Lead Created',
                        'remark' => json_encode(['message' => __('Lead imported via Facebook sync.')]),
                    ]);

                    // Update log status
                    $log->status = 'converted';
                    $log->save();

                    event(new CreateLead($request, $lead));
                    $syncedCount++;

                } catch (\Exception $ex) {
                    $log->status = 'failed';
                    $log->error_reason = 'Creation error: ' . $ex->getMessage();
                    $log->save();
                    $failedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => sprintf(__('Sync completed. Synced: %d, Already in logs: %d, Skipped: %d, Failed: %d'), $syncedCount, $alreadyLoggedCount, $skippedCount, $failedCount)
            ]);

        } catch (\Exception $ex) {
            return response()->json(['success' => false, 'message' => $ex->getMessage()], 500);
        }
    }
}
