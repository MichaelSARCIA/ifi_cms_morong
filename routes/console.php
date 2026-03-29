<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\SystemSetting;

try {
    $settings = SystemSetting::pluck('value', 'key')->toArray();
    $backupSchedule = $settings['backup_schedule'] ?? 'none';
    $backupTime = $settings['backup_time'] ?? '00:00';

    if ($backupSchedule === 'daily') {
        Schedule::command('backup:database')->dailyAt($backupTime);
    } elseif ($backupSchedule === 'weekly') {
        Schedule::command('backup:database')->weeklyOn(0, $backupTime);
    } elseif ($backupSchedule === 'monthly') {
        Schedule::command('backup:database')->monthlyOn(1, $backupTime);
    }
} catch (\Exception $e) {
    // Database might not be migrated or available yet during setup
}
