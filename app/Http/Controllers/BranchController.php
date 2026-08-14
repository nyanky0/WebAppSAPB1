<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Config;
use App\Services\SapService;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $query = Branch::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'ilike', "%{$search}%")
                  ->orWhere('name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        // Default to active branches first (disabled = false)
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->where('disabled', false);
        } elseif ($status === 'disabled') {
            $query->where('disabled', true);
        }

        $sortField = $request->get('sort', 'code');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }

        $branches = $query->paginate($perPage)->withQueryString();

        return view('branches.index', compact('branches', 'sortField', 'sortDirection', 'perPage'));
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

            $branchesSynced = 0;
            $response = null;

            try {
                $response = $sap->get($user, 'Branches');
            } catch (\Exception $ex) {
                $response = $sap->get($user, 'BusinessPlaces');
            }

            if (isset($response['value']) && is_array($response['value'])) {
                DB::beginTransaction();
                foreach ($response['value'] as $bData) {
                    $code = $bData['Code'] ?? ($bData['BPLID'] ?? ($bData['CodeNumber'] ?? null));
                    if ($code === null) continue;

                    $name = $bData['Name'] ?? ($bData['BPLName'] ?? "Branch {$code}");
                    $description = $bData['Description'] ?? ($bData['BPLName'] ?? $name);
                    $disabled = ($bData['Disabled'] ?? ($bData['Active'] ?? 'tNO')) === 'tYES' || ($bData['Disabled'] ?? false) === true;

                    Branch::updateOrCreate(
                        ['code' => (string) $code],
                        [
                            'name' => $name,
                            'description' => $description,
                            'disabled' => $disabled,
                            'sync_status' => 'Synced',
                            'sap_status' => 'Created',
                            'sync_error' => null
                        ]
                    );
                    $branchesSynced++;
                }
                DB::commit();
            }

            SystemLog::logAction('sap', 'Synced Branches', "Successfully synced {$branchesSynced} Branches from SAP.");

            return redirect()->route('branches.index')->with('success', "Successfully synced {$branchesSynced} Branches from SAP.");

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error("Branches Sync Error: " . $e->getMessage());
            return back()->with('error', 'Failed to sync Branches: ' . $e->getMessage());
        }
    }
}
