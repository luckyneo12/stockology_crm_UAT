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
        Schema::table('targets', function (Blueprint $table) {
            $table->unsignedBigInteger('responsible_user_id')->nullable()->after('assigned_by');
            $table->boolean('can_edit')->default(false)->after('responsible_user_id')->comment('Whether the responsible person can edit the target details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropColumn(['responsible_user_id', 'can_edit']);
        });
    }
};
