<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TreasurerController;
use App\Http\Controllers\PriestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PriestScheduleController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout']);

Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');

Route::get('/verify-otp', [AuthController::class, 'showVerifyOtpForm'])->name('otp.verify.form');
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');

Route::get('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');

// Unified System Routes (Protected by Auth)
Route::middleware(['auth'])->group(function () {

    // Unified Dashboard
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->middleware(['module:dashboard'])->name('dashboard');
    Route::get('/', function () {
        $user = auth()->user();
        if ($user->hasModule('dashboard')) {
            return redirect()->route('dashboard');
        }

        // Logic mapping modules to routes (Duplicate of AuthController helper to ensure root always redirects correctly)
        $moduleRoutes = [
            'scheduling'       => 'schedules',
            'service_requests' => 'service-requests.index',
            'collections'      => 'collections',
            'donations'        => 'donations',
            'service_records'  => 'sacraments',
            'reports'          => 'reports',
            'system_settings'  => 'system-settings.index',
            'system_roles'     => 'roles.index',
            'user_accounts'    => 'users',
            'audit_trail'      => 'audit-trail',
        ];

        foreach ($moduleRoutes as $module => $route) {
            if ($user->hasModule($module)) {
                return redirect()->route($route);
            }
        }

        return redirect()->route('profile');
    }); // Redirect root to dashboard or first module if auth

    // --- Modules ---


    // 1.5 Service Requests
    Route::middleware(['module:service_requests'])->group(function () {
        Route::get('/service-requests/check-availability', [App\Http\Controllers\ServiceRequestController::class, 'checkAvailability'])->name('service-requests.check-availability');
        Route::resource('service-requests', App\Http\Controllers\ServiceRequestController::class);
        Route::get('/api/priest-schedule/{priest}', [App\Http\Controllers\PriestScheduleController::class, 'getPriestSchedule'])->name('api.priest-schedule.get');
    });

    Route::get('/api/services/{id}', [App\Http\Controllers\SystemSettingController::class, 'getService'])->name('api.services.get');

    // 2. System Roles
    Route::middleware(['module:system_roles'])->group(function () {
        Route::resource('roles', App\Http\Controllers\RoleController::class);
    });

    // 2.5 User Accounts
    Route::middleware(['module:user_accounts'])->group(function () {
        Route::get('/users', [App\Http\Controllers\AccessControlController::class, 'index'])->name('users');
        Route::post('/users', [App\Http\Controllers\AccessControlController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [App\Http\Controllers\AccessControlController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [App\Http\Controllers\AccessControlController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{id}/restore', [App\Http\Controllers\AccessControlController::class, 'restore'])->name('users.restore');
        Route::delete('/users/{id}/force', [App\Http\Controllers\AccessControlController::class, 'forceDelete'])->name('users.force-delete');
        Route::get('/users/status', [App\Http\Controllers\AccessControlController::class, 'getUserStatuses'])->name('users.status');
    });

    // 3. Scheduling
    Route::middleware(['module:scheduling'])->group(function () {
        Route::get('/schedules', [App\Http\Controllers\SchedulingController::class, 'index'])->name('schedules'); // List View

        // API Routes
        Route::get('/api/schedules', [App\Http\Controllers\SchedulingController::class, 'getEvents'])->name('schedules.events');
        Route::post('/api/schedules', [App\Http\Controllers\SchedulingController::class, 'store'])->name('schedules.store');
        Route::put('/api/schedules/{id}', [App\Http\Controllers\SchedulingController::class, 'update'])->name('schedules.update');
        Route::delete('/api/schedules/{id}', [App\Http\Controllers\SchedulingController::class, 'destroy'])->name('schedules.destroy');

        // Calendar view
        Route::get('/calendar', [App\Http\Controllers\SchedulingController::class, 'calendar'])->name('calendar');
    });

    // 4. Sacraments (Services Records)
    Route::middleware(['module:service_records'])->group(function () {
        Route::get('/sacraments', [App\Http\Controllers\SacramentController::class, 'index'])->name('sacraments');
        Route::post('/sacraments/{id}/complete', [App\Http\Controllers\SacramentController::class, 'markComplete'])->name('sacraments.complete');
        Route::get('/sacraments/{id}/certificate', [App\Http\Controllers\SacramentController::class, 'printCertificate'])->name('sacraments.certificate');
    });

    // 5. Finance (Collections & Donations & Fees)
    Route::middleware(['module:collections'])->group(function () {
        Route::get('/collections', [App\Http\Controllers\FinanceController::class, 'collections'])->name('collections');
        Route::post('/collections', [App\Http\Controllers\FinanceController::class, 'storeCollection'])->name('collections.store');
    });

    Route::middleware(['module:donations'])->group(function () {
        Route::get('/donations', [App\Http\Controllers\FinanceController::class, 'donations'])->name('donations');
        Route::post('/donations', [App\Http\Controllers\FinanceController::class, 'storeDonation'])->name('donations.store');
    });

    // Payment Processing (Service Fees)
    Route::middleware(['module:services_fees'])->group(function () {
        Route::post('/service-fees/{id}/process-payment', [App\Http\Controllers\FinanceController::class, 'processPayment'])->name('service-fees.process-payment');
        Route::get('/payments/{id}/receipt', [App\Http\Controllers\FinanceController::class, 'downloadReceipt'])->name('payments.receipt');
    });

    // 6. Reports
    Route::middleware(['module:reports'])->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports');
        Route::get('/reports/export', [App\Http\Controllers\ReportController::class, 'export'])->name('reports.export');
        Route::get('/reports/export-pdf', [App\Http\Controllers\ReportController::class, 'exportPdf'])->name('reports.export-pdf');
    });

    // 7. System Settings (Common)
    Route::middleware(['module:audit_trail'])->get('/audit-trail', [App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit-trail');

    Route::middleware(['module:system_settings'])->prefix('system-settings')->name('system-settings.')->group(function () {
        Route::get('/', [App\Http\Controllers\SystemSettingController::class, 'index'])->name('index');
        Route::post('/general', [App\Http\Controllers\SystemSettingController::class, 'updateGeneral'])->name('update-general');

        // Services
        Route::post('/services', [App\Http\Controllers\SystemSettingController::class, 'storeService'])->name('store-service');
        Route::put('/services/{id}', [App\Http\Controllers\SystemSettingController::class, 'updateService'])->name('update-service');
        Route::delete('/services/{id}', [App\Http\Controllers\SystemSettingController::class, 'destroyService'])->name('destroy-service');
        Route::post('/services/{id}/restore', [App\Http\Controllers\SystemSettingController::class, 'restoreService'])->name('restore-service');
        Route::delete('/services/{id}/force', [App\Http\Controllers\SystemSettingController::class, 'forceDeleteService'])->name('force-delete-service');

        // Database
        Route::get('/backup', [App\Http\Controllers\SystemSettingController::class, 'backupDatabase'])->name('backup');
        Route::post('/restore', [App\Http\Controllers\SystemSettingController::class, 'restoreDatabase'])->name('restore');
        Route::get('/backup/download/{filename}', [App\Http\Controllers\SystemSettingController::class, 'downloadBackup'])->name('backup.download');
        Route::get('/backup/delete/{filename}', [App\Http\Controllers\SystemSettingController::class, 'deleteBackup'])->name('backup.delete');
        
        // Priest Schedules (Admin only via UI, though API is open to module)
        Route::post('/priest-schedule/{priest}', [App\Http\Controllers\PriestScheduleController::class, 'updatePriestSchedule'])->name('priest-schedule.update');

        // Payment Methods
        Route::post('/payment-methods', [App\Http\Controllers\SystemSettingController::class, 'storePaymentMethod'])->name('store-payment-method');
        Route::put('/payment-methods/{id}', [App\Http\Controllers\SystemSettingController::class, 'updatePaymentMethod'])->name('update-payment-method');
        Route::delete('/payment-methods/{id}', [App\Http\Controllers\SystemSettingController::class, 'destroyPaymentMethod'])->name('destroy-payment-method');
        Route::post('/payment-methods/{id}/restore', [App\Http\Controllers\SystemSettingController::class, 'restorePaymentMethod'])->name('restore-payment-method');
        Route::delete('/payment-methods/{id}/force', [App\Http\Controllers\SystemSettingController::class, 'forceDeletePaymentMethod'])->name('force-delete-payment-method');
        Route::post('/payment-methods/{id}/toggle', [App\Http\Controllers\SystemSettingController::class, 'togglePaymentMethod'])->name('toggle-payment-method');
    });

    // My Profile (Separate)
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/photo', [App\Http\Controllers\ProfileController::class, 'updateProfile'])->name('profile.update-photo');
    Route::post('/profile/account', [App\Http\Controllers\ProfileController::class, 'updateAccount'])->name('profile.update-account');
    Route::post('/profile/photo/clear', [App\Http\Controllers\ProfileController::class, 'clearPhoto'])->name('profile.clear-photo');

    // Notifications
    Route::get('/notifications', [App\Http\Controllers\AdminController::class, 'getNotifications'])->name('notifications');
    Route::get('/notifications/list', [App\Http\Controllers\AdminController::class, 'notificationsPage'])->name('notifications.index');
    Route::post('/notifications/read', [App\Http\Controllers\AdminController::class, 'markNotificationsAsRead'])->name('notifications.read');

    // Service Manifest
    Route::middleware(['module:service_records'])->get('/service-manifest', [App\Http\Controllers\ServiceManifestController::class, 'index'])->name('service-manifest');

});

// Development / Deployment Helper: Clear all caches via URL
Route::get('/clear-cache', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        return "All caches (view, route, config, data) cleared successfully!";
    } catch (\Exception $e) {
        return "Error clearing cache: " . $e->getMessage();
    }
});

// Hostinger / Shared Hosting Helper: Link Storage via URL
Route::get('/storage-link', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return "Storage link created successfully!";
    } catch (\Exception $e) {
        return "Error linking storage: " . $e->getMessage();
    }
});