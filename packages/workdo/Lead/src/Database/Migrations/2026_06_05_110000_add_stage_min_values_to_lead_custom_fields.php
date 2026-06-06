<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStageMinValuesToLeadCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_custom_fields', 'stage_min_values')) {
                $table->text('stage_min_values')->nullable()->after('required_stages');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_custom_fields', function (Blueprint $table) {
            if (Schema::hasColumn('lead_custom_fields', 'stage_min_values')) {
                $table->dropColumn('stage_min_values');
            }
        });
    }
}
