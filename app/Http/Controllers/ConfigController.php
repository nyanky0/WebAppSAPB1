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
