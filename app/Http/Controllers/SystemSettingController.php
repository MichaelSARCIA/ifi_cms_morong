<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\PaymentMethod;
use App\Models\ServiceType;
use App\Models\User;
use App\Helpers\AuditLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class SystemSettingController extends Controller
{
    public function index()
    {
        // Fetch all settings as key-value pairs
        $settings = SystemSetting::pluck('value', 'key')->toArray();
        $services = ServiceType::all();
        $paymentMethods = PaymentMethod::orderBy('sort_order')->orderBy('id')->get();
        $active_priests = User::where('role', 'Priest')->get();

        // Retrieve Backups
        $backupPath = storage_path('app/backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $allFiles = File::files($backupPath);
        $backups = collect($allFiles)->map(function ($file) {
            $bytes = $file->getSize();
            // Convert to MB
            $size = number_format($bytes / 1048576, 2) . ' MB';

            return [
                'name' => $file->getFilename(),
                'size' => $size,
                'path' => $file->getPathname(),
                'last_modified' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
            ];
        })->sortByDesc('last_modified')->values()->toArray();

        $latest_backup = !empty($backups) ? $backups[0] : null;

        return view('modules.system_settings.index', compact('settings', 'services', 'latest_backup', 'backups', 'active_priests', 'paymentMethods'));
    }

    public function updateGeneral(Request $request)
    {
        $data = $request->except(['_token', 'logo', 'login_background']);

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'church_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            SystemSetting::updateOrCreate(
                ['key' => 'church_logo'],
                ['value' => $filename]
            );
        }



        if ($request->hasFile('login_background')) {
            $file = $request->file('login_background');
            $filename = 'login_background_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads'), $filename);

            SystemSetting::updateOrCreate(
                ['key' => 'login_background'],
                ['value' => $filename]
            );
        }

        AuditLogger::log('Update Settings', 'Updated General Configuration');
        return redirect()->back()->with('success', 'General settings updated successfully.');
    }

    // --- Service Management ---

    public function storeService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
            'custom_fields' => 'nullable|json',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'string',
        ]);

        // Requirements are now passed as an array from the frontend
        $requirements = $request->requirements ?? [];
        $custom_fields = $request->custom_fields ? json_decode($request->custom_fields, true) : [];
        // Cast payment method IDs to integers for consistent storage
        $payment_methods = array_map('intval', $request->payment_methods ?? []);

        ServiceType::create([
            'name' => $validated['name'],
            'fee' => $validated['fee'],
            'requirements' => $requirements,
            'custom_fields' => $custom_fields,
            'icon' => $validated['icon'] ?? 'fa-church',
            'color' => $validated['color'] ?? 'blue',
            'payment_methods' => $payment_methods,
        ]);

        AuditLogger::log('Create Service', "Added service type: {$validated['name']}");
        Cache::forget('service_types_sidebar');
        return redirect()->back()->with('success', 'Service type added successfully.');
    }

    public function updateService(Request $request, $id)
    {
        $service = ServiceType::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string',
            'custom_fields' => 'nullable|json',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'payment_methods' => 'nullable|array',
            'payment_methods.*' => 'string',
        ]);

        $requirements = $request->requirements ?? [];
        $custom_fields = $request->custom_fields ? json_decode($request->custom_fields, true) : [];
        // Cast payment method IDs to integers for consistent storage
        $payment_methods = array_map('intval', $request->payment_methods ?? []);

        $service->update([
            'name' => $validated['name'],
            'fee' => $validated['fee'],
            'requirements' => $requirements,
            'custom_fields' => $custom_fields,
            'icon' => $validated['icon'] ?? 'fa-church',
            'color' => $validated['color'] ?? 'blue',
            'payment_methods' => $payment_methods,
        ]);

        AuditLogger::log('Update Service', "Updated service type: {$service->name}");
        Cache::forget('service_types_sidebar');
        return redirect()->back()->with('success', 'Service type updated successfully.');
    }

    public function destroyService($id)
    {
        $service = ServiceType::findOrFail($id);
        $name = $service->name;
        $service->delete();

        AuditLogger::log('Delete Service', "Deleted service type: {$name}");
        Cache::forget('service_types_sidebar');
        return redirect()->back()->with('success', 'Service type deleted successfully.');
    }

    // --- Database Management ---

    public function backupDatabase()
    {
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';

        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $path = storage_path('app/backups/' . $filename);
        
        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        $db = env('DB_DATABASE', 'ifi_cms_morong');

        $passStr = empty($pass) ? "" : "--password=\"{$pass}\"";

        // Try standard command
        $command = "mysqldump --user={$user} {$passStr} --host={$host} {$db} > " . escapeshellarg($path) . " 2>&1";
        exec($command, $output, $result);

        if ($result !== 0) {
            // Fallback for XAMPP Windows
            $xamppPath = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
            if (file_exists($xamppPath)) {
                $command = "\"$xamppPath\" --user={$user} {$passStr} --host={$host} {$db} > " . escapeshellarg($path) . " 2>&1";
                exec($command, $output, $result);
            }
        }

        if ($result === 0) {
            // Prepend foreign key checks off to the file
            $sql = file_get_contents($path);
            $newSql = "SET FOREIGN_KEY_CHECKS=0;\n" . $sql . "\nSET FOREIGN_KEY_CHECKS=1;\n";
            file_put_contents($path, $newSql);

            AuditLogger::log('Database Backup', 'Created database backup details');
            return redirect()->back()->with('success', 'Backup generated successfully.');
        }

        \Log::error("Database backup failed: " . implode("\n", $output));
        return redirect()->back()->with('error', 'Backup failed. Check server configuration.');
    }

    public function restoreDatabase(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|mimes:sql,txt',
        ]);

        $file = $request->file('backup_file');
        $path = $file->getRealPath();

        $user = env('DB_USERNAME', 'root');
        $pass = env('DB_PASSWORD', '');
        $host = env('DB_HOST', '127.0.0.1');
        $db = env('DB_DATABASE', 'ifi_cms_morong');

        $passStr = empty($pass) ? "" : "--password=\"{$pass}\"";

        $command = "mysql --user={$user} {$passStr} --host={$host} {$db} < " . escapeshellarg($path) . " 2>&1";
        exec($command, $output, $result);

        if ($result !== 0) {
            // Fallback for XAMPP
            $xamppPath = "C:\\xampp\\mysql\\bin\\mysql.exe";
            if (file_exists($xamppPath)) {
                $command = "\"$xamppPath\" --user={$user} {$passStr} --host={$host} {$db} < " . escapeshellarg($path) . " 2>&1";
                exec($command, $output, $result);
            }
        }

        if ($result === 0) {
            AuditLogger::log('Database Restore', 'Restored database from backup');
            return redirect()->back()->with('success', 'Database restored successfully.');
        }
        
        \Log::error("Database restore failed: " . implode("\n", $output));
        return redirect()->back()->with('error', 'Restore failed. Check server configuration.');
    }

    public function downloadBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (!File::exists($path)) {
            return redirect()->back()->with('error', 'Backup file not found.');
        }

        AuditLogger::log('Database Download', 'Downloaded backup file: ' . $filename);
        return response()->download($path);
    }

    public function deleteBackup($filename)
    {
        $path = storage_path('app/backups/' . $filename);

        if (File::exists($path)) {
            File::delete($path);
            AuditLogger::log('Database Delete', 'Deleted backup file: ' . $filename);
            return redirect()->back()->with('success', 'Backup file deleted successfully.');
        }

        return redirect()->back()->with('error', 'Backup file not found.');
    }

    // --- Payment Methods ---

    public function storePaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'icon'       => 'nullable|string|max:100',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $maxOrder = PaymentMethod::max('sort_order') ?? 0;

        PaymentMethod::create([
            'name'       => $validated['name'],
            'icon'       => $validated['icon'] ?? 'fa-money-bill',
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? ($maxOrder + 1),
        ]);

        AuditLogger::log('Create Payment Method', "Added payment method: {$validated['name']}");
        return redirect()->back()->with('success', 'Payment method added successfully.');
    }

    public function updatePaymentMethod(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'icon'       => 'nullable|string|max:100',
            'is_active'  => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $method->update([
            'name'       => $validated['name'],
            'icon'       => $validated['icon'] ?? 'fa-money-bill',
            'is_active'  => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? $method->sort_order,
        ]);

        AuditLogger::log('Update Payment Method', "Updated payment method: {$method->name}");
        return redirect()->back()->with('success', 'Payment method updated successfully.');
    }

    public function destroyPaymentMethod($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $name = $method->name;
        $method->delete();

        AuditLogger::log('Delete Payment Method', "Deleted payment method: {$name}");
        return redirect()->back()->with('success', 'Payment method deleted successfully.');
    }

    public function togglePaymentMethod($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->update(['is_active' => !$method->is_active]);

        $status = $method->is_active ? 'activated' : 'deactivated';
        AuditLogger::log('Toggle Payment Method', "Payment method '{$method->name}' {$status}");

        if (request()->expectsJson() || request()->header('Content-Type') === 'application/json') {
            return response()->json(['success' => true, 'is_active' => $method->is_active]);
        }

        return redirect()->back()->with('success', "Payment method {$status} successfully.");
    }
}
