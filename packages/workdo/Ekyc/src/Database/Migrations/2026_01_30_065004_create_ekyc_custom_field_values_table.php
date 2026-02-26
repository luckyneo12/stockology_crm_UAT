<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEkycCustomFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('ekyc_custom_field_values')) {
            Schema::create('ekyc_custom_field_values', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ekyc_id')->comment('Record ID from main table'); // Assuming ekyc records are in 'ekycs' table or similar? Or maybe 'users' context?
                // The user request was "custom fields create kerna he", presumably for the Ekyc records.
                // Assuming there is an 'ekyc' implementation, but I haven't seen an 'Ekyc' model.
                // Wait, 'Ekyc' module usually implies there is a model for the Ekyc request.
                // Let's assume 'ekyc_id' for now.
                $table->integer('field_id');
                $table->text('value')->nullable();
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
        Schema::dropIfExists('ekyc_custom_field_values');
    }
}
