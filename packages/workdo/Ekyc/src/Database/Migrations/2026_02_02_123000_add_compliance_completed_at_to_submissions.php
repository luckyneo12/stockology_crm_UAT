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
            $table->timestamp('compliance_completed_at')->nullable()->after('details_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ekyc_submissions', function (Blueprint $table) {
            $table->dropColumn('compliance_completed_at');
        });
    }
};
