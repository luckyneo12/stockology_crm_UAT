<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'extension_1')) {
                $table->string('extension_1')->nullable()->after('extension');
            }
            if (!Schema::hasColumn('users', 'extension_2')) {
                $table->string('extension_2')->nullable()->after('extension_1');
            }
            if (!Schema::hasColumn('users', 'active_extension')) {
                $table->integer('active_extension')->default(1)->after('extension_2');
            }
            if (!Schema::hasColumn('users', 'last_extension_edit')) {
                $table->timestamp('last_extension_edit')->nullable()->after('active_extension');
            }
        });

        // Optional: Migrate old extension to extension_1
        \DB::table('users')->whereNotNull('extension')->update([
            'extension_1' => \DB::raw('extension')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['extension_1', 'extension_2', 'active_extension', 'last_extension_edit']);
        });
    }
};
