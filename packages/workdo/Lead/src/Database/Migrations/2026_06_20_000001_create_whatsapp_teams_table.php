<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Create WhatsApp Teams and Team Members tables.
 * Teams group CRM users together. A WhatsApp config (phone number)
 * can be assigned to a team. Team members can use that number to chat.
 * Team heads can see all member chats.
 */
return new class extends Migration
{
    public function up()
    {
        // WhatsApp Teams
        Schema::create('whatsapp_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('whatsapp_config_id')->nullable(); // Assigned WhatsApp number
            $table->integer('workspace_id');
            $table->integer('created_by');
            $table->timestamps();

            $table->index('workspace_id');
            $table->index('whatsapp_config_id');
        });

        // WhatsApp Team Members
        Schema::create('whatsapp_team_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('team_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('role', ['head', 'member'])->default('member');
            $table->timestamps();

            $table->unique(['team_id', 'user_id']); // One user per team (prevent duplicates)
            $table->index('team_id');
            $table->index('user_id');

            $table->foreign('team_id')->references('id')->on('whatsapp_teams')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('whatsapp_team_members');
        Schema::dropIfExists('whatsapp_teams');
    }
};
