<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibilityToLeadCustomFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_custom_fields', function (Blueprint $table) {
            $table->longText('visible_stages')->nullable()->after('options');
            $table->longText('visible_roles')->nullable()->after('visible_stages');
            $table->boolean('is_filterable')->default(0)->after('visible_roles');
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
            $table->dropColumn(['visible_stages', 'visible_roles', 'is_filterable']);
        });
    }
}
