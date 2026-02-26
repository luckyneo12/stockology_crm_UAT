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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('designation_id')->comment('Reporting Manager ID');
                // Optional: Foreign key constraint if you want strict referential integrity
                // $table->foreign('parent_id')->references('id')->on('employees')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
             if (Schema::hasColumn('employees', 'parent_id')) {
                $table->dropColumn('parent_id');
            }
        });
    }
};
