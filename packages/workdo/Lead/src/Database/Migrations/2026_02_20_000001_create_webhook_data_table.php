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
        if (!Schema::hasTable('webhook_data')) {
            Schema::create('webhook_data', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('webhook_endpoint_id');
                $table->longText('payload')->nullable();
                $table->string('status')->default('pending'); // pending, converted, rejected
                $table->integer('assigned_user_id')->nullable();
                $table->integer('workspace_id');
                $table->timestamps();

                $table->foreign('webhook_endpoint_id')->references('id')->on('webhook_endpoints')->onDelete('cascade');
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
        Schema::dropIfExists('webhook_data');
    }
};
