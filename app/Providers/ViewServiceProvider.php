<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share service types with the sidebar for dynamic navigation (Cached for performance)
        \Illuminate\Support\Facades\View::composer('layouts.partials.sidebar', function ($view) {
            $service_types = \Illuminate\Support\Facades\Cache::remember('service_types_sidebar', 3600, function () {
                return \App\Models\ServiceType::all();
            });
            $view->with('service_types', $service_types);
        });
    }
}
