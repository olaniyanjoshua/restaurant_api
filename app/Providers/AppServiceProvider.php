<?php

namespace App\Providers;

use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(UrlGenerator $url): void
    {
        // Render terminates TLS in front of the container, so requests reach
        // Laravel as plain HTTP. Without this, asset()/url() helpers would
        // generate http:// links in production and trigger mixed-content
        // warnings in the browser.
        if (env('APP_ENV') === 'production') {
            $url->forceScheme('https');
        }
    }
}
