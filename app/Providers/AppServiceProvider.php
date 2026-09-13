<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

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
    public function boot(): void
    {
        Carbon::setLocale('id');

        // Paksa skema HTTPS jika diakses lewat Cloudflare Tunnel / Reverse Proxy SSL
        if (
            app()->environment('production') ||
            request()->header('x-forwarded-proto') === 'https' ||
            str_contains(request()->header('host', ''), 'trycloudflare.com')
        ) {
            URL::forceScheme('https');
        }
    }
}
