<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:4',
            'sap_user' => 'nullable|string|max:255',
            'sap_password' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id'
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'sap_user' => $request->sap_user,
            'sap_password' => $request->sap_password,
            'role_id' => $request->role_id,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        return back()->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->uid7, 'uid7')],
            'password' => 'nullable|string|min:4',
            'sap_user' => 'nullable|string|max:255',
            'sap_password' => 'nullable|string|max:255',
            'role_id' => 'required|exists:roles,id'
        ]);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'sap_user' => $request->sap_user,
            'sap_password' => $request->sap_password,
            'role_id' => $request->role_id,
            'updated_by' => auth()->id(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return back()->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->uid7 === auth()->id()) {
            return back()->withErrors(['Cannot delete yourself.']);
        }
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
