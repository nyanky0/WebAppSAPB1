<?php

namespace App\Http\Controllers;

use App\Models\Uom;
use App\Models\UomGroup;
use App\Models\SystemLog;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UomController extends Controller
{
    public function index(Request $request)
    {
        $query = Uom::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        $uoms = $query->paginate(20)->withQueryString();
        $uomGroups = UomGroup::all();

        return view('uoms.index', compact('uoms', 'uomGroups'));
    }

    public function create()
    {
        return view('uoms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:uoms,code',
            'name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $uom = Uom::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'sync_status' => 'Draft',
            ]);

            DB::commit();

            if ($request->has('instant_sync') && $request->instant_sync) {
                try {
                    $config = Config::first();
                    $sap = new SapService($config);
                    $this->pushToSap($uom, $sap);
                    return redirect()->route('uoms.index')->with('success', 'UoM created and instantly synced to SAP!');
                } catch (\Exception $e) {
                    return redirect()->route('uoms.index')->with('warning', 'UoM created locally but instant sync failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('uoms.index')->with('success', 'UoM created successfully as Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create UoM: ' . $e->getMessage())->withInput();
        }
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncUoms();
    }

    public function pushToSap(Uom $uom, SapService $sap)
    {
        try {
            $payload = [
                'Code' => $uom->code,
                'Name' => $uom->name,
            ];

            $response = $sap->post(auth()->user(), 'UnitOfMeasurements', $payload);

            $uom->update([
                'sync_status' => 'Synced',
                'sap_status' => 'Created',
                'sync_error' => null
            ]);

            SystemLog::logAction('sap', 'Synced UoM', "UoM '{$uom->code}' successfully pushed to SAP.");
        } catch (\Exception $e) {
            $uom->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            SystemLog::logAction('sap', 'Sync UoM Failed', "UoM '{$uom->code}' failed: " . $e->getMessage());
            throw $e;
        }
    }
}
