<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Classes\Module;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('module', function ($app) {
            return new Module();
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Absolute force override for Live Server
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'stockologysecurities.in') {
            \URL::forceScheme('https');
            \URL::forceRootUrl('https://stockologysecurities.in');
        }
    }
}
