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
        Schema::table('targets', function (Blueprint $table) {
            if (!Schema::hasColumn('targets', 'target_type')) {
                $table->string('target_type')->default('manual')->after('status');
            }
            if (!Schema::hasColumn('targets', 'pipeline_id')) {
                $table->unsignedBigInteger('pipeline_id')->nullable()->after('target_type');
            }
            if (!Schema::hasColumn('targets', 'stage_id')) {
                $table->unsignedBigInteger('stage_id')->nullable()->after('pipeline_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn(['target_type', 'pipeline_id', 'stage_id']);
        });
    }
};
