<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up()
    {
        if (!Schema::hasTable('chat_groups')) {
            Schema::create('chat_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('avatar')->nullable();
                $table->unsignedBigInteger('created_by');
                $table->unsignedBigInteger('workspace_id');
                $table->timestamps();

            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('chat_groups');
    }
};
