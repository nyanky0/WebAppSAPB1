<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\SystemLog;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('name')->get();
        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        $validated['permissions'] = $validated['permissions'] ?? [];

        Role::create($validated);

        SystemLog::logAction('admin', 'Created Role', "Role {$validated['name']} created.");

        return back()->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'string'
        ]);

        $validated['permissions'] = $validated['permissions'] ?? [];

        $oldName = $role->name;
        $role->update($validated);

        SystemLog::logAction('admin', 'Updated Role', "Role {$oldName} renamed to {$role->name}.");

        return back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        SystemLog::logAction('admin', 'Deleted Role', "Role {$role->name} deleted.");

        $role->delete();
        return back()->with('success', 'Role deleted successfully.');
    }
}
