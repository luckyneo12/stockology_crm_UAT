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
        Schema::create('ekyc_requests', function (Blueprint $table) {
            $table->id();
            $table->integer('lead_id');
            $table->string('digio_id')->nullable();
            $table->string('step_name');
            $table->string('status')->default('pending');
            $table->json('response_data')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('workspace_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekyc_requests');
    }
};
