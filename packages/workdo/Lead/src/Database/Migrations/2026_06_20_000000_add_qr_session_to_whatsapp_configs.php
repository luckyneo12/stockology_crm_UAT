<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Add QR-based session management fields to whatsapp_configs table.
 * The existing Meta Cloud API fields (phone_number_id, business_account_id,
 * access_token, verify_token) are made nullable since QR-based sessions
 * don't require them.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('whatsapp_configs', function (Blueprint $table) {
            // Session tracking fields
            $table->enum('session_status', ['disconnected', 'connecting', 'qr_pending', 'authenticated', 'connected', 'blocked'])
                  ->default('disconnected')
                  ->after('verify_token');
            $table->string('session_id', 255)->nullable()->after('session_status'); // Unique session identifier

            // Make Meta Cloud API fields nullable (not required for QR-based)
            $table->string('phone_number_id', 255)->nullable()->change();
            $table->string('business_account_id', 255)->nullable()->change();
            $table->text('access_token')->nullable()->change();
            $table->string('verify_token', 255)->nullable()->change();

            // WhatsApp connection type
            $table->enum('connection_type', ['meta_cloud', 'qr_session'])
                  ->default('qr_session')
                  ->after('session_id');
        });
    }

    public function down()
    {
        Schema::table('whatsapp_configs', function (Blueprint $table) {
            $table->dropColumn(['session_status', 'session_id', 'connection_type']);
        });
    }
};
