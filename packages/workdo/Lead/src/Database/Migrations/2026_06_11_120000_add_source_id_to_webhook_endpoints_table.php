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
            if (!Schema::hasColumn('webhook_endpoints', 'source_id')) {
                $table->integer('source_id')->nullable()->after('stage_id');
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
            if (Schema::hasColumn('webhook_endpoints', 'source_id')) {
                $table->dropColumn('source_id');
            }
        });
    }
};
