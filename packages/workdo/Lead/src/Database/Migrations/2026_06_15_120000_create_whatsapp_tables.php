<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. WhatsApp Configs Table
        Schema::create('whatsapp_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number');
            $table->string('phone_number_id');
            $table->string('business_account_id');
            $table->text('access_token');
            $table->string('verify_token');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('pipeline_id')->nullable();
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->integer('workspace_id');
            $table->integer('created_by');
            $table->timestamps();
        });

        // 2. WhatsApp Chats Table
        Schema::create('whatsapp_chats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_config_id');
            $table->string('customer_phone');
            $table->string('customer_name')->nullable();
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->integer('workspace_id');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('customer_phone');
            $table->index('assigned_user_id');
        });

        // 3. WhatsApp Messages Table
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('whatsapp_chat_id');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->string('message_type')->default('text'); // text, image, document, audio
            $table->text('body')->nullable();
            $table->text('media_url')->nullable();
            $table->string('message_sid')->nullable(); // Meta message ID
            $table->string('status')->default('sent'); // sent, delivered, read, failed
            $table->unsignedBigInteger('sender_id')->nullable(); // User ID if outbound
            $table->timestamps();

            $table->index('whatsapp_chat_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_chats');
        Schema::dropIfExists('whatsapp_configs');
    }
};
