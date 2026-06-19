<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Workdo\Lead\Entities\WhatsAppChat;
use Workdo\Lead\Entities\WhatsAppMessage;
use Workdo\Lead\Entities\WhatsAppConfig;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadStage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChatController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = getActiveWorkSpace();
        
        // Get all configs in workspace and filter by access
        $allConfigs = WhatsAppConfig::where('workspace_id', $workspaceId)->get();
        $configs = $allConfigs->filter(function ($config) {
            return $config->isAccessible();
        })->values();

        // Get all chats in workspace
        $allChats = WhatsAppChat::where('workspace_id', $workspaceId)
            ->with(['lead', 'config', 'assignee'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        // Filter based on visibility
        $chats = $allChats->filter(function ($chat) {
            return $chat->isAccessible();
        })->values();

        // Determine if there is a preloaded chat from a Lead show page
        $preloadedChatId = $request->query('chat_id');
        $preloadedLeadId = $request->query('lead_id');
        $preloadedChat = null;

        if ($preloadedChatId) {
            $preloadedChat = $chats->firstWhere('id', $preloadedChatId);
        } elseif ($preloadedLeadId) {
            $preloadedChat = $chats->firstWhere('lead_id', $preloadedLeadId);
            if (!$preloadedChat) {
                // If chat doesn't exist but lead does, maybe create or fetch one if we have a config
                $lead = Lead::find($preloadedLeadId);
                if ($lead && $lead->phone) {
                    $cleanedPhone = preg_replace('/[^0-9]/', '', $lead->phone);
                    // Match with the first config in workspace or lead's creator config
                    $config = $configs->first();
                    if ($config && $cleanedPhone) {
                        $preloadedChat = WhatsAppChat::firstOrCreate([
                            'customer_phone' => $cleanedPhone,
                            'whatsapp_config_id' => $config->id,
                            'workspace_id' => $workspaceId,
                        ], [
                            'customer_name' => $lead->name,
                            'lead_id' => $lead->id,
                            'assigned_user_id' => $lead->user_id ?? Auth::user()->id,
                            'last_message_at' => now(),
                        ]);
                        
                        // Reload chats list to include this new preloaded chat
                        $allChats = WhatsAppChat::where('workspace_id', $workspaceId)
                            ->with(['lead', 'config', 'assignee'])
                            ->orderBy('last_message_at', 'desc')
                            ->get();
                        $chats = $allChats->filter(function ($chat) {
                            return $chat->isAccessible();
                        })->values();
                    }
                }
            }
        }

        return view('lead::whatsapp.chat', compact('configs', 'chats', 'preloadedChat'));
    }

    public function getMessages($id)
    {
        $chat = WhatsAppChat::with(['lead', 'config', 'assignee'])->findOrFail($id);

        if (!$chat->isAccessible()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $messages = WhatsAppMessage::where('whatsapp_chat_id', $chat->id)
            ->with('sender')
            ->orderBy('id', 'asc')
            ->get();

        // Get stages for the lead's pipeline if lead exists
        $stages = [];
        if ($chat->lead && $chat->lead->pipeline_id) {
            $stages = LeadStage::where('pipeline_id', $chat->lead->pipeline_id)
                ->where('workspace_id', getActiveWorkSpace())
                ->get()
                ->pluck('name', 'id')
                ->toArray();
        }

        return response()->json([
            'chat' => $chat,
            'messages' => $messages,
            'stages' => $stages,
        ]);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        $chat = WhatsAppChat::with('config')->findOrFail($request->chat_id);

        if (!$chat->isAccessible()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $config = $chat->config;
        if (!$config) {
            return response()->json(['error' => __('Configuration not found for this chat.')], 404);
        }

        $msgSid = null;

        if ($config->isQrBased()) {
            $nodeUrl = rtrim(env('WHATSAPP_NODE_URL', 'http://localhost:3001'), '/');
            $response = Http::post("{$nodeUrl}/api/whatsapp/send/{$config->id}", [
                'to' => $chat->customer_phone,
                'message' => $request->message,
            ]);

            if (!$response->successful()) {
                Log::error('WhatsApp Node Service Send Error: ' . $response->body());
                return response()->json([
                    'error' => __('Failed to send message via WhatsApp Node Service.'),
                    'details' => $response->json()
                ], 400);
            }

            $resData = $response->json();
            $msgSid = $resData['message_id'] ?? null;
        } else {
            // Send to Meta Cloud API
            $phoneId = $config->phone_number_id;
            $accessToken = $config->access_token;

            $response = Http::withToken($accessToken)
                ->post("https://graph.facebook.com/v19.0/{$phoneId}/messages", [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $chat->customer_phone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $request->message,
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('WhatsApp Meta API Error: ' . $response->body());
                return response()->json([
                    'error' => __('Failed to send message via WhatsApp Cloud API.'),
                    'details' => $response->json()
                ], 400);
            }

            $resData = $response->json();
            $msgSid = $resData['messages'][0]['id'] ?? null;
        }

        // Save outbound message to local db
        $message = WhatsAppMessage::create([
            'whatsapp_chat_id' => $chat->id,
            'direction' => 'outbound',
            'message_type' => 'text',
            'body' => $request->message,
            'media_url' => null,
            'message_sid' => $msgSid,
            'status' => 'sent',
            'sender_id' => Auth::user()->id,
        ]);

        // Update chat last message time
        $chat->update(['last_message_at' => now()]);

        // Broadcast to Node.js server
        $nodeUrl = rtrim(env('WHATSAPP_NODE_URL', 'http://localhost:3001'), '/');
        try {
            Http::post("{$nodeUrl}/api/broadcast-message", [
                'chat' => $chat->load(['lead', 'assignee']),
                'message' => $message->load('sender'),
                'workspace_id' => $chat->workspace_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to notify Node.js server of outbound message: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => $message->load('sender'),
            'chat' => $chat,
        ]);
    }

    public function updateLeadStage(Request $request, $chatId)
    {
        $request->validate([
            'stage_id' => 'required|integer',
        ]);

        $chat = WhatsAppChat::findOrFail($chatId);
        if (!$chat->isAccessible()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $lead = $chat->lead;
        if (!$lead) {
            return response()->json(['error' => __('No linked lead found.')], 404);
        }

        $oldStageId = $lead->stage_id;
        $lead->stage_id = $request->stage_id;
        $lead->save();

        // Create lead activity log
        $newStage = LeadStage::find($request->stage_id);
        $oldStage = LeadStage::find($oldStageId);
        $newStageName = $newStage ? $newStage->name : __('Unknown');
        $oldStageName = $oldStage ? $oldStage->name : __('Unknown');

        $lead->activities()->create([
            'user_id' => Auth::user()->id,
            'log_type' => 'Stage Changed',
            'remark' => json_encode([
                'title' => __('Stage Changed'),
                'old_stage' => $oldStageName,
                'new_stage' => $newStageName,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => __('Lead stage updated successfully.'),
            'new_stage' => $newStageName,
        ]);
    }
}
