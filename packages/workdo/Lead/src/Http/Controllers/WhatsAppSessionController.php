<?php

namespace Workdo\Lead\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Workdo\Lead\Entities\WhatsAppConfig;
use Workdo\Lead\Entities\WhatsAppChat;
use Workdo\Lead\Entities\WhatsAppMessage;
use Workdo\Lead\Entities\Lead;
use Workdo\Lead\Entities\LeadStage;

/**
 * WhatsAppSessionController
 *
 * Handles:
 * - QR code generation & session management (proxied to Node.js)
 * - Incoming webhook from Node.js (saves messages to DB)
 * - Session status updates from Node.js
 * - Chat backup (manual trigger)
 */
class WhatsAppSessionController extends Controller
{
    private function nodeUrl(): string
    {
        return rtrim(env('WHATSAPP_NODE_URL', 'http://localhost:3001'), '/');
    }

    private function nodeSecret(): string
    {
        return env('WHATSAPP_NODE_SECRET', 'whatsapp_node_secret_key');
    }

    // ── Verify Node.js secret ────────────────────────────────────────────────
    private function verifyNodeSecret(Request $request): bool
    {
        return $request->input('secret') === $this->nodeSecret();
    }

    // ────────────────────────────────────────────────────────────────────────
    // QR: Initiate session / get QR image
    // GET /whatsapp-config/{id}/qr
    // ────────────────────────────────────────────────────────────────────────
    public function initiateQr($id)
    {
        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        // Request QR from Node.js service
        try {
            $response = Http::timeout(35)->get("{$this->nodeUrl()}/api/whatsapp/qr/{$id}");
            $data     = $response->json();

            if (($data['status'] ?? '') === 'connected') {
                return response()->json([
                    'status'  => 'connected',
                    'message' => __('WhatsApp is already connected.'),
                ]);
            }

            if (($data['status'] ?? '') === 'qr_pending' && !empty($data['qr_data_url'])) {
                return response()->json([
                    'status'      => 'qr_pending',
                    'qr_data_url' => $data['qr_data_url'],
                    'config_id'   => $id,
                    'config_name' => $config->name,
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $data['message'] ?? __('Failed to generate QR. Please try again.'),
            ], 500);

        } catch (\Exception $e) {
            Log::error("WhatsApp QR initiate error for config {$id}: " . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => __('Node.js service unreachable. Please ensure the WhatsApp service is running.'),
            ], 503);
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Session Status: Check current connection status
    // GET /whatsapp-config/{id}/status
    // ────────────────────────────────────────────────────────────────────────
    public function getStatus($id)
    {
        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        // Return DB status (updated by Node.js callbacks)
        return response()->json([
            'config_id'    => $id,
            'status'       => $config->session_status,
            'status_label' => $config->session_status_label,
            'status_color' => $config->session_status_color,
            'connected'    => $config->isConnected(),
            'phone_number' => $config->phone_number,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Disconnect: Terminate session
    // POST /whatsapp-config/{id}/disconnect
    // ────────────────────────────────────────────────────────────────────────
    public function disconnect($id)
    {
        $config = WhatsAppConfig::where('workspace_id', getActiveWorkSpace())->findOrFail($id);

        try {
            Http::timeout(10)->post("{$this->nodeUrl()}/api/whatsapp/disconnect/{$id}");
        } catch (\Exception $e) {
            Log::warning("WhatsApp disconnect request failed for config {$id}: " . $e->getMessage());
        }

        $config->update(['session_status' => 'disconnected']);

        return response()->json([
            'status'  => 'ok',
            'message' => __('WhatsApp session disconnected.'),
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Incoming Webhook: Node.js → Laravel (save message to DB)
    // POST /api/whatsapp/incoming-webhook
    // ────────────────────────────────────────────────────────────────────────
    public function handleWebhook(Request $request)
    {
        if (!$this->verifyNodeSecret($request)) {
            Log::warning('WhatsApp webhook: Invalid secret');
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $configId    = $request->input('config_id');
        $fromPhone   = preg_replace('/[^0-9]/', '', $request->input('from', ''));
        $body        = $request->input('body', '');
        $msgType     = $request->input('type', 'text');
        $messageSid  = $request->input('message_id');
        $hasMedia    = $request->boolean('has_media');
        $mediaBase64 = $request->input('media_base64');
        $mediaMime   = $request->input('media_mimetype');

        if (!$configId || !$fromPhone) {
            return response()->json(['status' => 'error', 'message' => 'Missing fields'], 400);
        }

        $config = WhatsAppConfig::find($configId);
        if (!$config) {
            return response()->json(['status' => 'error', 'message' => 'Config not found'], 404);
        }

        // Find or create chat
        $chat = WhatsAppChat::firstOrCreate(
            [
                'customer_phone'      => $fromPhone,
                'whatsapp_config_id'  => $configId,
                'workspace_id'        => $config->workspace_id,
            ],
            [
                'customer_name'    => $fromPhone, // Will be updated if lead matches
                'lead_id'          => null,
                'assigned_user_id' => null,
                'last_message_at'  => now(),
            ]
        );

        // Auto-link to lead if phone matches
        if (!$chat->lead_id) {
            $lead = Lead::where('workspace_id', $config->workspace_id)
                ->where(function ($q) use ($fromPhone) {
                    $q->where('phone', $fromPhone)
                      ->orWhere('phone', '+' . $fromPhone)
                      ->orWhere('phone', 'LIKE', "%{$fromPhone}%");
                })->first();

            if ($lead) {
                $chat->update([
                    'lead_id'          => $lead->id,
                    'customer_name'    => $lead->name,
                    'assigned_user_id' => $lead->user_id,
                ]);
            }
        }

        // Handle media: save URL or base64 reference
        $mediaUrl = null;
        if ($hasMedia && $mediaBase64 && $mediaMime) {
            // Store media file to disk
            $ext       = explode('/', $mediaMime)[1] ?? 'bin';
            $ext       = str_replace(['jpeg'], ['jpg'], $ext);
            $filename  = 'wa_media_' . uniqid() . '.' . $ext;
            $storagePath = storage_path("app/public/whatsapp_media/{$filename}");
            @mkdir(dirname($storagePath), 0755, true);
            file_put_contents($storagePath, base64_decode($mediaBase64));
            $mediaUrl = asset("storage/whatsapp_media/{$filename}");
        }

        // Save message
        $message = WhatsAppMessage::create([
            'whatsapp_chat_id' => $chat->id,
            'direction'        => 'inbound',
            'message_type'     => $msgType,
            'body'             => $body ?: ($hasMedia ? '[Media]' : ''),
            'media_url'        => $mediaUrl,
            'message_sid'      => $messageSid,
            'status'           => 'received',
            'sender_id'        => null,
        ]);

        // Update chat's last_message_at
        $chat->update(['last_message_at' => now()]);

        // Auto-create lead if config has pipeline/stage set and no lead linked
        if (!$chat->lead_id && $config->pipeline_id && $config->stage_id) {
            $newLead = Lead::create([
                'name'         => 'WhatsApp - ' . $fromPhone,
                'phone'        => $fromPhone,
                'pipeline_id'  => $config->pipeline_id,
                'stage_id'     => $config->stage_id,
                'workspace_id' => $config->workspace_id,
                'user_id'      => $config->created_by,
                'created_by'   => $config->created_by,
                'source_id'    => null,
            ]);
            $chat->update([
                'lead_id'       => $newLead->id,
                'customer_name' => $newLead->name,
            ]);
        }

        return response()->json([
            'status'       => 'success',
            'chat'         => $chat->load(['lead', 'config', 'assignee']),
            'message'      => $message->load('sender'),
            'workspace_id' => $config->workspace_id,
        ]);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Session Status Update: Node.js → Laravel
    // POST /api/whatsapp/session-status
    // ────────────────────────────────────────────────────────────────────────
    public function updateSessionStatus(Request $request)
    {
        if (!$this->verifyNodeSecret($request)) {
            return response()->json(['status' => 'error'], 401);
        }

        $configId    = $request->input('config_id');
        $status      = $request->input('status');
        $phoneNumber = $request->input('phone_number');

        $allowed = ['disconnected', 'connecting', 'qr_pending', 'authenticated', 'connected', 'blocked'];
        if (!$configId || !in_array($status, $allowed)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
        }

        $config = WhatsAppConfig::find($configId);
        if (!$config) {
            return response()->json(['status' => 'error', 'message' => 'Config not found'], 404);
        }

        $updateData = ['session_status' => $status];
        if ($phoneNumber) {
            $updateData['phone_number'] = $phoneNumber;
        }

        // If blocked → trigger auto-backup of all chats
        if ($status === 'blocked') {
            $this->triggerConfigBackup($config->id, 'number_blocked');
        }

        $config->update($updateData);

        return response()->json(['status' => 'ok']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Message ACK update (delivery receipts)
    // POST /api/whatsapp/message-ack
    // ────────────────────────────────────────────────────────────────────────
    public function messageAck(Request $request)
    {
        if (!$this->verifyNodeSecret($request)) {
            return response()->json(['status' => 'error'], 401);
        }

        $messageSid = $request->input('message_sid');
        $status     = $request->input('status', 'sent');

        if ($messageSid) {
            WhatsAppMessage::where('message_sid', $messageSid)->update(['status' => $status]);
        }

        return response()->json(['status' => 'ok']);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Manual Chat Backup
    // POST /whatsapp-chats/{id}/backup
    // ────────────────────────────────────────────────────────────────────────
    public function backupChat($chatId)
    {
        $chat = WhatsAppChat::where('workspace_id', getActiveWorkSpace())->findOrFail($chatId);

        if (!$chat->isAccessible()) {
            return response()->json(['error' => __('Permission denied.')], 403);
        }

        $this->performChatBackup($chat, 'manual', Auth::id());

        return response()->json([
            'status'  => 'success',
            'message' => __('Chat backup created successfully.'),
        ]);
    }

    // ── Internal: Perform backup for a single chat ───────────────────────────
    public function performChatBackup(WhatsAppChat $chat, string $reason = 'manual', ?int $backedUpBy = null)
    {
        $messages = WhatsAppMessage::where('whatsapp_chat_id', $chat->id)
            ->with('sender')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id'           => $msg->id,
                    'direction'    => $msg->direction,
                    'type'         => $msg->message_type,
                    'body'         => $msg->body,
                    'media_url'    => $msg->media_url,
                    'status'       => $msg->status,
                    'sender_name'  => $msg->sender?->name,
                    'sent_at'      => $msg->created_at?->toISOString(),
                ];
            })->toArray();

        \DB::table('whatsapp_chat_backups')->insert([
            'whatsapp_chat_id' => $chat->id,
            'customer_phone'   => $chat->customer_phone,
            'customer_name'    => $chat->customer_name,
            'backup_reason'    => $reason,
            'messages_json'    => json_encode($messages),
            'message_count'    => count($messages),
            'workspace_id'     => $chat->workspace_id,
            'backed_up_by'     => $backedUpBy,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        Log::info("WhatsApp chat #{$chat->id} backed up. Reason: {$reason}. Messages: " . count($messages));
    }

    // ── Internal: Trigger backup for all chats of a config ──────────────────
    private function triggerConfigBackup(int $configId, string $reason)
    {
        $chats = WhatsAppChat::where('whatsapp_config_id', $configId)->get();
        foreach ($chats as $chat) {
            try {
                $this->performChatBackup($chat, $reason);
            } catch (\Exception $e) {
                Log::error("Backup failed for chat #{$chat->id}: " . $e->getMessage());
            }
        }
        Log::info("Triggered {$reason} backup for config #{$configId}: {$chats->count()} chats.");
    }
}
