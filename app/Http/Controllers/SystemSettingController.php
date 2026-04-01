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
        $archivedServices = ServiceType::onlyTrashed()->get();
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
        $archivedPaymentMethods = PaymentMethod::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
 
        return view('modules.system_settings.index', compact('settings', 'services', 'archivedServices', 'latest_backup', 'backups', 'active_priests', 'paymentMethods', 'archivedPaymentMethods'));
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

        $requirements = $request->requirements ?? [];
        $custom_fields = $request->custom_fields ? json_decode($request->custom_fields, true) : [];
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

        AuditLogger::log('Archive Service', "Archived service type: {$name}");
        Cache::forget('service_types_sidebar');
        return redirect()->back()->with('success', 'Service type archived successfully.');
    }

    public function restoreService($id)
    {
        $service = ServiceType::onlyTrashed()->findOrFail($id);
        $name = $service->name;
        $service->restore();

        AuditLogger::log('Restore Service', "Restored service type: {$name}");
        Cache::forget('service_types_sidebar');
        return redirect()->back()->with('success', 'Service type restored successfully.');
    }

    public function forceDeleteService($id)
    {
        $service = ServiceType::onlyTrashed()->findOrFail($id);
        $name = $service->name;
        $service->forceDelete();

        AuditLogger::log('Permanent Delete Service', "Permanently deleted service type: {$name}");
        Cache::forget('service_types_sidebar');
        return redirect()->back()->with('success', 'Service type permanently deleted.');
    }

    public function getService($id)
    {
        $service = ServiceType::find($id);
        if (!$service) {
            return response()->json(['error' => 'Service not found'], 404);
        }
        return response()->json($service);
    }

    // --- Database Management ---

    public function backupDatabase()
    {
        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';

        if (!file_exists(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $path = storage_path('app/backups/' . $filename);
        
        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $db   = config('database.connections.mysql.database');

        $passStr = empty($pass) ? "" : "--password=\"" . addslashes($pass) . "\"";

        // Try standard path first
        $command = "mysqldump --user=" . escapeshellarg($user) . " {$passStr} --host=" . escapeshellarg($host) . " " . escapeshellarg($db) . " > " . escapeshellarg($path) . " 2>&1";
        exec($command, $output, $result);

        if ($result !== 0) {
            $xamppPath = "C:\\xampp\\mysql\\bin\\mysqldump.exe";
            if (file_exists($xamppPath)) {
                $command = "\"$xamppPath\" --user=" . escapeshellarg($user) . " {$passStr} --host=" . escapeshellarg($host) . " " . escapeshellarg($db) . " > " . escapeshellarg($path) . " 2>&1";
                exec($command, $output, $result);
            }
        }

        if ($result === 0) {
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
        
        // Wrap the SQL with foreign key checks disable/enable to ensure safe restore
        $sqlContent = file_get_contents($file->getRealPath());
        $tempPath = storage_path('app/temp_restore_' . time() . '.sql');
        $wrappedSql = "SET FOREIGN_KEY_CHECKS=0;\n" . $sqlContent . "\nSET FOREIGN_KEY_CHECKS=1;";
        file_put_contents($tempPath, $wrappedSql);

        $user = config('database.connections.mysql.username');
        $pass = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');
        $db   = config('database.connections.mysql.database');

        $passStr = empty($pass) ? "" : "--password=\"" . addslashes($pass) . "\"";

        // Try standard path first
        $command = "mysql --user=" . escapeshellarg($user) . " {$passStr} --host=" . escapeshellarg($host) . " " . escapeshellarg($db) . " < " . escapeshellarg($tempPath) . " 2>&1";
        exec($command, $output, $result);

        if ($result !== 0) {
            $xamppPath = "C:\\xampp\\mysql\\bin\\mysql.exe";
            if (file_exists($xamppPath)) {
                $command = "\"$xamppPath\" --user=" . escapeshellarg($user) . " {$passStr} --host=" . escapeshellarg($host) . " " . escapeshellarg($db) . " < " . escapeshellarg($tempPath) . " 2>&1";
                exec($command, $output, $result);
            }
        }

        // Clean up temp file
        if (file_exists($tempPath)) unlink($tempPath);

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
        AuditLogger::log('Archive Payment Method', "Archived payment method: {$name}");
        return redirect()->back()->with('success', 'Payment method archived successfully.');
    }
 
    public function restorePaymentMethod($id)
    {
        $method = PaymentMethod::onlyTrashed()->findOrFail($id);
        $name = $method->name;
        $method->restore();
        AuditLogger::log('Restore Payment Method', "Restored payment method: {$name}");
        return redirect()->back()->with('success', 'Payment method restored successfully.');
    }

    public function forceDeletePaymentMethod($id)
    {
        $method = PaymentMethod::onlyTrashed()->findOrFail($id);
        $name = $method->name;
        $method->forceDelete();
        AuditLogger::log('Permanent Delete Payment Method', "Permanently deleted payment method: {$name}");
        return redirect()->back()->with('success', 'Payment method permanently deleted.');
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
