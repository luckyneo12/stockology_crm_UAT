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
            $table->string('running_account_auth')->nullable()->default('once_in_quarter');
            $table->boolean('receive_credits')->default(true);
            $table->boolean('pledge_instruction')->default(false);
            $table->string('nominee_statement_type')->nullable()->default('nomination_status');
            $table->string('statement_requirement')->nullable()->default('daily');
            $table->boolean('electronic_statement')->default(true);
            $table->boolean('share_email_rta')->default(true);
            $table->string('annual_report_media')->nullable()->default('electronic');
            $table->boolean('receive_dividend_directly')->default(true);
            $table->boolean('dis_booklet')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ekyc_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'running_account_auth',
                'receive_credits',
                'pledge_instruction',
                'nominee_statement_type',
                'statement_requirement',
                'electronic_statement',
                'share_email_rta',
                'annual_report_media',
                'receive_dividend_directly',
                'dis_booklet'
            ]);
        });
    }
};
