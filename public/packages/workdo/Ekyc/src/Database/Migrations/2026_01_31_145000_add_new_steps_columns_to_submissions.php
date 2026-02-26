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
        Schema::table('ekyc_submissions', function (Blueprint $table) {
            $table->timestamp('segments_completed_at')->nullable()->after('bank_verified_at');
            $table->timestamp('details_completed_at')->nullable()->after('segments_completed_at');
            $table->timestamp('nominee_completed_at')->nullable()->after('details_completed_at');
            $table->timestamp('documents_completed_at')->nullable()->after('nominee_completed_at');
            
            // New fields for segments and details
            $table->text('trading_segments')->nullable(); // JSON Array
            $table->string('brokerage_plan')->nullable();
            
            // Personal Details fields
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('occupation')->nullable();
            $table->string('annual_income')->nullable();
            $table->boolean('is_pep')->default(false);
            $table->string('settlement_frequency')->nullable();
            $table->boolean('ddpi_consent')->default(false);
            
            // Nominee details
            $table->boolean('has_nominee')->nullable();
            $table->text('nominee_data')->nullable(); // JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ekyc_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'segments_completed_at',
                'details_completed_at',
                'nominee_completed_at',
                'documents_completed_at',
                'trading_segments',
                'brokerage_plan',
                'father_name',
                'mother_name',
                'marital_status',
                'occupation',
                'annual_income',
                'is_pep',
                'settlement_frequency',
                'ddpi_consent',
                'has_nominee',
                'nominee_data'
            ]);
        });
    }
};
