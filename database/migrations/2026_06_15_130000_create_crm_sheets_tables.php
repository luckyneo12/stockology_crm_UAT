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
        if (!Schema::hasTable('crm_sheets')) {
            Schema::create('crm_sheets', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->integer('workspace_id');
                $table->unsignedBigInteger('created_by');
                $table->longText('data')->nullable(); // Cell structure & values JSON
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('crm_sheet_collaborators')) {
            Schema::create('crm_sheet_collaborators', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('sheet_id');
                $table->unsignedBigInteger('user_id');
                $table->string('status')->default('pending'); // pending, accepted
                $table->timestamps();

                $table->foreign('sheet_id')->references('id')->on('crm_sheets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_sheet_collaborators');
        Schema::dropIfExists('crm_sheets');
    }
};
