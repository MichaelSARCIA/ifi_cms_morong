<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

class SettingsServiceProvider extends ServiceProvider
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
        // Check if the table exists to prevent errors during migrations
        if (Schema::hasTable('system_settings')) {
            // Share settings with all views
            $settings = SystemSetting::pluck('value', 'key')->toArray();
            View::share('global_settings', $settings);
        }
    }
}
