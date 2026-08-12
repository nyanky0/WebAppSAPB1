<?php

namespace App\Http\Controllers;

use App\Models\WithholdingTax;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithholdingTaxController extends Controller
{
    public function index(Request $request)
    {
        $query = WithholdingTax::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%")
                  ->orWhere('category', 'ilike', "%{$search}%")
                  ->orWhere('gl_account', 'ilike', "%{$search}%");
            });
        }

        // Default to active taxes first (inactive = false)
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->where('inactive', false);
        } elseif ($status === 'inactive') {
            $query->where('inactive', true);
        }

        $sortField = $request->get('sort', 'code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $withholdingTaxes = $query->paginate($perPage)->withQueryString();

        return view('withholding-taxes.index', compact('withholdingTaxes', 'sortField', 'sortDirection', 'perPage'));
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

            $wtSynced = 0;
            $response = null;

            try {
                $response = $sap->get($user, 'WithholdingTaxCodes');
            } catch (\Exception $ex) {
                $response = $sap->get($user, 'WTax');
            }

            if (isset($response['value']) && is_array($response['value'])) {
                DB::beginTransaction();
                foreach ($response['value'] as $wtData) {
                    $code = $wtData['WTCode'] ?? ($wtData['Code'] ?? null);
                    if (!$code) continue;

                    $name = $wtData['WTName'] ?? ($wtData['Name'] ?? ($wtData['WTDescription'] ?? $code));
                    $rate = (float) ($wtData['Rate'] ?? ($wtData['Percent'] ?? 0));
                    $category = $wtData['Category'] ?? ($wtData['Type'] ?? null);
                    $account = $wtData['Account'] ?? ($wtData['GLAccount'] ?? null);
                    $inactive = ($wtData['Inactive'] ?? ($wtData['Locked'] ?? 'tNO')) === 'tYES';

                    WithholdingTax::updateOrCreate(
                        ['code' => $code],
                        [
                            'name' => $name,
                            'rate' => $rate,
                            'category' => $category,
                            'gl_account' => $account,
                            'inactive' => $inactive,
                            'sync_status' => 'Synced',
                            'sap_status' => 'Created',
                            'sync_error' => null
                        ]
                    );
                    $wtSynced++;
                }
                DB::commit();
            }

            SystemLog::logAction('sap', 'Synced Withholding Taxes', "Successfully synced {$wtSynced} Withholding Taxes from SAP.");

            return redirect()->route('withholding-taxes.index')->with('success', "Successfully synced {$wtSynced} Withholding Taxes from SAP.");

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("Withholding Taxes Sync Error: " . $e->getMessage());
            return back()->with('error', 'Failed to sync Withholding Taxes: ' . $e->getMessage());
        }
    }
}
