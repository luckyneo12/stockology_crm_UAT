<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('lead_sections')) {
            $hasLayoutType = Schema::hasColumn('lead_sections', 'layout_type');
            $hasApiUrl = Schema::hasColumn('lead_sections', 'api_url');
            $hasApiMethod = Schema::hasColumn('lead_sections', 'api_method');
            $hasApiTriggerStageId = Schema::hasColumn('lead_sections', 'api_trigger_stage_id');
            $hasApiResponseMapping = Schema::hasColumn('lead_sections', 'api_response_mapping');

            Schema::table('lead_sections', function (Blueprint $table) use ($hasLayoutType, $hasApiUrl, $hasApiMethod, $hasApiTriggerStageId, $hasApiResponseMapping) {
                if (!$hasLayoutType) {
                    $table->string('layout_type')->default('section')->nullable();
                }
                if (!$hasApiUrl) {
                    $table->text('api_url')->nullable();
                }
                if (!$hasApiMethod) {
                    $table->string('api_method')->default('GET');
                }
                if (!$hasApiTriggerStageId) {
                    $table->integer('api_trigger_stage_id')->nullable();
                }
                if (!$hasApiResponseMapping) {
                    $table->text('api_response_mapping')->nullable();
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('lead_sections')) {
            $hasLayoutType = Schema::hasColumn('lead_sections', 'layout_type');
            $hasApiUrl = Schema::hasColumn('lead_sections', 'api_url');
            $hasApiMethod = Schema::hasColumn('lead_sections', 'api_method');
            $hasApiTriggerStageId = Schema::hasColumn('lead_sections', 'api_trigger_stage_id');
            $hasApiResponseMapping = Schema::hasColumn('lead_sections', 'api_response_mapping');

            Schema::table('lead_sections', function (Blueprint $table) use ($hasLayoutType, $hasApiUrl, $hasApiMethod, $hasApiTriggerStageId, $hasApiResponseMapping) {
                $columnsToDrop = [];
                if ($hasLayoutType) $columnsToDrop[] = 'layout_type';
                if ($hasApiUrl) $columnsToDrop[] = 'api_url';
                if ($hasApiMethod) $columnsToDrop[] = 'api_method';
                if ($hasApiTriggerStageId) $columnsToDrop[] = 'api_trigger_stage_id';
                if ($hasApiResponseMapping) $columnsToDrop[] = 'api_response_mapping';
                
                if (count($columnsToDrop) > 0) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
