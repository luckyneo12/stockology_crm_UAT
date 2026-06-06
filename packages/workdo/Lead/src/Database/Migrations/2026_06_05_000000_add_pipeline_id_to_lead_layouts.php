<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('lead_sections')) {
            Schema::table('lead_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_sections', 'pipeline_id')) {
                    $table->unsignedBigInteger('pipeline_id')->nullable()->after('workspace_id');
                }
            });
        }

        if (Schema::hasTable('lead_custom_fields')) {
            Schema::table('lead_custom_fields', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_custom_fields', 'pipeline_id')) {
                    $table->unsignedBigInteger('pipeline_id')->nullable()->after('workspace_id');
                }
            });
        }

        // Map existing sections and custom fields to the first pipeline in their workspace
        try {
            $workspaces = DB::table('lead_sections')->select('workspace_id')->union(
                DB::table('lead_custom_fields')->select('workspace_id')
            )->distinct()->pluck('workspace_id');

            foreach ($workspaces as $workspaceId) {
                $firstPipeline = DB::table('pipelines')->where('workspace_id', $workspaceId)->orderBy('id')->first();
                if ($firstPipeline) {
                    DB::table('lead_sections')
                        ->where('workspace_id', $workspaceId)
                        ->whereNull('pipeline_id')
                        ->update(['pipeline_id' => $firstPipeline->id]);

                    DB::table('lead_custom_fields')
                        ->where('workspace_id', $workspaceId)
                        ->whereNull('pipeline_id')
                        ->update(['pipeline_id' => $firstPipeline->id]);
                }
            }
        } catch (\Exception $e) {
            // Log/ignore errors if table has no entries or pipelines doesn't exist
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('lead_sections')) {
            Schema::table('lead_sections', function (Blueprint $table) {
                if (Schema::hasColumn('lead_sections', 'pipeline_id')) {
                    $table->dropColumn('pipeline_id');
                }
            });
        }

        if (Schema::hasTable('lead_custom_fields')) {
            Schema::table('lead_custom_fields', function (Blueprint $table) {
                if (Schema::hasColumn('lead_custom_fields', 'pipeline_id')) {
                    $table->dropColumn('pipeline_id');
                }
            });
        }
    }
};
