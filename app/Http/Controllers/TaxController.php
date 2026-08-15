<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Config;
use App\Services\SapService;

class TaxController extends Controller
{
    public function index(Request $request)
    {
        $query = Tax::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%");
            });
        }

        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->where('locked', false);
        } elseif ($status === 'locked') {
            $query->where('locked', true);
        }

        $sortField = $request->get('sort', 'code');
        $sortDirection = $request->get('direction', 'asc');
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $taxes = $query->paginate($perPage)->withQueryString();

        return view('taxes.index', compact('taxes', 'sortField', 'sortDirection', 'perPage'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncTaxes();
    }

    public function create()
    {
        return view('taxes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:taxes,code',
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $tax = Tax::create([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'rate' => $validated['rate'],
                'locked' => $request->has('locked'),
                'sync_status' => 'Draft',
            ]);

            DB::commit();

            if ($request->has('instant_sync') && $request->instant_sync) {
                try {
                    $config = Config::first();
                    $sap = new SapService($config);
                    $this->pushToSap($tax, $sap);
                    return redirect()->route('taxes.index')->with('success', 'Tax created and instantly synced to SAP!');
                } catch (\Exception $e) {
                    return redirect()->route('taxes.index')->with('warning', 'Tax created locally but instant sync failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('taxes.index')->with('success', 'Tax created successfully and saved as Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create tax: ' . $e->getMessage())->withInput();
        }
    }

    public function pushToSap(Tax $tax, SapService $sap)
    {
        try {
            $payload = [
                'Code' => $tax->code,
                'Name' => $tax->name,
                'Inactive' => $tax->locked ? 'tYES' : 'tNO',
                'VatGroups_Lines' => [
                    [
                        'Rate' => $tax->rate
                    ]
                ]
            ];

            $response = $sap->post(auth()->user(), 'VatGroups', $payload);
            
            $tax->update([
                'sync_status' => 'Synced',
                'sap_status' => 'Created',
                'sync_error' => null
            ]);
            
            SystemLog::logAction('sap', 'Synced Tax', "Tax '{$tax->code}' successfully pushed to SAP.");
        } catch (\Exception $e) {
            $tax->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            SystemLog::logAction('sap', 'Sync Tax Failed', "Tax '{$tax->code}' failed: " . $e->getMessage());
            throw $e;
        }
    }
}
