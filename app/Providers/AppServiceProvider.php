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
        // Force the root URL and scheme globally to fix redirect issues
        if (!app()->runningInConsole() && config('app.url') && strpos(config('app.url'), 'localhost') === false) {
            \URL::forceScheme(parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https');
            \URL::forceRootUrl(config('app.url'));
        }
    }
}
