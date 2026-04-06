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
    $backupDayOfWeek = $settings['backup_day_of_week'] ?? 'Sunday';
    $backupDayOfMonth = $settings['backup_day_of_month'] ?? '1';

    // Map day name to integer (0=Sunday, 6=Saturday)
    $daysMap = [
        'Sunday' => 0,
        'Monday' => 1,
        'Tuesday' => 2,
        'Wednesday' => 3,
        'Thursday' => 4,
        'Friday' => 5,
        'Saturday' => 6,
    ];
    $dayNum = $daysMap[$backupDayOfWeek] ?? 0;

    if ($backupSchedule === 'daily') {
        Schedule::command('backup:database')->dailyAt($backupTime);
    } elseif ($backupSchedule === 'weekly') {
        Schedule::command('backup:database')->weeklyOn($dayNum, $backupTime);
    } elseif ($backupSchedule === 'monthly') {
        Schedule::command('backup:database')->monthlyOn((int)$backupDayOfMonth, $backupTime);
    }
} catch (\Exception $e) {
    // Database might not be migrated or available yet during setup
}
