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
        if (!Schema::hasTable('stage_custom_fields')) {
            Schema::create('stage_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->integer('stage_id');
                $table->integer('custom_field_id');
                $table->integer('created_by');
                $table->integer('workspace_id');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('pipeline_stage_automations')) {
            Schema::create('pipeline_stage_automations', function (Blueprint $table) {
                $table->id();
                $table->integer('pipeline_id');
                $table->integer('stage_id');
                $table->integer('target_department_id');
                $table->integer('target_user_id')->nullable();
                $table->integer('created_by');
                $table->integer('workspace_id');
                $table->timestamps();
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
        Schema::dropIfExists('stage_custom_fields');
        Schema::dropIfExists('pipeline_stage_automations');
    }
};
