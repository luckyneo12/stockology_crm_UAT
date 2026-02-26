<?php

namespace Workdo\Ekyc\Listeners;

use App\Events\SuperAdminSettingEvent;

class SuperAdminSettingListener
{
    public function handle(SuperAdminSettingEvent $event): void
    {
        $event->html .= view('ekyc::settings', ['settings' => $event->settings])->render();
    }
}
