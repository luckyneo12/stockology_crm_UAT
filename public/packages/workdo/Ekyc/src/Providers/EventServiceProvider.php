<?php

namespace Workdo\Ekyc\Providers;

use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\SuperAdminMenuEvent;
use App\Events\SuperAdminSettingEvent;
use App\Events\SuperAdminSettingMenuEvent;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Workdo\Ekyc\Listeners\CompanyMenuListener;
use Workdo\Ekyc\Listeners\CompanySettingListener;
use Workdo\Ekyc\Listeners\CompanySettingMenuListener;
use Workdo\Ekyc\Listeners\SuperAdminMenuListener;
use Workdo\Ekyc\Listeners\SuperAdminSettingListener;
use Workdo\Ekyc\Listeners\SuperAdminSettingMenuListener;

class EventServiceProvider extends Provider
{
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class
        ],
        CompanySettingEvent::class => [
            CompanySettingListener::class
        ],
        SuperAdminMenuEvent::class => [
            SuperAdminMenuListener::class
        ],
        SuperAdminSettingMenuEvent::class => [
            SuperAdminSettingMenuListener::class
        ],
        SuperAdminSettingEvent::class => [
            SuperAdminSettingListener::class
        ],
    ];
}
