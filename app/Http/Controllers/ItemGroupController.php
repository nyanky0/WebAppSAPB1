<?php

namespace App\Http\Controllers;

use App\Models\ItemGroup;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SystemLog;

class ItemGroupController extends Controller
{
    public function index(Request $request)
    {
        // Simple search and filter implementation
        $query = ItemGroup::query();
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('group_code', 'ilike', "%{$search}%")
                  ->orWhere('group_name', 'ilike', "%{$search}%");
        }
        
        // Sorting
        $sort = $request->get('sort', 'group_code');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);
        
        // Pagination
        $itemGroups = $query->paginate(20)->withQueryString();
        
        return view('item-groups.index', compact('itemGroups', 'sort', 'direction'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncItemGroups();
    }

    public function create()
    {
        return view('item-groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'group_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $group = ItemGroup::create([
                'group_name' => $validated['group_name'],
                'sync_status' => 'Draft',
            ]);

            DB::commit();

            if ($request->has('instant_sync') && $request->instant_sync) {
                try {
                    $config = Config::first();
                    $sap = new SapService($config);
                    $this->pushToSap($group, $sap);
                    return redirect()->route('item-groups.index')->with('success', 'Item Group created and instantly synced to SAP!');
                } catch (\Exception $e) {
                    return redirect()->route('item-groups.index')->with('warning', 'Item Group created locally as Draft, but instant sync failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('item-groups.index')->with('success', 'Item Group created successfully and saved as Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create item group: ' . $e->getMessage())->withInput();
        }
    }

    public function pushSingle(ItemGroup $group)
    {
        try {
            $config = Config::first();
            $sap = new SapService($config);
            $this->pushToSap($group, $sap);
            return redirect()->route('item-groups.index')->with('success', "Item Group '{$group->group_name}' pushed successfully to SAP!");
        } catch (\Exception $e) {
            return redirect()->route('item-groups.index')->with('error', "Failed to push Item Group to SAP: " . $e->getMessage());
        }
    }

    public function pushToSap(ItemGroup $group, SapService $sap)
    {
        try {
            $payload = [
                'GroupName' => $group->group_name
            ];

            $response = $sap->post(auth()->user(), 'ItemGroups', $payload);
            
            $sapNumber = $response['Number'] ?? null;

            // If SAP didn't return Number directly in response, fetch created group by GroupName to retrieve SAP Number
            if (!$sapNumber) {
                try {
                    $encodedName = rawurlencode($group->group_name);
                    $fetchRes = $sap->get(auth()->user(), "ItemGroups?\$filter=GroupName eq '{$encodedName}'");
                    if (isset($fetchRes['value'][0]['Number'])) {
                        $sapNumber = $fetchRes['value'][0]['Number'];
                    }
                } catch (\Exception $ex) {
                    Log::warning("Failed to fetch Group Number by name for '{$group->group_name}': " . $ex->getMessage());
                }
            }
            
            // Update group_code with SAP Number if available
            if ($sapNumber) {
                $group->group_code = $sapNumber;
            }
            
            $group->update([
                'sync_status' => 'Synced',
                'sap_status' => 'Created',
                'sync_error' => null
            ]);
            
            SystemLog::logAction('sap', 'Synced Item Group', "Item Group '{$group->group_name}' (SAP Group #{$sapNumber}) successfully pushed to SAP.");
        } catch (\Exception $e) {
            $group->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            SystemLog::logAction('sap', 'Sync Item Group Failed', "Item Group '{$group->group_name}' failed: " . $e->getMessage());
            throw $e;
        }
    }
}
