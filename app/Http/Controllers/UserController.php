<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\SystemLog;
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

        $authUser = auth()->user();
        $userPerms = $authUser?->role?->permissions ?? [];
        $hasOfflineSavePerm = in_array('Administrator.OfflineSave', $userPerms) || $authUser?->role?->name === 'Super Admin';
        $bypassTest = $request->boolean('bypass_test') && $hasOfflineSavePerm;

        $sapUser = $request->sap_user;
        $sapPassword = $request->sap_password;

        if ($sapUser && !$bypassTest) {
            $testResult = $this->testSapLogin($sapUser, $sapPassword);
            if ($testResult !== true) {
                return $testResult; // Returns back with error message and sanitized JSON payload
            }
        }

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

        SystemLog::logAction('admin', 'Created User', "User {$request->username} created.");

        $config = \App\Models\Config::first();
        $sanitizedPayload = [
            'CompanyDB' => $config?->database ?? 'N/A',
            'UserName' => $sapUser ?? $request->username,
        ];
        $jsonPreview = json_encode($sanitizedPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($bypassTest) {
            $successMsg = "<strong>⚡ User Created in Offline Mode (Bypassed SAP Connection Test)!</strong><br>"
                        . "<strong>User Payload:</strong>"
                        . "<pre class='bg-gray-900 text-purple-300 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>";
        } else {
            $successMsg = "<strong>✅ User Created & SAP Connection Test Successful!</strong><br>"
                        . "<strong>Login Payload Verified:</strong>"
                        . "<pre class='bg-gray-900 text-green-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>";
        }

        return back()->with('success', $successMsg);
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

        $authUser = auth()->user();
        $userPerms = $authUser?->role?->permissions ?? [];
        $hasOfflineSavePerm = in_array('Administrator.OfflineSave', $userPerms) || $authUser?->role?->name === 'Super Admin';
        $bypassTest = $request->boolean('bypass_test') && $hasOfflineSavePerm;

        $sapUser = $request->sap_user;
        $sapPassword = $request->filled('sap_password') ? $request->sap_password : $user->sap_password;

        if ($sapUser && !$bypassTest) {
            $testResult = $this->testSapLogin($sapUser, $sapPassword);
            if ($testResult !== true) {
                return $testResult; // Returns back with error message and sanitized JSON payload
            }
        }

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'sap_user' => $request->sap_user,
            'sap_password' => $request->sap_password ?? $user->sap_password,
            'role_id' => $request->role_id,
            'updated_by' => auth()->id(),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        
        // Clear cached SAP session for this user
        \Illuminate\Support\Facades\Cache::forget('sap_session_' . $user->uid7);

        SystemLog::logAction('admin', 'Updated User', "User {$user->username} updated.");

        $config = \App\Models\Config::first();
        $sanitizedPayload = [
            'CompanyDB' => $config?->database ?? 'N/A',
            'UserName' => $sapUser ?? $user->username,
        ];
        $jsonPreview = json_encode($sanitizedPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($bypassTest) {
            $successMsg = "<strong>⚡ User Updated in Offline Mode (Bypassed SAP Connection Test)!</strong><br>"
                        . "<strong>User Payload:</strong>"
                        . "<pre class='bg-gray-900 text-purple-300 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>";
        } else {
            $successMsg = "<strong>✅ User Updated & SAP Connection Test Successful!</strong><br>"
                        . "<strong>Login Payload Verified:</strong>"
                        . "<pre class='bg-gray-900 text-green-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>";
        }

        return back()->with('success', $successMsg);
    }

    protected function testSapLogin($sapUser, $sapPassword)
    {
        $config = \App\Models\Config::first();
        if (!$config || empty($config->base_url) || empty($config->database)) {
            $errorMsg = "<strong>❌ SAP Service Layer Configuration Missing!</strong><br>"
                      . "<span class='text-xs text-red-600 font-semibold'>Please configure Service Layer Base URL and Database in System Configuration first.</span>";
            return back()->withInput()->with('error', $errorMsg);
        }

        $sanitizedPayload = [
            'CompanyDB' => $config->database,
            'UserName' => $sapUser,
        ];
        $jsonPreview = json_encode($sanitizedPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        $baseUrl = rtrim($config->base_url, '/');
        $parsed = parse_url($baseUrl);
        $hostHeader = 'localhost' . (isset($parsed['port']) ? ':' . $parsed['port'] : '');

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(12)
                ->withHeaders(['Host' => $hostHeader])
                ->post("{$baseUrl}/Login", [
                    'UserName' => $sapUser,
                    'Password' => $sapPassword,
                    'CompanyDB' => $config->database,
                ]);

            if (!$response->successful()) {
                $errorDetail = $response->json('error.message.value') ?? $response->body();
                $errorMsg = "<strong>❌ SAP User Connection Test Failed! (HTTP {$response->status()})</strong><br>"
                          . "<span class='text-xs text-red-600 font-semibold'>" . e($errorDetail) . "</span><br><br>"
                          . "<strong>Login Payload Sent:</strong>"
                          . "<pre class='bg-gray-900 text-amber-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>"
                          . "<span class='text-xs text-gray-500 mt-1 block'>User was not saved. Please verify that SAP Username, Password, and Database ({$config->database}) permissions match.</span>";

                return back()->withInput()->with('error', $errorMsg);
            }

            return true;
        } catch (\Exception $e) {
            $errorMsg = "<strong>❌ SAP User Connection Test Failed!</strong><br>"
                      . "<span class='text-xs text-red-600 font-semibold'>" . e($e->getMessage()) . "</span><br><br>"
                      . "<strong>Login Payload Sent:</strong>"
                      . "<pre class='bg-gray-900 text-amber-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>"
                      . "<span class='text-xs text-gray-500 mt-1 block'>User was not saved due to network / connection error.</span>";

            return back()->withInput()->with('error', $errorMsg);
        }
    }

    public function destroy(User $user)
    {
        if ($user->uid7 === auth()->id()) {
            return back()->withErrors(['Cannot delete yourself.']);
        }

        SystemLog::logAction('admin', 'Deleted User', "User {$user->username} deleted.");
        $user->delete();
        return back()->with('success', 'User deleted successfully.');
    }
}
