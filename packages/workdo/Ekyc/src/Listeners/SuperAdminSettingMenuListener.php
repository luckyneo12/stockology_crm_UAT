<?php

namespace Workdo\Ekyc\Listeners;

use App\Events\SuperAdminSettingMenuEvent;

class SuperAdminSettingMenuListener
{
    public function handle(SuperAdminSettingMenuEvent $event): void
    {
        $event->menu->add([
            'title' => __('eKYC Settings'),
            'name' => 'ekyc-settings',
            'order' => 1000,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => 'Ekyc',
            'permission' => 'ekyc manage'
        ]);
    }
}
