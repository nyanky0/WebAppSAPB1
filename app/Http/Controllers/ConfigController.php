<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;
use App\Models\SystemLog;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first() ?? new Config();
        return view('config.index', compact('config'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'base_url' => 'required|url',
            'database' => 'required|string',
            'period_indicator' => 'nullable|string',
            'scheduler_active' => 'nullable|boolean',
            'scheduler_interval' => 'nullable|integer|min:1',
            'max_retries' => 'nullable|integer|min:1|max:10',
        ]);

        $user = auth()->user();
        $sapUser = $user?->sap_user ?? $user?->username ?? 'manager';
        $sapPassword = $user?->sap_password ?? 'P@ssw0rd';

        // Sanitized JSON payload (company + user tanpa password)
        $sanitizedPayload = [
            'CompanyDB' => $request->database,
            'UserName' => $sapUser,
        ];
        $jsonPreview = json_encode($sanitizedPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Perform SAP Service Layer connection & login test
        $baseUrl = rtrim($request->base_url, '/');
        $parsed = parse_url($baseUrl);
        $hostHeader = 'localhost' . (isset($parsed['port']) ? ':' . $parsed['port'] : '');
        $maxRetries = (int) ($request->max_retries ?? 3);

        try {
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(12)
                ->withHeaders(['Host' => $hostHeader])
                ->post("{$baseUrl}/Login", [
                    'UserName' => $sapUser,
                    'Password' => $sapPassword,
                    'CompanyDB' => $request->database,
                ]);

            if (!$response->successful()) {
                $errorDetail = $response->json('error.message.value') ?? $response->body();
                $errorMsg = "<strong>❌ Test Connection Failed! (HTTP {$response->status()})</strong><br>"
                          . "<span class='text-xs text-red-600 font-semibold'>" . e($errorDetail) . "</span><br><br>"
                          . "<strong>Login Payload Sent:</strong>"
                          . "<pre class='bg-gray-900 text-amber-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>"
                          . "<span class='text-xs text-gray-500 mt-1 block'>Configuration was not saved. Please verify your SAP Service Layer URL, Database Name, and User credentials.</span>";

                return back()->withInput()->with('error', $errorMsg);
            }
        } catch (\Exception $e) {
            $errorMsg = "<strong>❌ Test Connection Failed!</strong><br>"
                      . "<span class='text-xs text-red-600 font-semibold'>" . e($e->getMessage()) . "</span><br><br>"
                      . "<strong>Login Payload Sent:</strong>"
                      . "<pre class='bg-gray-900 text-amber-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>"
                      . "<span class='text-xs text-gray-500 mt-1 block'>Configuration was not saved due to connection error.</span>";

            return back()->withInput()->with('error', $errorMsg);
        }

        // Test Succeeded -> Proceed to Save
        $config = Config::first() ?? new Config();
        $oldDatabase = $config->database;

        $config->fill($request->only(['base_url', 'database', 'period_indicator']));
        $config->scheduler_interval = $request->filled('scheduler_interval') ? (int) $request->scheduler_interval : 5;
        $config->max_retries = $request->filled('max_retries') ? (int) $request->max_retries : 3;
        $config->scheduler_active = $request->has('scheduler_active');
        $config->save();

        // Clear existing session cache for SAP session
        if ($user) {
            \Illuminate\Support\Facades\Cache::forget('sap_session_' . ($user->uid7 ?? $user->id));
        }

        if ($oldDatabase && $oldDatabase !== $request->database) {
            \App\Models\Item::where('sync_status', '!=', 'Draft')->delete();
            \App\Models\ItemGroup::where('sync_status', '!=', 'Draft')->delete();
            \App\Models\Tax::where('sync_status', '!=', 'Draft')->delete();
            \App\Models\BusinessPartner::where('sync_status', '!=', 'Draft')->delete();
            SystemLog::logAction('system', 'Database Changed', "SAP Company database changed to {$request->database}. All synced Master Data was purged.");
        }

        SystemLog::logAction('admin', 'Update Configuration', [
            'base_url' => $request->base_url,
            'database' => $request->database,
            'period_indicator' => $request->period_indicator
        ]);

        $successMsg = "<strong>✅ Test Connection Successful & Configuration Saved!</strong><br>"
                    . "<strong>Login Payload Verified:</strong>"
                    . "<pre class='bg-gray-900 text-green-400 p-3 rounded-lg text-xs mt-1 text-left overflow-x-auto font-mono'>{$jsonPreview}</pre>";

        return back()->with('success', $successMsg);
    }

    public function updatePersonal(Request $request)
    {
        $user = auth()->user();
        
        $user->debug_mode = $request->has('debug_mode');
        $user->save();
        
        return back()->with('success', 'Personal settings updated successfully.');
    }

    public function fetchPeriodIndicator(\Illuminate\Http\Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncPeriodIndicators();
    }

    public function fetchDatabases(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'base_url' => 'required|url'
            ]);

            $tempConfig = new Config([
                'base_url' => $request->base_url,
                'database' => '' // Database is not needed to fetch companies
            ]);
            $sap = new \App\Services\SapService($tempConfig);
            
            $data = $sap->getDatabases();
            
            $databases = [];
            if (is_array($data)) {
                if (isset($data['value']) && is_array($data['value'])) {
                    $databases = $data['value'];
                } else {
                    $databases = $data;
                }
            }
            
            // Normalize database items into [{CompanyDB: '...', CompanyName: '...'}]
            $normalized = collect($databases)->map(function($item) {
                if (is_string($item)) {
                    return ['CompanyDB' => $item, 'CompanyName' => $item];
                }
                if (is_array($item)) {
                    $db = $item['CompanyDB'] ?? $item['dbName'] ?? $item['Code'] ?? $item['name'] ?? $item['db'] ?? null;
                    if (!$db && count($item) > 0) {
                        $db = reset($item);
                    }
                    $name = $item['CompanyName'] ?? $item['companyName'] ?? $item['Name'] ?? $db;
                    return ['CompanyDB' => $db, 'CompanyName' => $name];
                }
                return null;
            })->filter(fn($i) => !empty($i['CompanyDB']))->values()->all();
            
            if (count($normalized) > 0) {
                return response()->json(['success' => true, 'databases' => $normalized]);
            }
            
            return response()->json(['success' => false, 'message' => 'No databases found in SAP response.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
