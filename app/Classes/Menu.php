<?php

namespace App\Classes;

class Menu
{
    public $menu = [];
    public $user;
    public $modules;

    public function __construct($user)
    {
        $this->user = $user;
        // Bypass Module system temporarily
        $this->modules = ['Base', 'Lead', 'Hrm', 'Ekyc'];
    }

    public function add(array $array): void {
        // If this item is marked as admin-only, only show to company owner or super admin
        $isAdminOnly = !empty($array['is_admin']) && $array['is_admin'] === true;
        if ($isAdminOnly) {
            $userType = $this->user->type ?? '';
            if (!in_array($userType, ['company', 'super admin'])) {
                return; // Hide from non-company users
            }
        }

        if(in_array($array['module'],$this->modules) && ((empty($array['permission'])) ||  $this->user->isAbleTo($array['permission']))){
            $this->menu[] = $array;
        }
    }
}
