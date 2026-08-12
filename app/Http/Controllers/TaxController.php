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

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('locked', false);
            } elseif ($request->status === 'locked') {
                $query->where('locked', true);
            }
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
        $config = Config::first();
        if (!$config || !$config->base_url || !$config->database) {
            return back()->with('error', 'Configuration is missing. Please set up Base URL and Database in the Config page.');
        }

        try {
            $sap = new SapService($config);
            $user = auth()->user();

            $taxesSynced = 0;
            $nextLink = '/VatGroups';

            while ($nextLink) {
                $path = $nextLink;
                if (strpos($nextLink, 'http') === 0) {
                    $parsedUrl = parse_url($nextLink);
                    $path = $parsedUrl['path'] . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
                    $path = preg_replace('/^\/b1s\/v[12]\//', '/', $path);
                }
                
                $path = ltrim($path, '/');

                try {
                    $response = $sap->get($user, $path);

                    if (isset($response['value']) && is_array($response['value'])) {
                        DB::beginTransaction();
                        foreach ($response['value'] as $taxData) {
                            if (!isset($taxData['Code'])) {
                                continue;
                            }

                            $rate = 0;
                            if (isset($taxData['VatGroups_Lines']) && is_array($taxData['VatGroups_Lines']) && count($taxData['VatGroups_Lines']) > 0) {
                                $rate = $taxData['VatGroups_Lines'][0]['Rate'] ?? 0;
                            }

                            $localTax = Tax::where('code', $taxData['Code'])->first();
                            
                            if ($localTax) {
                                $localTax->update([
                                    'name' => $taxData['Name'] ?? null,
                                    'rate' => $rate,
                                    'locked' => ($taxData['Inactive'] ?? 'tNO') === 'tYES',
                                    'sync_status' => 'Synced',
                                    'sap_status' => 'Created'
                                ]);
                            } else {
                                Tax::create([
                                    'code' => $taxData['Code'],
                                    'name' => $taxData['Name'] ?? null,
                                    'rate' => $rate,
                                    'locked' => ($taxData['Inactive'] ?? 'tNO') === 'tYES',
                                    'sync_status' => 'Synced',
                                    'sap_status' => 'Created'
                                ]);
                            }
                            
                            $taxesSynced++;
                        }
                        DB::commit();
                    }

                    if (isset($response['odata.nextLink'])) {
                        $nextLink = $response['odata.nextLink'];
                    } else if (isset($response['@odata.nextLink'])) {
                        $nextLink = $response['@odata.nextLink'];
                    } else {
                        $nextLink = null;
                    }
                } catch (\Exception $pageException) {
                    if (DB::transactionLevel() > 0) {
                        DB::rollBack();
                    }
                    Log::warning("Tax sync page error at '{$path}': " . $pageException->getMessage());
                    break;
                }
            }

            SystemLog::logAction('sap', 'Synced Taxes', "Successfully synced {$taxesSynced} taxes from SAP.");

            return redirect()->route('taxes.index')->with('success', "Successfully synced {$taxesSynced} Taxes from SAP Business One.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Taxes Sync Error: " . $e->getMessage());
            SystemLog::logAction('sap', 'Sync Taxes Failed', $e->getMessage());
            return redirect()->route('taxes.index')->with('error', 'An error occurred during synchronization: ' . $e->getMessage());
        }
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
