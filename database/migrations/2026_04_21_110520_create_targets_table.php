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
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->string('target_name');
            $table->unsignedBigInteger('assigned_to')->comment('user_id of employee');
            $table->unsignedBigInteger('assigned_by')->comment('user_id of manager');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('target_value')->default(0);
            $table->integer('achieved_value')->default(0);
            $table->string('status')->default('Pending');
            $table->integer('workspace')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
