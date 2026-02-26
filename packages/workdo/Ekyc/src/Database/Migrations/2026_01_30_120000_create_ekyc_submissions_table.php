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
        Schema::create('ekyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id')->unique();
            $table->integer('current_step')->default(1);
            
            // Contact Information
            $table->string('mobile_number', 20)->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('rm_pp_code', 50)->nullable();
            $table->string('relation', 50)->nullable(); // SELF, FATHER, MOTHER, etc.
            
            // PAN Details
            $table->string('pan_number', 10)->nullable();
            $table->string('pan_name')->nullable();
            $table->date('pan_dob')->nullable();
            $table->timestamp('pan_verified_at')->nullable();
            $table->text('pan_response')->nullable(); // JSON response from Digio
            
            // Aadhaar Details
            $table->string('aadhaar_number', 12)->nullable();
            $table->timestamp('aadhaar_verified_at')->nullable();
            $table->string('aadhaar_xml_path')->nullable();
            $table->text('aadhaar_data')->nullable(); // JSON parsed data
            
            // Selfie & Face Match
            $table->string('selfie_path')->nullable();
            $table->decimal('face_match_score', 5, 2)->nullable();
            $table->timestamp('face_verified_at')->nullable();
            
            // Bank Details
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_ifsc', 11)->nullable();
            $table->string('bank_account_holder_name')->nullable();
            $table->timestamp('bank_verified_at')->nullable();
            $table->text('bank_response')->nullable(); // JSON response
            
            // Video KYC
            $table->timestamp('video_kyc_scheduled_at')->nullable();
            $table->timestamp('video_kyc_completed_at')->nullable();
            $table->unsignedBigInteger('video_kyc_officer_id')->nullable();
            $table->text('video_kyc_notes')->nullable();
            
            // Status & Workflow
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected', 'on_hold'])->default('pending');
            $table->unsignedBigInteger('pipeline_id')->nullable();
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            // Metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('additional_data')->nullable(); // JSON for custom fields
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('session_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('mobile_number');
            $table->index('email');
            $table->index('pan_number');
            $table->index(['pipeline_id', 'stage_id']);
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('pipeline_id')->references('id')->on('ekyc_pipelines')->onDelete('set null');
            $table->foreign('stage_id')->references('id')->on('ekyc_stages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekyc_submissions');
    }
};
