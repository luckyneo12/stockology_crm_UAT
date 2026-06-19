<?php

namespace Workdo\Lead\Listeners;

use App\Events\CompanyMenuEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Workdo\Lead\Entities\WebhookEndpoint;
use Workdo\Lead\Entities\WebhookData;

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
            'title' => __('Bulk Actions'),
            'icon' => 'layers-subtract',
            'name' => 'bulk-actions',
            'parent' => null,
            'order' => 505,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.list',
            'module' => $module,
            'permission' => 'lead manage'
        ]);
        $menu->add([
            'category' => 'General',
            'title' => __('Sheets'),
            'icon' => 'table',
            'name' => 'crm-sheets',
            'parent' => null,
            'order' => 3,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'crm.sheets.index',
            'module' => $module,
            'permission' => 'lead manage'
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
            'title' => __('WhatsApp Chat'),
            'icon' => '',
            'name' => 'whatsapp-chats',
            'parent' => 'crm',
            'order' => 26,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'whatsapp.chat.index',
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
        $user = Auth::user();
        $isCompany = $user->type == 'company' || $user->type == 'super admin';

        $canSeeEndpoints = false;
        if ($isCompany) {
            $canSeeEndpoints = true;
        } else {
            $canSeeEndpoints = WebhookEndpoint::whereJsonContains('edit_permissions', (string) $user->id)
                ->orWhere('created_by', $user->id)->exists();
        }

        $canSeeData = false;
        if ($isCompany) {
            $canSeeData = true;
        } else {
            $canSeeData = WebhookEndpoint::whereJsonContains('view_permissions', (string) $user->id)
                ->orWhereJsonContains('edit_permissions', (string) $user->id)
                ->orWhere('created_by', $user->id)->exists();
            if (!$canSeeData) {
                $canSeeData = WebhookData::where('assigned_user_id', $user->id)->exists();
            }
        }

        $canSeeFbData = $isCompany || Auth::user()->isAbleTo('crm manage');
        $canSeeOrionData = $isCompany || Auth::user()->isAbleTo('crm manage');

        // Automation top-level parent menu condition
        $canSeeAutomation = $canSeeEndpoints || $canSeeOrionData || ($isCompany || Auth::user()->isAbleTo('crm manage'));
        if ($canSeeAutomation) {
            $menu->add([
                'category' => 'Sales',
                'title' => __('Automation'),
                'icon' => 'cpu',
                'name' => 'automation',
                'parent' => null,
                'order' => 502,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => '',
                'module' => $module,
                'permission' => '' // Dynamically checked via child visibility
            ]);
        }

        if ($canSeeEndpoints) {
            $menu->add([
                'category' => 'Sales',
                'title' => __('Webhook Endpoints'),
                'icon' => '',
                'name' => 'webhook-endpoints',
                'parent' => 'automation',
                'order' => 50,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'webhook-endpoints.index',
                'module' => $module,
                'permission' => '' // Conditional access managed above
            ]);
        }

        if ($isCompany) {
            $menu->add([
                'category' => 'Sales',
                'title' => __('WhatsApp Settings'),
                'icon' => '',
                'name' => 'whatsapp-config',
                'parent' => 'automation',
                'order' => 60,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'whatsapp-config.index',
                'module' => $module,
                'permission' => ''
            ]);
        }

        if ($canSeeData) {
            $menu->add([
                'category' => 'Sales',
                'title' => __('Webhook Data'),
                'icon' => '',
                'name' => 'webhook-data',
                'parent' => 'crm',
                'order' => 27,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'webhook-data.index',
                'module' => $module,
                'permission' => '' // We are manually checking permission above
            ]);
        }

        if ($canSeeFbData) {
            $menu->add([
                'category' => 'Sales',
                'title' => __('Facebook Lead Data'),
                'icon' => '',
                'name' => 'facebook-lead-data',
                'parent' => 'crm',
                'order' => 28,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'facebook-lead-data.index',
                'module' => $module,
                'permission' => '' // Checked manually above
            ]);
        }

        if ($canSeeOrionData) {
            $menu->add([
                'category' => 'Sales',
                'title' => __('Orion EKYC Logs'),
                'icon' => '',
                'name' => 'orion-lead-logs',
                'parent' => 'automation',
                'order' => 40,
                'ignore_if' => [],
                'depend_on' => [],
                'route' => 'orion-lead-logs.index',
                'module' => $module,
                'permission' => ''
            ]);
        }

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
            'title' => __('E-Sign Templates'),
            'icon' => '',
            'name' => 'esign-templates',
            'parent' => 'automation',
            'order' => 30,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'esign-templates.index',
            'module' => $module,
            'permission' => 'crm manage',
            'is_admin' => true
        ]);
        $menu->add([
            'category' => 'Sales',
            'title' => __('Automations'),
            'icon' => '',
            'name' => 'crm-automations',
            'parent' => 'automation',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'crm.automations.index',
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
            'title' => __('Bulk Import'),
            'icon' => '',
            'name' => 'lead-bulk-import',
            'parent' => 'crm',
            'order' => 29,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'leads.bulk.import',
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
            'permission' => 'crm report manage'
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
