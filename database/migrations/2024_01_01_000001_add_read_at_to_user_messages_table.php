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
        Schema::table('user_messages', function (Blueprint $table) {
            // Check if read_at column doesn't exist, then add it
            if (!Schema::hasColumn('user_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('body');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_messages', function (Blueprint $table) {
            $table->dropColumn('read_at');
        });
    }
};
