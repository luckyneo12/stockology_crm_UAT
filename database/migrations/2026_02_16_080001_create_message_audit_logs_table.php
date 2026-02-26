<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('message_audit_logs')) {
            Schema::create('message_audit_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('message_id')->nullable()->comment('ID of the message, nullable in case of force delete');
                $table->string('action'); // soft_delete, force_delete, restore
                $table->unsignedBigInteger('performed_by'); // User who performed the action
                $table->text('message_content_snapshot')->nullable(); // Snapshot of body
                $table->string('file_path_snapshot')->nullable(); // Snapshot of file path
                $table->timestamps();

                // Indexes for filtering
                $table->index('message_id');
                $table->index('performed_by');
                $table->index('action');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_audit_logs');
    }
};
