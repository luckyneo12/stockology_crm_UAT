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
        // Force the root URL and scheme from the .env APP_URL
        if (config('app.url') && config('app.url') !== 'http://localhost' && config('app.url') !== 'https://localhost') {
            \URL::forceScheme(parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https');
            \URL::forceRootUrl(config('app.url'));
        }
    }
}
