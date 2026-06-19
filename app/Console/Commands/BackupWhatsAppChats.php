<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workdo\Lead\Entities\WhatsAppChat;
use Workdo\Lead\Http\Controllers\WhatsAppSessionController;

class BackupWhatsAppChats extends Command
{
    protected $signature   = 'whatsapp:backup {--workspace= : Backup only a specific workspace ID}';
    protected $description = 'Backup all WhatsApp chat histories to the database (run nightly via scheduler)';

    public function handle()
    {
        $workspaceId = $this->option('workspace');

        $query = WhatsAppChat::query();
        if ($workspaceId) {
            $query->where('workspace_id', $workspaceId);
        }

        $chats = $query->get();
        $this->info("Starting backup for {$chats->count()} chats…");

        $controller = new WhatsAppSessionController();
        $success    = 0;
        $errors     = 0;

        foreach ($chats as $chat) {
            try {
                $controller->performChatBackup($chat, 'scheduled');
                $success++;
                $this->line("  ✓ Chat #{$chat->id} ({$chat->customer_phone}) backed up.");
            } catch (\Exception $e) {
                $errors++;
                $this->error("  ✗ Chat #{$chat->id} failed: " . $e->getMessage());
            }
        }

        $this->info("\nBackup complete. Success: {$success}, Errors: {$errors}");
        return Command::SUCCESS;
    }
}
