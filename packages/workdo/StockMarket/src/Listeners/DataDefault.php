<?php

namespace Workdo\StockMarket\Listeners;

use App\Events\DefaultData;
use Workdo\StockMarket\Entities\StockSignal;

class DataDefault
{
    public function handle(DefaultData $event)
    {
        $company_id = $event->company_id;
        $workspace_id = $event->workspace_id;
        $user_module = $event->user_module;

        if (!empty($user_module)) {
            if (in_array('StockMarket', $user_module)) {
                StockSignal::defaultdata($company_id, $workspace_id);
            }
        }
    }
}
