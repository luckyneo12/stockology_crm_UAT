<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;
use App\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = [
            'ekyc manage',
        ];

        foreach ($permissions as $name) {
            $permission = Permission::where('name', $name)->first();
            if (!$permission) {
                $permission = Permission::create([
                    'name' => $name,
                    'guard_name' => 'web',
                    'module' => 'Ekyc',
                ]);
            }

            $companyRole = Role::where('name', 'company')->first();
            if ($companyRole) {
                $permissionExists = $companyRole->permissions()->where('name', $name)->exists();
                if (!$permissionExists) {
                    $companyRole->givePermission($permission);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Permission::where('module', 'Ekyc')->delete();
    }
};
