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
        if(!Schema::hasTable('lead_custom_fields'))
        {
            Schema::create('lead_custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type'); // text, number, date, textarea, select
                $table->text('options')->nullable(); // For select type, comma separated
                $table->integer('order')->default(0);
                $table->boolean('is_required')->default(0);
                $table->integer('workspace_id');
                $table->integer('created_by');
                $table->timestamps();
            });
        }

        if(!Schema::hasTable('lead_custom_field_values'))
        {
            Schema::create('lead_custom_field_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('lead_id');
                $table->unsignedBigInteger('field_id');
                $table->text('value')->nullable();
                $table->timestamps();
                
                $table->foreign('lead_id')->references('id')->on('leads')->onDelete('cascade');
                $table->foreign('field_id')->references('id')->on('lead_custom_fields')->onDelete('cascade');
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
        Schema::dropIfExists('lead_custom_field_values');
        Schema::dropIfExists('lead_custom_fields');
    }
};
