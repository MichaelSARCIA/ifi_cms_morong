<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    private $modules = [
        'dashboard' => 'Dashboard',
        'service_requests' => 'Service Request',
        'service_requests_form' => '— Application Form',
        'service_requests_records' => '— Applications List',
        'scheduling' => 'Schedule',
        'service_records' => 'Services Records',
        'collections' => 'Collections',
        'donations' => 'Donations',
        'services_fees' => 'Services Fees',
        'reports' => 'Reports',
        'system_settings' => 'System Settings',
        'system_settings_general' => '— General Settings',
        'system_settings_priests' => '— Priest Schedules',
        'system_settings_services' => '— Services & Requirements',
        'system_settings_payment_methods' => '— Payment Methods',
        'system_settings_database' => '— Backup & Database',
        'system_roles' => 'System Roles',
        'user_accounts' => 'User Accounts',
        'audit_trail' => 'Activity Logs'
    ];

    public function index()
    {
        $roles = Role::all();
        $modules = $this->modules; // Access class property
        return view('modules.access_control.roles', compact('roles', 'modules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'modules' => 'array'
        ]);

        Role::create([
            'name' => $request->name,
            'modules' => $request->modules ?? []
        ]);

        \App\Helpers\AuditLogger::log('Create Role', 'Created role: ' . $request->name);

        return redirect()->back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name,' . $role->id,
            'modules' => 'array'
        ]);

        $role->update([
            'name' => $request->name,
            'modules' => $request->modules ?? []
        ]);

        \App\Helpers\AuditLogger::log('Update Role', 'Updated role: ' . $role->name);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if (in_array($role->name, ['Admin'])) {
            return redirect()->back()->with('error', 'Cannot delete core system roles.');
        }

        $role->delete();
        \App\Helpers\AuditLogger::log('Delete Role', 'Deleted role: ' . $role->name);

        return redirect()->back()->with('success', 'Role deleted successfully.');
    }
}
