<?php

namespace Workdo\Ekyc\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    public function handle(CompanySettingMenuEvent $event): void
    {
        $module = 'Ekyc';
        $menu = $event->menu;
        $menu->add([
            'title' => __('eKYC Settings'),
            'name' => 'ekyc-settings',
            'order' => 600,
            'module' => $module,
            'navigation' => 'ekyc-settings'
        ]);
    }
}
