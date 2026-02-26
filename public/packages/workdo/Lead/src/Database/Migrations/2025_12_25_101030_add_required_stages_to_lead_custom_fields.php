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
        Schema::table('lead_custom_fields', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_custom_fields', 'required_stages')) {
                $table->longText('required_stages')->nullable()->after('visible_stages');
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
            if (Schema::hasColumn('lead_custom_fields', 'required_stages')) {
                $table->dropColumn('required_stages');
            }
        });
    }
};
