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
        $addon = \App\Models\AddOn::where('module', 'StockMarket')->first();
        if (!$addon) {
            \App\Models\AddOn::create([
                'module' => 'StockMarket',
                'name' => 'stockmarket',
                'monthly_price' => 0,
                'yearly_price' => 0,
                'is_enable' => 1,
                'image' => '/packages/workdo/StockMarket/favicon.png',
                'package_name' => 'stockmarket'
            ]);
        } else {
            $addon->is_enable = 1;
            $addon->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $addon = \App\Models\AddOn::where('module', 'StockMarket')->first();
        if ($addon) {
            $addon->delete();
        }
    }
};
