<?php

namespace Workdo\Ekyc\Listeners;

use App\Events\SuperAdminMenuEvent;

class SuperAdminMenuListener
{
    public function handle(SuperAdminMenuEvent $event): void
    {
        $event->menu->add([
            'category' => 'Operations',
            'title' => __('eKYC Reports'),
            'icon' => 'user-check',
            'name' => 'ekyc-reports',
            'parent' => null,
            'order' => 100,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ekyc.reports',
            'module' => 'Ekyc',
            'permission' => 'ekyc manage'
        ]);
    }
}
