<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEkycStagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ekyc_stages')) {
            Schema::create('ekyc_stages', function (Blueprint $table) {
                $table->id();
                $table->integer('pipeline_id');
                $table->string('name');
                $table->integer('order')->default(0);
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
        Schema::dropIfExists('ekyc_stages');
    }
}
