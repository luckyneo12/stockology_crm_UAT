<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEkycCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ekyc_custom_fields')) {
            Schema::create('ekyc_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type');
                $table->integer('order')->default(0);
                $table->integer('workspace_id');
                $table->integer('created_by');
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
        Schema::dropIfExists('ekyc_custom_fields');
    }
}
