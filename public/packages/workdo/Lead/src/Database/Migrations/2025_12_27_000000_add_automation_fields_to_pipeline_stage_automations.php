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
        if (Schema::hasTable('pipeline_stage_automations')) {
            Schema::table('pipeline_stage_automations', function (Blueprint $table) {
                if (!Schema::hasColumn('pipeline_stage_automations', 'is_auto_task')) {
                    $table->boolean('is_auto_task')->default(false)->after('target_user_id');
                }
                if (!Schema::hasColumn('pipeline_stage_automations', 'auto_task_name')) {
                    $table->string('auto_task_name')->nullable()->after('is_auto_task');
                }
                if (!Schema::hasColumn('pipeline_stage_automations', 'auto_task_priority')) {
                    $table->string('auto_task_priority')->nullable()->after('auto_task_name');
                }
                if (!Schema::hasColumn('pipeline_stage_automations', 'auto_task_duration')) {
                    $table->integer('auto_task_duration')->nullable()->after('auto_task_priority');
                }
                if (!Schema::hasColumn('pipeline_stage_automations', 'is_auto_reminder')) {
                    $table->boolean('is_auto_reminder')->default(false)->after('auto_task_duration');
                }
                if (!Schema::hasColumn('pipeline_stage_automations', 'auto_reminder_title')) {
                    $table->string('auto_reminder_title')->nullable()->after('is_auto_reminder');
                }
                if (!Schema::hasColumn('pipeline_stage_automations', 'auto_reminder_duration')) {
                    $table->integer('auto_reminder_duration')->nullable()->after('auto_reminder_title');
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
        if (Schema::hasTable('pipeline_stage_automations')) {
            Schema::table('pipeline_stage_automations', function (Blueprint $table) {
                $table->dropColumn([
                    'is_auto_task',
                    'auto_task_name',
                    'auto_task_priority',
                    'auto_task_duration',
                    'is_auto_reminder',
                    'auto_reminder_title',
                    'auto_reminder_duration',
                ]);
            });
        }
    }
};
