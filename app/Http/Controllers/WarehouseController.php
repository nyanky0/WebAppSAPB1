<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\BinLocation;
use App\Models\SystemLog;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::with('bins');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('whs_code', 'ilike', "%{$search}%")
                  ->orWhere('whs_name', 'ilike', "%{$search}%")
                  ->orWhere('location', 'ilike', "%{$search}%");
            });
        }

        $warehouses = $query->paginate(20)->withQueryString();

        return view('warehouses.index', compact('warehouses'));
    }

    public function create()
    {
        return view('warehouses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'whs_code' => 'required|string|max:50|unique:warehouses,whs_code',
            'whs_name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'bin_enabled' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $whs = Warehouse::create([
                'whs_code' => $validated['whs_code'],
                'whs_name' => $validated['whs_name'],
                'location' => $validated['location'] ?? null,
                'bin_enabled' => $request->has('bin_enabled'),
                'is_active' => true,
                'sync_status' => 'Draft',
            ]);

            DB::commit();

            if ($request->has('instant_sync') && $request->instant_sync) {
                try {
                    $config = Config::first();
                    $sap = new SapService($config);
                    $this->pushToSap($whs, $sap);
                    return redirect()->route('warehouses.index')->with('success', 'Warehouse created and instantly synced to SAP!');
                } catch (\Exception $e) {
                    return redirect()->route('warehouses.index')->with('warning', 'Warehouse created locally but instant sync failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully as Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create warehouse: ' . $e->getMessage())->withInput();
        }
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncWarehouses();
    }

    public function pushToSap(Warehouse $whs, SapService $sap)
    {
        try {
            $payload = [
                'WarehouseCode' => $whs->whs_code,
                'WarehouseName' => $whs->whs_name,
                'Inactive' => $whs->is_active ? 'tNO' : 'tYES',
                'EnableBinLocations' => $whs->bin_enabled ? 'tYES' : 'tNO',
            ];

            $response = $sap->post(auth()->user(), 'Warehouses', $payload);

            $whs->update([
                'sync_status' => 'Synced',
                'sap_status' => 'Created',
                'sync_error' => null
            ]);

            SystemLog::logAction('sap', 'Synced Warehouse', "Warehouse '{$whs->whs_code}' successfully pushed to SAP.");
        } catch (\Exception $e) {
            $whs->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            SystemLog::logAction('sap', 'Sync Warehouse Failed', "Warehouse '{$whs->whs_code}' failed: " . $e->getMessage());
            throw $e;
        }
    }
}
