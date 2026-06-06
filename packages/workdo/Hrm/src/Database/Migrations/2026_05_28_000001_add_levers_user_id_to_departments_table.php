<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds levers_user_id to departments table.
     * Each team has one dedicated "Levers Account" user.
     * When any team member is inactivated or deleted, their leads go to this account.
     */
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (!Schema::hasColumn('departments', 'levers_user_id')) {
                $table->unsignedBigInteger('levers_user_id')->nullable()->after('manager_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'levers_user_id')) {
                $table->dropColumn('levers_user_id');
            }
        });
    }
};
