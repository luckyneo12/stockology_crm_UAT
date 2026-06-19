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
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            if (!Schema::hasColumn('webhook_endpoints', 'auto_convert')) {
                $table->integer('auto_convert')->default(1)->after('stage_id');
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
        Schema::table('webhook_endpoints', function (Blueprint $table) {
            if (Schema::hasColumn('webhook_endpoints', 'auto_convert')) {
                $table->dropColumn('auto_convert');
            }
        });
    }
};
