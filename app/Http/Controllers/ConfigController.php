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

        $config = Config::first() ?? new Config();
        $oldDatabase = $config->database;

        $config->fill($request->only(['base_url', 'database', 'period_indicator']));
        $config->scheduler_interval = $request->filled('scheduler_interval') ? (int) $request->scheduler_interval : 5;
        $config->max_retries = $request->filled('max_retries') ? (int) $request->max_retries : 3;
        $config->scheduler_active = $request->has('scheduler_active');
        $config->save();

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

        return back()->with('success', 'Configuration updated successfully.');
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
        $debugLogs = [];
        try {
            $baseUrl = trim($request->input('base_url', ''));
            $database = trim($request->input('database', ''));

            // Fallback to saved config if not supplied
            if (empty($baseUrl) || empty($database)) {
                $savedConfig = Config::first();
                $baseUrl = $baseUrl ?: $savedConfig?->base_url;
                $database = $database ?: $savedConfig?->database;
            }

            if (empty($baseUrl) || empty($database)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Please fill in both Base URL and Database Name text boxes first.',
                    'debug_logs' => []
                ], 422);
            }

            // Create temporary config directly from text box inputs
            $tempConfig = new Config([
                'base_url' => $baseUrl,
                'database' => $database,
            ]);
            $sap = new \App\Services\SapService($tempConfig);
            $user = auth()->user();

            $indicators = [];

            // 1. Try fetching via SQLQueries ('GetActivePeriod3')
            try {
                $sap->post($user, "SQLQueries", [
                    "SqlCode" => "GetActivePeriod3",
                    "SqlName" => "Get Active Period Parameter",
                    "SqlText" => "SELECT T0.[Indicator] FROM OFPR T0 WHERE T0.[F_RefDate] <= :CurrentDate AND T0.[T_RefDate] >= :CurrentDate ORDER BY T0.[AbsEntry] DESC"
                ]);
            } catch (\Exception $e) {
                // Ignore if exists (-2035)
            }

            $today = now()->format('Y-m-d');
            try {
                $data = $sap->get($user, "SQLQueries('GetActivePeriod3')/List?CurrentDate='{$today}'");
                if (isset($data['value']) && is_array($data['value'])) {
                    $indicators = $data['value'];
                }
            } catch (\Exception $e) {
                // Ignore and try fallback
            }

            // 2. Fallback: Try GET /PeriodCategory if SQLQueries didn't return any
            if (empty($indicators)) {
                try {
                    $catData = $sap->get($user, "PeriodCategory");
                    if (isset($catData['value']) && is_array($catData['value'])) {
                        foreach ($catData['value'] as $cat) {
                            $ind = $cat['PeriodCategory'] ?? $cat['Category'] ?? $cat['Indicator'] ?? null;
                            if ($ind) {
                                $indicators[] = ['Indicator' => $ind];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore fallback failure
                }
            }

            // 3. Fallback: Try GET /FinancialPeriods if still empty
            if (empty($indicators)) {
                try {
                    $fpData = $sap->get($user, "FinancialPeriods");
                    if (isset($fpData['value']) && is_array($fpData['value'])) {
                        foreach ($fpData['value'] as $fp) {
                            $ind = $fp['PeriodIndicator'] ?? $fp['Indicator'] ?? null;
                            if ($ind) {
                                $indicators[] = ['Indicator' => $ind];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore fallback failure
                }
            }

            // Collect debug logs from session (populated by SapService)
            $debugLogs = session()->get('sap_debug_logs', []);

            if (count($indicators) > 0) {
                // Deduplicate indicators by 'Indicator' key
                $uniqueIndicators = collect($indicators)->unique('Indicator')->values()->all();
                return response()->json([
                    'success' => true, 
                    'indicators' => $uniqueIndicators,
                    'debug_logs' => $debugLogs
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'No active period indicator found in SAP.',
                'debug_logs' => $debugLogs
            ]);

        } catch (\Exception $e) {
            $debugLogs = session()->get('sap_debug_logs', []);
            return response()->json([
                'success' => false, 
                'message' => 'SAP Error: ' . $e->getMessage(),
                'debug_logs' => $debugLogs
            ], 500);
        }
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
