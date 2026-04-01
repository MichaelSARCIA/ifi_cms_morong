<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Helpers\AuditLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\UserEmailChanged;

class AccessControlController extends Controller
{
    // --- USER ACCOUNTS MANAGEMENT ---

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'active');
        $query = User::query();

        if ($tab === 'archived') {
            $query->onlyTrashed();
        }

        // Apply Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "{$search}%") // Only match starting substring to prevent catching '.com' domains
                  ->orWhere('role', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10);
        $roles = Role::all();
        return view('modules.access_control.users', compact('users', 'roles', 'tab'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|exists:roles,name',
            'password' => 'required|string|min:8',
            'title' => 'nullable|string|max:255',
            'working_days' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'max_services_per_day' => 'nullable|integer|min:1',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['status'] = 'Inactive';

        // Auto-assign modules based on selected Role
        $selectedRole = Role::where('name', $request->role)->first();
        $validated['modules'] = $selectedRole ? $selectedRole->modules : [];

        User::create($validated);
        AuditLogger::log('Add User', 'Created new user: ' . $validated['name']);

        return redirect()->route('users')->with('success', 'User created successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $oldEmail = $user->email;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'role' => 'required|exists:roles,name',
            'password' => 'nullable|string|min:8',
            'title' => 'nullable|string|max:255',
            'working_days' => 'nullable|array',
            'working_hours' => 'nullable|array',
            'max_services_per_day' => 'nullable|integer|min:1',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Auto-assign modules based on selected Role
        $selectedRole = Role::where('name', $request->role)->first();
        $validated['modules'] = $selectedRole ? $selectedRole->modules : [];

        $user->update($validated);

        // Security Alert: Notify old email if changed
        if ($oldEmail !== $validated['email']) {
            try {
                Mail::to($oldEmail)->send(new UserEmailChanged($user, $oldEmail, $validated['email']));
            } catch (\Exception $e) {
                Log::error("Failed to send email change notification to {$oldEmail}: " . $e->getMessage());
            }
        }

        AuditLogger::log('Update User', 'Updated user: ' . $validated['name']);

        return redirect()->route('users')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // Soft delete if enabled, or strict delete

        AuditLogger::log('Archive User', 'Archived user: ' . $user->name);

        return redirect()->route('users')->with('success', 'User archived successfully.');
    }

    public function restore($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        AuditLogger::log('Restore User', 'Restored user: ' . $user->name);

        return redirect()->route('users', ['tab' => 'archived'])->with('success', 'User restored successfully.');
    }

    public function getUserStatuses()
    {
        // Get all users with their ID, Status, and Last Seen
        $users = User::select('id', 'status', 'last_seen_at')->get();

        // Map to include the accessor value explicitly
        $data = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'status' => $user->status,
                'is_online' => $user->is_online,
                'last_seen' => $user->last_seen_at ? $user->last_seen_at->diffForHumans() : 'Never'
            ];
        });

        return response()->json($data);
    }
}
