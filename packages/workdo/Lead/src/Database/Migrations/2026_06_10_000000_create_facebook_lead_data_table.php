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
        if (!Schema::hasTable('facebook_lead_data')) {
            Schema::create('facebook_lead_data', function (Blueprint $table) {
                $table->id();
                $table->string('rule_id');
                $table->string('leadgen_id')->nullable();
                $table->string('page_id')->nullable();
                $table->string('form_id')->nullable();
                $table->longText('payload')->nullable();
                $table->string('status')->default('pending'); // pending, converted, failed
                $table->text('error_reason')->nullable();
                $table->integer('assigned_user_id')->nullable();
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
        Schema::dropIfExists('facebook_lead_data');
    }
};
