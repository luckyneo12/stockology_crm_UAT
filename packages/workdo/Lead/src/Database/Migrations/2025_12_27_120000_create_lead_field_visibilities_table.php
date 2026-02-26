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
        if (!Schema::hasTable('lead_field_visibilities')) {
            Schema::create('lead_field_visibilities', function (Blueprint $table) {
                $table->id();
                $table->string('field_name');
                $table->integer('role_id')->nullable();
                $table->integer('pipeline_id')->nullable();
                $table->integer('stage_id')->nullable();
                $table->enum('encryption_type', ['none', 'mask', 'hide'])->default('none');
                $table->enum('masking_type', ['partial', 'full', 'hide'])->default('partial')->after('encryption_type');
                $table->integer('workspace_id');
                $table->integer('created_by');
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
        Schema::dropIfExists('lead_field_visibilities');
    }
};
