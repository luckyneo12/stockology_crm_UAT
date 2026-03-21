<?php

namespace Workdo\StockMarket\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;
use Workdo\StockMarket\Entities\StockSignal;

class StockMarketPermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = StockSignal::stockPermissions();

        foreach ($permissions as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if (!$permission) {
                Permission::create([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                    'module' => 'StockMarket',
                    'created_by' => 0,
                ]);
            } else {
                $permission->module = 'StockMarket';
                $permission->save();
            }
        }
    }
}
