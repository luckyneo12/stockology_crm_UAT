<?php

namespace Workdo\Ekyc\Listeners;

use App\Events\CompanySettingEvent;

class CompanySettingListener
{
    public function handle(CompanySettingEvent $event): void
    {
        $module = 'Ekyc';
        $html = view('ekyc::settings')->render();
        $event->html->add([
            'html' => $html,
            'order' => 600,
            'module' => $module,
            'navigation' => 'ekyc-settings'
        ]);
    }
}
