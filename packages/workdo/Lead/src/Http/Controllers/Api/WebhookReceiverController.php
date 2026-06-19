<?php

namespace Workdo\Lead\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WebhookEndpoint;
use Workdo\Lead\Entities\WebhookData;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Events\CreateLead;
use Illuminate\Support\Facades\Auth;

class WebhookReceiverController extends Controller
{
    public function store(Request $request, $url)
    {
        $endpoint = WebhookEndpoint::where('url', $url)->first();

        if (!$endpoint) {
            return response()->json([
                'status' => 'error',
                'message' => 'Webhook URL not found.'
            ], 404);
        }

        $payload = $request->all();
        $mapping = $endpoint->field_mapping ?? [];

        // Extract phone number using the mapping
        $phone = $payload[$mapping['phone'] ?? ''] ?? $payload['phone'] ?? $payload['mobile'] ?? '';
        $cleanedPhone = preg_replace('/[^0-9]/', '', $phone);

        // Validation checks
        $status = 'pending';
        $errorReason = null;

        // 1. Check number length (must be exactly 10 digits)
        if (empty($cleanedPhone) || strlen($cleanedPhone) !== 10) {
            $status = 'failed';
            $errorReason = 'Invalid phone number (must be exactly 10 digits).';
        }
        // 2. Check duplicate phone number in current workspace
        else {
            $existingLead = Lead::where('phone', $cleanedPhone)
                ->where('workspace_id', $endpoint->workspace_id)
                ->first();
            if ($existingLead) {
                $status = 'failed';
                $errorReason = 'Duplicate phone number found in database.';

                // Send notification to the user(s) who hold the existing lead
                try {
                    $userIds = [];
                    if (!empty($existingLead->user_id)) {
                        $userIds[] = $existingLead->user_id;
                    }
                    if ($existingLead->users) {
                        $userIds = array_merge($userIds, $existingLead->users->pluck('id')->toArray());
                    }
                    $userIds = array_unique(array_filter($userIds));

                    foreach ($userIds as $userId) {
                        \App\Models\UserNotification::create([
                            'user_id' => $userId,
                            'type' => 'duplicate_webhook_lead',
                            'data' => [
                                'lead_id' => $existingLead->id,
                                'lead_name' => $existingLead->name,
                                'message' => "An incoming duplicate webhook lead attempt with phone {$cleanedPhone} was blocked. You are the current assignee/owner of this lead.",
                                'url' => route('leads.show', $existingLead->id),
                            ],
                            'is_read' => 0,
                            'workspace_id' => $endpoint->workspace_id,
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send duplicate webhook lead notification: ' . $e->getMessage());
                }
            }
        }

        if ($errorReason) {
            $payload['_error_reason'] = $errorReason;
        }

        $webhookData = WebhookData::create([
            'webhook_endpoint_id' => $endpoint->id,
            'payload' => $payload,
            'status' => $status,
            'assigned_user_id' => $endpoint->assign_to,
            'workspace_id' => $endpoint->workspace_id,
        ]);

        // If validation passes and auto_convert is enabled, automatically convert to Lead!
        if ($status === 'pending' && (!isset($endpoint->auto_convert) || $endpoint->auto_convert == 1)) {
            try {
                $lead = new Lead();

                // Map standard fields
                $lead->name = $payload[$mapping['name'] ?? ''] ?? $payload['name'] ?? $payload['title'] ?? 'Lead from Webhook ' . $endpoint->name;
                $lead->email = $payload[$mapping['email'] ?? ''] ?? $payload['email'] ?? '';
                $lead->phone = $cleanedPhone;
                $lead->subject = $payload[$mapping['subject'] ?? ''] ?? $payload['subject'] ?? 'Webhook Lead';

                $lead->pipeline_id = $endpoint->pipeline_id;
                $lead->stage_id = $endpoint->stage_id;
                if (!empty($endpoint->source_id)) {
                    $lead->sources = (string)$endpoint->source_id;
                }
                $lead->created_by = $endpoint->created_by ?? 1; // Fallback to creator of endpoint
                $lead->date = date('Y-m-d');
                $lead->workspace_id = $endpoint->workspace_id;
                $lead->save();

                // Handle Custom Fields Mapping
                if (isset($mapping['custom']) && is_array($mapping['custom'])) {
                    foreach ($mapping['custom'] as $fieldId => $jsonKey) {
                        if (!empty($jsonKey) && isset($payload[$jsonKey])) {
                            \Workdo\Lead\Entities\LeadCustomFieldValue::updateOrCreate([
                                'lead_id' => $lead->id,
                                'field_id' => $fieldId,
                            ], [
                                'value' => $payload[$jsonKey],
                            ]);
                        }
                    }
                }

                // Assign User to Lead
                $assignToId = $endpoint->assign_to;
                if ($assignToId) {
                    UserLead::create([
                        'user_id' => $assignToId,
                        'lead_id' => $lead->id,
                    ]);
                }

                // Update webhook data status
                $webhookData->status = 'converted';
                $webhookData->save();

                // Fire Laravel CreateLead event
                event(new CreateLead($request, $lead));

            } catch (\Exception $e) {
                // In case of automatic creation failure, fallback to failed status
                $payload['_error_reason'] = 'Automatic lead creation error: ' . $e->getMessage();
                $webhookData->payload = $payload;
                $webhookData->status = 'failed';
                $webhookData->save();
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => $webhookData->status === 'converted' 
                ? 'Data processed and lead created successfully.' 
                : ($webhookData->status === 'pending'
                    ? 'Data received and logged successfully for manual review.'
                    : 'Data logged but validation failed: ' . ($errorReason ?? $webhookData->payload['_error_reason'] ?? 'Unknown error'))
        ], 200);
    }
}
