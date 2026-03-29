<?php

namespace App\Providers;

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
    public function boot(): void
    {
        // Set timezone from .env
        $timezone = env('APP_TIMEZONE', 'Asia/Manila');
        config(['app.timezone' => $timezone]);
        date_default_timezone_set($timezone);
    }
}
