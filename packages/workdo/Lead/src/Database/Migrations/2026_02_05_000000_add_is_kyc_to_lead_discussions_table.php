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
        Schema::table('lead_discussions', function (Blueprint $table) {
            if (!Schema::hasColumn('lead_discussions', 'is_kyc')) {
                $table->boolean('is_kyc')->default(false)->after('comment');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lead_discussions', function (Blueprint $table) {
            if (Schema::hasColumn('lead_discussions', 'is_kyc')) {
                $table->dropColumn('is_kyc');
            }
        });
    }
};
