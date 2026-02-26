<?php

namespace Workdo\Ekyc\Providers;

use Illuminate\Support\ServiceProvider;

class EkycServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Ekyc';
    protected $moduleNameLower = 'ekyc';

    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
    }

    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'ekyc');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
