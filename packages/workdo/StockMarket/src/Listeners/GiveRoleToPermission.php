<?php

namespace Workdo\StockMarket\Listeners;

use App\Events\GivePermissionToRole;
use Workdo\StockMarket\Entities\StockSignal;

class GiveRoleToPermission
{
    public function handle(GivePermissionToRole $event)
    {
        $role_id = $event->role_id;
        $rolename = $event->rolename;
        $user_module = $event->user_module;

        if (!empty($user_module)) {
            if (in_array('StockMarket', $user_module)) {
                StockSignal::GivePermissionToRoles($role_id, $rolename);
            }
        }
    }
}
