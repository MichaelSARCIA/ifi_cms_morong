<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class AuditTrailController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $export = $request->input('export', false);

        $query = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('audit_logs.*', 'users.name as user_name', 'users.profile_pic as user_avatar');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('audit_logs.action', 'like', "%{$search}%")
                  ->orWhere('audit_logs.details', 'like', "%{$search}%")
                  ->orWhere('users.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('audit_logs.user_role', $request->role);
        }

        $query->orderBy('audit_logs.created_at', 'desc')
              ->orderBy('audit_logs.id', 'desc');

        if ($export) {
            $logs = $query->get();
            $content = "=== SYSTEM ACTIVITY LOGS ===\n";
            $content .= "Generated on: " . now()->format('Y-m-d H:i:s') . "\n\n";

            foreach ($logs as $log) {
                $user = $log->user_name ?? 'System';
                $role = $log->user_role ? "({$log->user_role})" : '';
                $date = \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s');
                $action = strtoupper($log->action);

                $content .= "[$date] $user $role - $action\n";
                $content .= "Details: {$log->details}\n";
                if ($log->ip_address) {
                    $content .= "IP: {$log->ip_address}\n";
                }
                $content .= "----------------------------------------\n";
            }

            return Response::make($content, 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="activity_logs_' . date('Ymd_His') . '.txt"',
            ]);
        }

        $audit_logs = $query->paginate($perPage)->withQueryString();

        return view('modules.audit_trail.index', compact('audit_logs', 'perPage'));
    }
}
