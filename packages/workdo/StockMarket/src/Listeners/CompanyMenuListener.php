<?php

namespace Workdo\StockMarket\Listeners;

use App\Events\CompanyMenuEvent;

class CompanyMenuListener
{
    public function handle(CompanyMenuEvent $event): void
    {
        $module = 'StockMarket';
        $menu = $event->menu;

        $menu->add([
            'category' => 'General',
            'title' => __('Stock Dashboard'),
            'icon' => '',
            'name' => 'stockmarket-dashboard',
            'parent' => 'dashboard',
            'order' => 55,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'stockmarket.dashboard',
            'module' => $module,
            'permission' => 'stockmarket dashboard manage',
        ]);

        $menu->add([
            'category' => 'Stock',
            'title' => __('Stock Market'),
            'icon' => 'ti ti-chart-candle',
            'name' => 'stockmarket',
            'parent' => null,
            'order' => 550,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => '',
            'module' => $module,
            'permission' => 'stockmarket manage',
        ]);

        $menu->add([
            'category' => 'Stock',
            'title' => __('Signals / Calls'),
            'icon' => '',
            'name' => 'stock-signals',
            'parent' => 'stockmarket',
            'order' => 10,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'stock-signals.index',
            'module' => $module,
            'permission' => 'signal manage',
        ]);

        $menu->add([
            'category' => 'Stock',
            'title' => __('Create Signal'),
            'icon' => '',
            'name' => 'stock-signals-create',
            'parent' => 'stockmarket',
            'order' => 12,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'stock-signals.create',
            'module' => $module,
            'permission' => 'signal create',
        ]);

        $menu->add([
            'category' => 'Stock',
            'title' => __('Option Chain'),
            'icon' => '',
            'name' => 'stock-option-chain',
            'parent' => 'stockmarket',
            'order' => 13,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'stockmarket.option-chain',
            'module' => $module,
            'permission' => 'stockmarket dashboard manage',
        ]);

        $menu->add([
            'category' => 'Stock',
            'title' => __('Categories'),
            'icon' => '',
            'name' => 'stock-categories',
            'parent' => 'stockmarket',
            'order' => 15,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'stock-categories.index',
            'module' => $module,
            'permission' => 'stock category manage',
        ]);

        $menu->add([
            'category' => 'Stock',
            'title' => __('System Setup'),
            'icon' => '',
            'name' => 'stock-settings',
            'parent' => 'stockmarket',
            'order' => 20,
            'ignore_if' => [],
            'depend_on' => [],
            'route' => 'stockmarket.settings',
            'module' => $module,
            'permission' => 'stock setting manage',
        ]);
    }
}
