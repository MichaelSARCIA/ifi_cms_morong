<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AuditLogger
{
    /**
     * Log an action to the audit_logs table.
     *
     * @param string $action The action performed (e.g., 'Login', 'Add Member')
     * @param string $details Detailed description of the action
     * @return void
     */
    public static function log($action, $details)
    {
        if (Auth::check()) {
            DB::table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'user_role' => Auth::user()->role,
                'action' => $action,
                'details' => $details,
                'ip_address' => Request::ip(),
                'created_at' => now(),
                'updated_at' => now(), // Assuming the table has updated_at, usually logs don't update but timestamps might assume it
            ]);
        }
    }
}
