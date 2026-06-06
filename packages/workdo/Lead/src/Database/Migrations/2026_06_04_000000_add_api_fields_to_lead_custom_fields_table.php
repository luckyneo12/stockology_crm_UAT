<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('lead_custom_fields')) {
            $hasApiUrl = Schema::hasColumn('lead_custom_fields', 'api_url');
            $hasApiMethod = Schema::hasColumn('lead_custom_fields', 'api_method');
            $hasApiTriggerStageId = Schema::hasColumn('lead_custom_fields', 'api_trigger_stage_id');
            $hasApiResponseKey = Schema::hasColumn('lead_custom_fields', 'api_response_key');

            Schema::table('lead_custom_fields', function (Blueprint $table) use ($hasApiUrl, $hasApiMethod, $hasApiTriggerStageId, $hasApiResponseKey) {
                if (!$hasApiUrl) {
                    $table->text('api_url')->nullable();
                }
                if (!$hasApiMethod) {
                    $table->string('api_method')->default('GET');
                }
                if (!$hasApiTriggerStageId) {
                    $table->integer('api_trigger_stage_id')->nullable();
                }
                if (!$hasApiResponseKey) {
                    $table->string('api_response_key')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('lead_custom_fields')) {
            Schema::table('lead_custom_fields', function (Blueprint $table) {
                $columnsToDrop = [];
                if (Schema::hasColumn('lead_custom_fields', 'api_url')) $columnsToDrop[] = 'api_url';
                if (Schema::hasColumn('lead_custom_fields', 'api_method')) $columnsToDrop[] = 'api_method';
                if (Schema::hasColumn('lead_custom_fields', 'api_trigger_stage_id')) $columnsToDrop[] = 'api_trigger_stage_id';
                if (Schema::hasColumn('lead_custom_fields', 'api_response_key')) $columnsToDrop[] = 'api_response_key';

                if (count($columnsToDrop) > 0) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};
