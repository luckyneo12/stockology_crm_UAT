<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lead_tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_tasks', 'user_id')) {
                $table->integer('user_id')->default(0)->after('lead_id');
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
        Schema::table('lead_tasks', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
