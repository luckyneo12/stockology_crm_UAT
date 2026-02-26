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
            $table->string('education')->nullable()->after('marital_status');
            $table->string('trading_experience')->nullable()->after('annual_income');
            $table->string('networth')->nullable()->after('trading_experience');
            $table->date('networth_date')->nullable()->after('networth');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ekyc_submissions', function (Blueprint $table) {
            $table->dropColumn(['education', 'trading_experience', 'networth', 'networth_date']);
        });
    }
};
