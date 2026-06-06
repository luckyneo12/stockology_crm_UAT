<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Find all users where name ends with ' Lever' or email ends with '.lever@teamaccount.local'
        $users = DB::table('users')
            ->where('email', 'like', '%.lever@teamaccount.local')
            ->orWhere('name', 'like', '% Lever')
            ->get();

        foreach ($users as $user) {
            $newName = preg_replace('/(\s+)Lever$/i', '$1Leaver', $user->name);
            $newEmail = preg_replace('/\.lever@teamaccount\.local$/i', '.leaver@teamaccount.local', $user->email);
            $newType = preg_replace('/(\s+)Lever$/i', '$1Leaver', $user->type);

            DB::table('users')->where('id', $user->id)->update([
                'name' => $newName,
                'email' => $newEmail,
                'type' => $newType,
            ]);

            // 2. Update employee records matching the user
            DB::table('employees')->where('user_id', $user->id)->update([
                'name' => $newName,
                'email' => $newEmail,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration needed
    }
};
