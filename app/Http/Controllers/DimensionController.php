<?php

namespace App\Http\Controllers;

use App\Models\Dimension;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DimensionController extends Controller
{
    public function index(Request $request)
    {
        $query = Dimension::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereRaw("CAST(dimension_code AS TEXT) ILIKE ?", ["%{$search}%"])
                  ->orWhere('dimension_name', 'ilike', "%{$search}%");
            });
        }

        $sortField = $request->get('sort', 'dimension_code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $dimensions = $query->paginate($perPage)->withQueryString();

        return view('dimensions.index', compact('dimensions', 'sortField', 'sortDirection', 'perPage'));
    }

    public function sync(Request $request)
    {
        set_time_limit(300);
        $config = Config::first();
        if (!$config || !$config->base_url || !$config->database) {
            return back()->with('error', 'Configuration is missing.');
        }

        try {
            $sap = new SapService($config);
            $user = auth()->user();

            $dimensionsSynced = 0;
            $response = $sap->get($user, 'Dimensions');

            if (isset($response['value']) && is_array($response['value'])) {
                DB::beginTransaction();
                foreach ($response['value'] as $dimData) {
                    $code = $dimData['DimensionCode'] ?? ($dimData['Code'] ?? null);
                    if (!$code) continue;

                    Dimension::updateOrCreate(
                        ['dimension_code' => $code],
                        [
                            'dimension_name' => $dimData['DimensionName'] ?? ($dimData['DimensionDescription'] ?? "Dimension {$code}"),
                            'is_active' => ($dimData['IsActive'] ?? 'tYES') === 'tYES',
                            'sync_status' => 'Synced',
                            'sap_status' => 'Created',
                            'sync_error' => null
                        ]
                    );
                    $dimensionsSynced++;
                }
                DB::commit();
            }

            SystemLog::logAction('sap', 'Synced Dimensions', "Successfully synced {$dimensionsSynced} Dimensions from SAP.");

            return redirect()->route('dimensions.index')->with('success', "Successfully synced {$dimensionsSynced} Dimensions from SAP.");

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("Dimensions Sync Error: " . $e->getMessage());
            return back()->with('error', 'Failed to sync Dimensions: ' . $e->getMessage());
        }
    }
}
