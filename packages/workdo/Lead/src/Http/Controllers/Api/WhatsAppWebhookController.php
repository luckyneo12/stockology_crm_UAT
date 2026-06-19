<?php

namespace Workdo\Lead\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WhatsAppConfig;
use Workdo\Lead\Entities\WhatsAppChat;
use Workdo\Lead\Entities\WhatsAppMessage;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\UserLead;
use Workdo\Lead\Entities\Source;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function handleIncoming(Request $request)
    {
        Log::info('WhatsAppWebhookController received payload: ' . json_encode($request->all()));

        $entry = $request->input('entry.0');
        if (!$entry) {
            return response()->json(['status' => 'error', 'message' => 'Invalid webhook entry data.'], 400);
        }

        $change = $entry['changes'][0]['value'] ?? null;
        if (!$change || !isset($change['messages'][0])) {
            return response()->json(['status' => 'success', 'message' => 'No messages in payload.'], 200);
        }

        $metadata = $change['metadata'] ?? [];
        $phoneId = $metadata['phone_number_id'] ?? null;

        if (!$phoneId) {
            return response()->json(['status' => 'error', 'message' => 'Missing phone_number_id in payload.'], 400);
        }

        // 1. Resolve configuration
        $config = WhatsAppConfig::where('phone_number_id', $phoneId)->first();
        if (!$config) {
            Log::warning("WhatsApp Webhook: No configuration found for Phone Number ID {$phoneId}");
            return response()->json(['status' => 'error', 'message' => 'Configuration not found.'], 404);
        }

        $messageData = $change['messages'][0];
        $customerPhone = $messageData['from'];
        
        // Clean customer phone: numeric only
        $cleanedPhone = preg_replace('/[^0-9]/', '', $customerPhone);
        
        $contact = $change['contacts'][0] ?? null;
        $customerName = $contact['profile']['name'] ?? $cleanedPhone;

        $msgSid = $messageData['id'] ?? null;
        $msgType = $messageData['type'] ?? 'text';
        $body = '';

        if ($msgType === 'text') {
            $body = $messageData['text']['body'] ?? '';
        } else {
            $body = '[' . ucfirst($msgType) . ' message]';
        }

        $workspaceId = $config->workspace_id;
        $createdBy = $config->created_by;

        // Check if message is already logged (avoid double inserts)
        if ($msgSid) {
            $existingMsg = WhatsAppMessage::where('message_sid', $msgSid)->first();
            if ($existingMsg) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Message already exists.',
                    'message_id' => $existingMsg->id,
                ]);
            }
        }

        // 2. Find or create Chat Thread
        $chat = WhatsAppChat::where('customer_phone', $cleanedPhone)
            ->where('whatsapp_config_id', $config->id)
            ->first();

        // 3. Resolve Lead and Routing logic
        $lead = null;
        if ($chat && $chat->lead_id) {
            $lead = Lead::find($chat->lead_id);
        }

        if (!$lead) {
            // Find Lead in database by phone number
            $lead = Lead::where('phone', $cleanedPhone)
                ->where('workspace_id', $workspaceId)
                ->first();
        }

        $assignedUserId = null;

        if ($lead) {
            // Route message to lead owner/assignee
            $assignedUserId = $lead->user_id ?? $lead->users()->first()?->id ?? $createdBy;
        } else {
            // Create new Lead
            // Resolve Department Head / Team Head
            $deptHeadId = $this->resolveDepartmentHead($config);
            $assignedUserId = $deptHeadId;

            // Resolve WhatsApp Source
            $source = Source::firstOrCreate(
                ['name' => 'WhatsApp', 'workspace_id' => $workspaceId],
                ['created_by' => $createdBy]
            );

            // Create new Lead record
            $lead = new Lead();
            $lead->name = $customerName;
            $lead->phone = $cleanedPhone;
            $lead->subject = "WhatsApp Lead: " . $customerName;
            $lead->pipeline_id = $config->pipeline_id;
            $lead->stage_id = $config->stage_id;
            $lead->sources = (string) $source->id;
            $lead->created_by = $deptHeadId;
            $lead->user_id = $deptHeadId;
            $lead->workspace_id = $workspaceId;
            $lead->date = date('Y-m-d');
            $lead->notes = "Created automatically from inbound WhatsApp message: " . $body;
            $lead->save();

            // Create UserLead mapping
            UserLead::firstOrCreate([
                'lead_id' => $lead->id,
                'user_id' => $deptHeadId,
            ]);

            // Add activity log
            $lead->activities()->create([
                'user_id' => $deptHeadId,
                'log_type' => 'Lead Created',
                'remark' => json_encode(['message' => __('Lead automatically created from WhatsApp inbound message.')]),
            ]);
        }

        if (!$chat) {
            $chat = WhatsAppChat::create([
                'whatsapp_config_id' => $config->id,
                'customer_phone' => $cleanedPhone,
                'customer_name' => $customerName,
                'lead_id' => $lead->id,
                'assigned_user_id' => $assignedUserId,
                'workspace_id' => $workspaceId,
                'last_message_at' => now(),
            ]);
        } else {
            $chat->update([
                'lead_id' => $lead->id,
                'assigned_user_id' => $assignedUserId,
                'last_message_at' => now(),
            ]);
        }

        // 4. Save the Message
        $message = WhatsAppMessage::create([
            'whatsapp_chat_id' => $chat->id,
            'direction' => 'inbound',
            'message_type' => $msgType,
            'body' => $body,
            'media_url' => null, 
            'message_sid' => $msgSid,
            'status' => 'delivered',
            'sender_id' => null,
        ]);

        return response()->json([
            'status' => 'success',
            'chat' => $chat->load(['lead', 'assignee']),
            'message' => $message,
            'workspace_id' => $workspaceId,
        ]);
    }

    private function resolveDepartmentHead($config)
    {
        $workspaceId = $config->workspace_id;
        $fallbackId = $config->created_by ?: 1;

        if ($config->department_id) {
            if (module_is_active('Hrm') && class_exists('\Workdo\Hrm\Entities\Department') && class_exists('\Workdo\Hrm\Entities\Employee')) {
                $dept = \Workdo\Hrm\Entities\Department::find($config->department_id);
                if ($dept && $dept->manager_id) {
                    $manager = \Workdo\Hrm\Entities\Employee::find($dept->manager_id);
                    if ($manager && $manager->user_id) {
                        return $manager->user_id;
                    }
                }
            }
        }

        return $fallbackId;
    }
}
