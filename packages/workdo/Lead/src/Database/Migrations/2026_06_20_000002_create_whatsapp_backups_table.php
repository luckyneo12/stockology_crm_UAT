<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create WhatsApp chat backups table.
 * Stores full conversation history in JSON format.
 * Triggered manually, on schedule, or when a number gets blocked.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::create('whatsapp_chat_backups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_chat_id');
            $table->string('customer_phone', 50)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('backup_reason', 100)->default('manual'); // manual, number_blocked, scheduled
            $table->longText('messages_json');   // Full chat history as JSON array
            $table->unsignedInteger('message_count')->default(0);
            $table->integer('workspace_id');
            $table->integer('backed_up_by')->nullable(); // User ID who triggered
            $table->timestamps();

            $table->index('whatsapp_chat_id');
            $table->index('workspace_id');
            $table->index('customer_phone');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_chat_backups');
    }
};
