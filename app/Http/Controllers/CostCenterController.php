<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Dimension;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostCenterController extends Controller
{
    public function index(Request $request)
    {
        $query = CostCenter::query()->where('center_code', 'NOT ILIKE', 'Centr_z%');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('center_code', 'ilike', "%{$search}%")
                  ->orWhere('center_name', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('dimension')) {
            $query->where('dimension_code', (int) $request->dimension);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $sortField = $request->get('sort', 'center_code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $costCenters = $query->paginate($perPage)->withQueryString();
        $dimensions = Dimension::where('is_active', true)->orderBy('dimension_code')->get();

        return view('cost-centers.index', compact('costCenters', 'dimensions', 'sortField', 'sortDirection', 'perPage'));
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

            $centersSynced = 0;
            $response = null;

            try {
                $response = $sap->get($user, 'ProfitCenters');
            } catch (\Exception $ex) {
                $response = $sap->get($user, 'CostCenters');
            }

            if (isset($response['value']) && is_array($response['value'])) {
                DB::beginTransaction();

                // Clean up any existing system dummy cost centers (Centr_z%)
                CostCenter::where('center_code', 'ILIKE', 'Centr_z%')->delete();

                foreach ($response['value'] as $centerData) {
                    $code = $centerData['CenterCode'] ?? ($centerData['PrcCode'] ?? ($centerData['Code'] ?? null));
                    if (!$code || stripos($code, 'centr_z') === 0) continue;

                    $name = $centerData['CenterName'] ?? ($centerData['PrcName'] ?? ($centerData['Name'] ?? $code));
                    $dimCode = $centerData['InWhichDimension'] ?? ($centerData['Dimension'] ?? ($centerData['DimCode'] ?? 1));
                    $isActive = ($centerData['Active'] ?? ($centerData['Inactive'] ?? 'tNO')) === 'tYES' || ($centerData['Active'] ?? 'tYES') === 'tYES';

                    CostCenter::updateOrCreate(
                        ['center_code' => $code],
                        [
                            'center_name' => $name,
                            'dimension_code' => (int) $dimCode,
                            'is_active' => $isActive,
                            'sync_status' => 'Synced',
                            'sap_status' => 'Created',
                            'sync_error' => null
                        ]
                    );
                    $centersSynced++;
                }
                DB::commit();
            }

            SystemLog::logAction('sap', 'Synced Cost Centers', "Successfully synced {$centersSynced} Cost Centers from SAP.");

            return redirect()->route('cost-centers.index')->with('success', "Successfully synced {$centersSynced} Cost Centers from SAP.");

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("Cost Centers Sync Error: " . $e->getMessage());
            return back()->with('error', 'Failed to sync Cost Centers: ' . $e->getMessage());
        }
    }
}
