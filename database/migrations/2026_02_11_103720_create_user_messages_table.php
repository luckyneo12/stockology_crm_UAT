<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('from_id')->unsigned();
            $table->bigInteger('to_id')->unsigned();
            $table->text('body')->nullable();
            $table->string('attachment')->nullable();
            $table->boolean('is_seen')->default(0);
            $table->integer('workspace_id')->nullable();
            $table->timestamps();

            $table->index(['from_id', 'to_id']);
            $table->index(['to_id', 'is_seen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_messages');
    }
};
