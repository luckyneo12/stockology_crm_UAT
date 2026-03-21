<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            // Index for Kanban main query and counts
            if (!Schema::hasIndex('leads', 'leads_workspace_pipeline_stage_idx')) {
                $table->index(['workspace_id', 'pipeline_id', 'stage_id'], 'leads_workspace_pipeline_stage_idx');
            }
            // Index for sorting by recently active
            if (!Schema::hasIndex('leads', 'leads_updated_at_index')) {
                $table->index('updated_at', 'leads_updated_at_index');
            }
        });

        Schema::table('lead_activity_logs', function (Blueprint $table) {
            // Index for real-time polling (changesSince)
            if (!Schema::hasIndex('lead_activity_logs', 'log_created_at_index')) {
                $table->index('created_at', 'log_created_at_index');
            }
            // Index for grouping/filtering by lead
            if (!Schema::hasIndex('lead_activity_logs', 'log_lead_id_index')) {
                $table->index('lead_id', 'log_lead_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasIndex('leads', 'leads_workspace_pipeline_stage_idx')) {
                $table->dropIndex('leads_workspace_pipeline_stage_idx');
            }
            if (Schema::hasIndex('leads', 'leads_updated_at_index')) {
                $table->dropIndex('leads_updated_at_index');
            }
        });

        Schema::table('lead_activity_logs', function (Blueprint $table) {
            if (Schema::hasIndex('lead_activity_logs', 'log_created_at_index')) {
                $table->dropIndex('log_created_at_index');
            }
            if (Schema::hasIndex('lead_activity_logs', 'log_lead_id_index')) {
                $table->dropIndex('log_lead_id_index');
            }
        });
    }
};
