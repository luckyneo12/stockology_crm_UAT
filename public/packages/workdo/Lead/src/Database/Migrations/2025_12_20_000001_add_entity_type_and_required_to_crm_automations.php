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
        if (Schema::hasTable('stage_custom_fields')) {
            Schema::table('stage_custom_fields', function (Blueprint $table) {
                if (!Schema::hasColumn('stage_custom_fields', 'entity_type')) {
                    $table->string('entity_type')->default('lead')->after('id');
                }
                if (!Schema::hasColumn('stage_custom_fields', 'is_required')) {
                    $table->boolean('is_required')->default(0)->after('custom_field_id');
                }
            });
        }

        if (Schema::hasTable('pipeline_stage_automations')) {
            Schema::table('pipeline_stage_automations', function (Blueprint $table) {
                if (!Schema::hasColumn('pipeline_stage_automations', 'entity_type')) {
                    $table->string('entity_type')->default('lead')->after('id');
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
        if (Schema::hasTable('stage_custom_fields')) {
            Schema::table('stage_custom_fields', function (Blueprint $table) {
                $table->dropColumn(['entity_type', 'is_required']);
            });
        }
        if (Schema::hasTable('pipeline_stage_automations')) {
            Schema::table('pipeline_stage_automations', function (Blueprint $table) {
                $table->dropColumn('entity_type');
            });
        }
    }
};
