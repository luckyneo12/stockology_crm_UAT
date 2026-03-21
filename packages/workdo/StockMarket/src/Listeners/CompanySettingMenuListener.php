<?php

namespace Workdo\StockMarket\Listeners;

use App\Events\CompanySettingMenuEvent;

class CompanySettingMenuListener
{
    public function handle(CompanySettingMenuEvent $event): void
    {
        // No company setting menu items needed for now
    }
}
