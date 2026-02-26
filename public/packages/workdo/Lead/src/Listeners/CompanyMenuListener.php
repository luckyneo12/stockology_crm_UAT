<?php

namespace Workdo\Lead\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    /**
     * Handle the event.
     */
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'Lead';
        $menu = $event->menu;
        $menu->add([
            'category' => 'General',
            'title' => __('CRM Dashboard'),
            'icon' => '',
            'name' => 'crm-dashboard',
            'parent' => 'dashboard',
            'order' => 50,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'lead.dashboard',
            'module' => $module,
            'permission' => 'crm dashboard manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('CRM'),
            'icon' => 'layers-difference',
            'name' => 'crm',
            'parent' => null,
            'order' => 500,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'crm manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('My Tasks'),
            'icon' => '',
            'name' => 'my-tasks',
            'parent' => 'crm',
            'order' => 510,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.my.tasks',
            'module' => $module,
            'permission' => 'lead manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('My Reminders'),
            'icon' => '',
            'name' => 'my-reminders',
            'parent' => 'crm',
            'order' => 520,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.my.reminders',
            'module' => $module,
            'permission' => 'lead manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Data Visibility'),
            'icon' => '',
            'name' => 'visibility-settings',
            'parent' => 'crm',
            'order' => 900,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.visibility.settings',
            'module' => $module,
            'permission' => 'crm manage',
            'is_admin' => true
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Lead'),
            'icon' => '',
            'name' => 'lead',
            'parent' => 'crm',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.index',
            'module' => $module,
            'permission' => 'lead manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Deal'),
            'icon' => '',
            'name' => 'deal',
            'parent' => 'crm',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'deals.index',
            'module' => $module,
            'permission' => 'deal manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Tasks'),
            'icon' => '',
            'name' => 'lead-tasks',
            'parent' => 'crm',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'lead_tasks.index',
            'module' => $module,
            'permission' => 'lead manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('System Setup'),
            'icon' => '',
            'name' => 'system-setup',
            'parent' => 'crm',
            'order' => 25,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'pipelines.index',
            'module' => $module,
            'permission' => 'crm manage'
        ]);
        // Lead Documents - Hidden as per user request
        // $menu->add([
        //     'category' => 'Sales',
        //     'title' => __('Lead Documents'),
        //     'icon' => '',
        //     'name' => 'lead-documents',
        //     'parent' => 'crm',
        //     'order' => 26,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'lead-documents.index',
        //     'module' => $module,
        //     'permission' => 'crm manage'
        // ]);
        
        // Lead Custom Fields - Replaced by Lead Layout Builder
        // $menu->add([
        //     'category' => 'Sales',
        //     'title' => __('Lead Custom Fields'),
        //     'icon' => '',
        //     'name' => 'lead-custom-fields',
        //     'parent' => 'crm',
        //     'order' => 27,
        //     'ignore_if' => [],
        //     'depend_on' => [],
        //     'route' => 'lead-custom-fields.index',
        //     'module' => $module,
        //     'permission' => 'crm manage',
        //     'is_admin' => true
        // ]);
        
        $menu->add([
            'category' => 'Sales',
            'title' => __('Lead Layout Builder'),
            'icon' => '',
            'name' => 'lead-layout-builder',
            'parent' => 'crm',
            'order' => 27,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'lead-builder.index',
            'module' => $module,
            'permission' => 'crm manage',
            'is_admin' => true // Restrict to Admin/Company only
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Duplicates'),
            'icon' => '',
            'name' => 'lead-duplicates',
            'parent' => 'crm',
            'order' => 28,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.duplicates',
            'module' => $module,
            'permission' => 'lead import'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Report'),
            'icon' => '',
            'name' => 'crm-report',
            'parent' => 'crm',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' =>'crm report manage'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Lead'),
            'icon' => '',
            'name' => 'lead-report',
            'parent' => 'crm-report',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'report.lead',
            'module' => $module,
            'permission' => 'lead report'
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Deal'),
            'icon' => '',
            'name' => 'deal-report',
            'parent' => 'crm-report',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'report.deal',
            'module' => $module,
            'permission' => 'deal report'
        ]);
    }
}
