<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissionName = 'lead kyc comment';
        $module = 'Lead';

        $check = Permission::where('name', $permissionName)->where('module', $module)->first();
        if (!$check) {
            $permission = Permission::create([
                'name' => $permissionName,
                'guard_name' => 'web',
                'module' => $module,
                'created_by' => 0,
            ]);

            // Give permission to company role by default
            $companyRole = Role::where('name', 'company')->first();
            if ($companyRole && !$companyRole->hasPermission($permissionName)) {
                $companyRole->givePermission($permission);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissionName = 'lead kyc comment';
        $module = 'Lead';
        
        $permission = Permission::where('name', $permissionName)->where('module', $module)->first();
        if ($permission) {
            $permission->delete();
        }
    }
};
