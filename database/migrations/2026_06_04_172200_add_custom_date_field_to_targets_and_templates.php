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
            if (!Schema::hasColumn('targets', 'custom_date_field')) {
                $table->string('custom_date_field')->default('created_at')->after('stage_id');
            }
        });

        Schema::table('target_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('target_templates', 'custom_date_field')) {
                $table->string('custom_date_field')->default('created_at')->after('stage_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn('custom_date_field');
        });

        Schema::table('target_templates', function (Blueprint $table) {
            $table->dropColumn('custom_date_field');
        });
    }
};
