<?php

namespace Workdo\Ekyc\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    public function handle(CompanyMenuEvent $event): void
    {
        \Log::info('CompanyMenuListener Ekyc actually running');
        $module = 'Ekyc';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('eKYC Dashboard'),
            'icon' => '',
            'name' => 'ekyc-dashboard',
            'parent' => 'dashboard',
            'order' => 40,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ekyc.dashboard',
            'module' => $module,
            'permission' => 'ekyc manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('eKyc'),
            'icon' => 'user-check',
            'name' => 'ekyc',
            'parent' => null,
            'order' => 600,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'ekyc manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('eKYC List'),
            'icon' => '',
            'name' => 'ekyc-list',
            'parent' => 'ekyc',
            'order' => 605,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ekyc.index',
            'module' => $module,
            'permission' => 'ekyc manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('Submissions'),
            'icon' => '',
            'name' => 'ekyc-submissions',
            'parent' => 'ekyc',
            'order' => 607,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ekyc.admin.submissions.index',
            'module' => $module,
            'permission' => 'ekyc manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('System Setting'),
            'icon' => '',
            'name' => 'system-setting',
            'parent' => 'ekyc',
            'order' => 610,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'ekyc.settings',
            'module' => $module,
            'permission' => 'ekyc manage'
        ]);
    }
}
