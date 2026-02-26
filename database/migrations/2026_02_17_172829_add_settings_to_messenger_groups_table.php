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
        Schema::table('messenger_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('messenger_groups', 'allow_images')) {
                $table->boolean('allow_images')->default(true)->after('workspace_id');
            }
            if (!Schema::hasColumn('messenger_groups', 'allow_files')) {
                $table->boolean('allow_files')->default(true)->after('allow_images');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messenger_groups', function (Blueprint $table) {
            $table->dropColumn(['allow_images', 'allow_files']);
        });
    }
};
