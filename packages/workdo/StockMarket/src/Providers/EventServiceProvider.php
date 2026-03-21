<?php

namespace Workdo\StockMarket\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use App\Events\CompanyMenuEvent;
use App\Events\CompanySettingMenuEvent;
use App\Events\GivePermissionToRole;
use App\Events\DefaultData;
use Workdo\StockMarket\Listeners\CompanyMenuListener;
use Workdo\StockMarket\Listeners\CompanySettingMenuListener;
use Workdo\StockMarket\Listeners\GiveRoleToPermission;
use Workdo\StockMarket\Listeners\DataDefault;

class EventServiceProvider extends Provider
{
    protected $listen = [
        CompanyMenuEvent::class => [
            CompanyMenuListener::class,
        ],
        CompanySettingMenuEvent::class => [
            CompanySettingMenuListener::class,
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
        DefaultData::class => [
            DataDefault::class,
        ],
    ];

    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
