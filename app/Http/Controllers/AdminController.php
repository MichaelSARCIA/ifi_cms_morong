<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {

        // 2. Total Collections
        $total_donations_sum = \DB::table('donations')->sum('amount');
        $total_collections = "₱ " . number_format($total_donations_sum / 1000, 1) . 'k';

        // 3. Upcoming Events Count
        $total_upcoming_events = \DB::table('schedules')->where('start_datetime', '>=', now())->count();

        // 4. Pending Requests (Placeholder)
        $pending_requests = "12";

        // 5. Recent Transactions
        $recent_transactions = \DB::table('donations')->orderBy('date_received', 'desc')->limit(3)->get();

        // 6. Upcoming Schedules
        $upcoming_events = \DB::table('schedules')->where('start_datetime', '>=', now())->orderBy('start_datetime', 'asc')->limit(2)->get();

        return view('admin.dashboard', compact(
            'total_collections',
            'total_upcoming_events',
            'pending_requests',
            'recent_transactions',
            'upcoming_events'
        ));
    }

    public function users()
    {
        $users = \App\Models\User::paginate(10);
        $roles = \App\Models\Role::all();
        return view('modules.access_control.users', compact('users', 'roles'));
    }

    public function getUserStatuses()
    {
        $statuses = \App\Models\User::select('id', 'status')->get();
        return response()->json($statuses);
    }

    public function getNotifications()
    {
        // Fetch native Laravel database notifications for the logged-in user
        if (!auth()->check()) {
            return response()->json([]);
        }

        $notifications = auth()->user()->notifications()->take(10)->get()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'user_name' => 'System Update',
                'user_avatar' => asset('uploads/default_avatar.jpg'),
                'action' => $notif->data['title'] ?? 'Notification',
                'details' => $notif->data['message'] ?? '',
                'time_ago' => $notif->created_at->diffForHumans(),
                'type' => $notif->data['type'] ?? 'info',
                'icon' => $notif->data['icon'] ?? 'fa-bell',
                'color' => $notif->data['color'] ?? 'bg-blue-500',
                'link' => isset($notif->data['service_request_id']) ? route('service-requests.index', ['request_id' => $notif->data['service_request_id']]) : route('service-requests.index'),
                'is_read' => $notif->read_at !== null
            ];
        });

        return response()->json($notifications);
    }

    public function markNotificationsAsRead(Request $request)
    {
        if (auth()->check()) {
            auth()->user()->unreadNotifications->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }

    public function notificationsPage(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $perPage = $request->input('per_page', 15);
        $notifications = auth()->user()->notifications()->paginate($perPage);
        
        // Map data to match the format used in the header dropdown if needed, 
        // or just pass the raw objects and handle mapping in the view.
        // For consistency with the existing JSON API, let's map it.
        $notifications->getCollection()->transform(function ($notif) {
            return (object) [
                'id' => $notif->id,
                'action' => $notif->data['title'] ?? 'Notification',
                'details' => $notif->data['message'] ?? '',
                'time_ago' => $notif->created_at->diffForHumans(),
                'icon' => $notif->data['icon'] ?? 'fa-bell',
                'color' => $notif->data['color'] ?? 'bg-blue-500',
                'link' => isset($notif->data['service_request_id']) ? route('service-requests.index', ['request_id' => $notif->data['service_request_id']]) : route('service-requests.index'),
                'is_read' => $notif->read_at !== null,
                'created_at' => $notif->created_at
            ];
        });

        return view('notifications.index', compact('notifications'));
    }

    private function getLinkForAction($action)
    {
        $action = strtolower($action);
        if (str_contains($action, 'schedule') || str_contains($action, 'event'))
            return route('schedules');
        if (str_contains($action, 'sacrament'))
            return route('sacraments');
        if (str_contains($action, 'report'))
            return route('reports');
        if (str_contains($action, 'setting'))
            return route('system-settings.index');
        if (str_contains($action, 'profile') || str_contains($action, 'account'))
            return route('profile');
        return '#';
    }

    private function getNotificationType($action)
    {
        if (stripos($action, 'Add') !== false)
            return 'create';
        if (stripos($action, 'Update') !== false || stripos($action, 'Edit') !== false)
            return 'update';
        if (stripos($action, 'Delete') !== false || stripos($action, 'Archive') !== false)
            return 'delete';
        if (stripos($action, 'Restore') !== false)
            return 'restore';
        if (stripos($action, 'Login') !== false)
            return 'login';
        return 'info';
    }

    // Migrated to other controllers
}
