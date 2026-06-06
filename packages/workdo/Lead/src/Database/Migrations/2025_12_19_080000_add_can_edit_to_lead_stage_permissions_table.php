<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('lead_stage_permissions')) {
            Schema::table('lead_stage_permissions', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_stage_permissions', 'can_edit')) {
                    $table->boolean('can_edit')->default(true)->after('can_move');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('lead_stage_permissions')) {
            Schema::table('lead_stage_permissions', function (Blueprint $table) {
                if (Schema::hasColumn('lead_stage_permissions', 'can_edit')) {
                    $table->dropColumn('can_edit');
                }
            });
        }
    }
};
