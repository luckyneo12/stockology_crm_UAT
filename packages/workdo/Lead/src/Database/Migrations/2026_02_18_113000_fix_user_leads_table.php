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
        // 1. Truncate the pivot table to remove bad/duplicate data
        DB::table('user_leads')->truncate();

        // 2. Insert fresh data based strictly on leads.user_id
        // We only take leads that have a valid user_id assigned AND exist in users table
        $leads = DB::table('leads')
            ->join('users', 'leads.user_id', '=', 'users.id')
            ->select('leads.id', 'leads.user_id')
            ->get();

        foreach ($leads as $lead) {
            DB::table('user_leads')->insert([
                'user_id' => $lead->user_id,
                'lead_id' => $lead->id,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    // No real way to reverse this safely as we destroyed the old partial data.
    // But logic is sound: leads.user_id IS the source of truth for ownership.
    }
};
