<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\AuditLogger;
use Carbon\Carbon;
use Ifsnop\Mysqldump\Mysqldump;

// Manual include as fallback for environment issues
if (!class_exists(Mysqldump::class)) {
    $path = base_path('vendor/ifsnop/mysqldump-php/src/Ifsnop/Mysqldump/Mysqldump.php');
    if (file_exists($path)) {
        require_once $path;
    }
}

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:database {--force : Ignore schedule and force a backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a database backup and uploads it based on system settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $settings = SystemSetting::pluck('value', 'key')->toArray();
        $schedule = $settings['backup_schedule'] ?? 'none';
        
        if ($schedule === 'none' && !$this->option('force')) {
            $this->info('Scheduled backups are disabled. Use --force to run anyway.');
            return;
        }

        // Additional logic could be added here to check if exactly the correct day/time matches
        // For now, the Kernel.php schedule handles the timing.

        $this->info('Starting database backup process...');
        
        $filename = 'scheduled-backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $db   = config('database.connections.mysql.database');

        try {
            $dump = new Mysqldump("mysql:host=$host;dbname=$db", $user, $pass, [
                'add-drop-table' => true,
                'single-transaction' => true,
                'lock-tables' => false,
                'add-locks' => true,
                'extended-insert' => true,
                'disable-keys' => true,
                'default-character-set' => 'utf8mb4',
            ]);
            
            $dump->start($path);

            // Prepend foreign key checks
            $sql = file_get_contents($path);
            $newSql = "SET FOREIGN_KEY_CHECKS=0;\n" . $sql . "\nSET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($path, $newSql);

            $this->info("Backup generated locally at: {$path}");
            AuditLogger::log('System Backup', "Scheduled backup generated: {$filename}");

            // Cloud Upload if configured
            $googleDriveEnabled = $settings['google_drive_enabled'] ?? false;
            
            if ($googleDriveEnabled && env('GOOGLE_DRIVE_REFRESH_TOKEN')) {
                $this->info('Attempting upload to Google Drive...');
                try {
                    $fileContent = file_get_contents($path);
                    Storage::disk('google')->put($filename, $fileContent);
                    $this->info('Successfully uploaded to Google Drive!');
                    AuditLogger::log('Cloud Backup', "Uploaded {$filename} to Google Drive");
                } catch (\Exception $e) {
                    $this->error('Failed to upload to Google Drive: ' . $e->getMessage());
                    Log::error("Google Drive Backup Error: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            $this->error("Database backup failed: " . $e->getMessage());
            Log::error("Scheduled database backup failed: " . $e->getMessage());
        }
    }
}
