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
        Schema::create('ekyc_otp_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submission_id')->nullable();
            $table->string('identifier'); // email or phone number
            $table->enum('identifier_type', ['email', 'mobile']);
            $table->string('otp_code'); // encrypted
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts')->default(3);
            $table->boolean('is_testing_mode')->default(false); // Track if testing mode was used
            $table->string('provider')->nullable(); // twilio, msg91, sendgrid, etc.
            $table->text('provider_response')->nullable(); // JSON response from provider
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('identifier');
            $table->index('expires_at');
            $table->index(['identifier', 'identifier_type']);
            
            // Foreign key
            $table->foreign('submission_id')->references('id')->on('ekyc_submissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekyc_otp_logs');
    }
};
