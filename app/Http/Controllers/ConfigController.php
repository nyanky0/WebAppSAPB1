<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Config;

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
            'period_indicator' => 'nullable|string'
        ]);

        $config = Config::first() ?? new Config();
        $config->base_url = $request->base_url;
        $config->database = $request->database;
        $config->period_indicator = $request->period_indicator;
        $config->save();

        return back()->with('success', 'Configuration updated successfully.');
    }

    public function fetchPeriodIndicator(\Illuminate\Http\Request $request)
    {
        try {
            $request->validate([
                'base_url' => 'required|url',
                'database' => 'required|string',
            ]);

            $tempConfig = new Config([
                'base_url' => $request->base_url,
                'database' => $request->database,
            ]);
            $sap = new \App\Services\SapService($tempConfig);
            
            $user = auth()->user();
            
            // 1. Try to create the SQL query. If it exists (-2035), catch the exception and proceed.
            try {
                $sap->post($user, "SQLQueries", [
                    "SqlCode" => "GetActivePeriod3",
                    "SqlName" => "Get Active Period Parameter",
                    "SqlText" => "SELECT T0.[Indicator] FROM OFPR T0 WHERE T0.[F_RefDate] <= :CurrentDate AND T0.[T_RefDate] >= :CurrentDate ORDER BY T0.[AbsEntry] DESC"
                ]);
            } catch (\Exception $e) {
                // Ignore the error, it's likely -2035 already exists
            }

            // 2. Fetch the indicator using the query and today's date
            $today = now()->format('Y-m-d');
            $data = $sap->get($user, "SQLQueries('GetActivePeriod3')/List?CurrentDate='{$today}'");
            
            if (isset($data['value']) && count($data['value']) > 0) {
                return response()->json(['success' => true, 'indicators' => $data['value']]);
            }
            
            return response()->json(['success' => false, 'message' => 'No active period found for today.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
            
            if (isset($data['value'])) {
                return response()->json(['success' => true, 'databases' => $data['value']]);
            }
            
            return response()->json(['success' => false, 'message' => 'No databases found.']);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
