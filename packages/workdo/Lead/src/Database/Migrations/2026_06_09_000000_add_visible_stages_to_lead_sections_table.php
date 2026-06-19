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
        if (Schema::hasTable('lead_sections')) {
            Schema::table('lead_sections', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_sections', 'visible_stages')) {
                    $table->longText('visible_stages')->nullable()->after('is_system');
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
        if (Schema::hasTable('lead_sections')) {
            Schema::table('lead_sections', function (Blueprint $table) {
                if (Schema::hasColumn('lead_sections', 'visible_stages')) {
                    $table->dropColumn('visible_stages');
                }
            });
        }
    }
};
