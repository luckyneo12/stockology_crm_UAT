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
        if (!Schema::hasTable('webhook_endpoints')) {
            Schema::create('webhook_endpoints', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('url')->unique();
                $table->integer('created_by');
                $table->integer('assign_to')->nullable();
                $table->integer('pipeline_id')->nullable();
                $table->integer('stage_id')->nullable();
                $table->json('view_permissions')->nullable();
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
        Schema::dropIfExists('webhook_endpoints');
    }
};
