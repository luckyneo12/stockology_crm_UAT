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
        Schema::table('leads', function (Blueprint $table) {
            $table->index('pipeline_id');
            $table->index('workspace_id');
            $table->index('stage_id');
            $table->index('user_id');
            $table->index('created_by');
        });

        Schema::table('lead_field_visibilities', function (Blueprint $table) {
            $table->index('workspace_id');
            $table->index('field_name');
        });

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->index('user_id');
                $table->index('workspace');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['pipeline_id']);
            $table->dropIndex(['workspace_id']);
            $table->dropIndex(['stage_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('lead_field_visibilities', function (Blueprint $table) {
            $table->dropIndex(['workspace_id']);
            $table->dropIndex(['field_name']);
        });

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['workspace']);
            });
        }
    }
};
