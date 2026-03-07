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
        // 1. Re-register StockMarket in add_ons table if not exists
        \DB::table('add_ons')->updateOrInsert(
            ['module' => 'StockMarket'],
            [
                'name' => 'StockMarket',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'image' => 'main_img.png',
                'is_enable' => 1,
                'package_name' => 'stockmarket',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // 2. Grant permissions to existing 'company' role
        if (class_exists(\Workdo\StockMarket\Entities\StockSignal::class)) {
            $companyRole = \App\Models\Role::where('name', 'company')->first();
            if ($companyRole) {
                \Workdo\StockMarket\Entities\StockSignal::GivePermissionToRoles($companyRole->id, $companyRole->name);
            }
        }
    }

    public function down(): void
    {
        \DB::table('add_ons')->where('module', 'StockMarket')->delete();
    }
};
