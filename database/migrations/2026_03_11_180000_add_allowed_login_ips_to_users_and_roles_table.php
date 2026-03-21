<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->text('allowed_login_ips')->nullable();
        });

        Schema::table('roles', function (Blueprint $blueprint) {
            $blueprint->text('allowed_login_ips')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $blueprint) {
            $blueprint->dropColumn('allowed_login_ips');
        });

        Schema::table('roles', function (Blueprint $blueprint) {
            $blueprint->dropColumn('allowed_login_ips');
        });
    }
};
