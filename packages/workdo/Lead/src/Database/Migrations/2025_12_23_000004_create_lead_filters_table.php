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
        if (!Schema::hasTable('lead_filters')) {
            Schema::create('lead_filters', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('user_id');
                $table->json('filters');
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
        Schema::dropIfExists('lead_filters');
    }
};
