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
        if (!Schema::hasTable('orion_lead_logs')) {
            Schema::create('orion_lead_logs', function (Blueprint $table) {
                $table->id();
                $table->integer('lead_id')->nullable();
                $table->string('client_code')->nullable();
                $table->string('api_type')->nullable(); // fetch_details, post_ekyc, post_modify
                $table->longText('request_payload')->nullable();
                $table->longText('response_payload')->nullable();
                $table->string('status')->default('pending'); // pending, success, failed
                $table->text('error_reason')->nullable();
                $table->integer('workspace_id');
                $table->integer('created_by')->nullable();
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
        Schema::dropIfExists('orion_lead_logs');
    }
};
