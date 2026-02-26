<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration 
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Truncate user_leads table to enforce single ownership logic
        // (Lead->user_id is the source of truth)
        if (Schema::hasTable('user_leads')) {
            DB::table('user_leads')->truncate();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    // Cannot restore deleted data
    }
};
