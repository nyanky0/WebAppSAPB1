<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\SystemLog;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        // Simple search and filter implementation
        $query = Item::query();
        
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('item_code', 'ilike', "%{$search}%")
                  ->orWhere('item_name', 'ilike', "%{$search}%")
                  ->orWhere('foreign_name', 'ilike', "%{$search}%")
                  ->orWhere('item_group', 'ilike', "%{$search}%");
        }
        
        // Sorting
        $sort = $request->get('sort', 'item_code');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);
        
        // Pagination
        $perPage = (int) $request->get('per_page', 20);
        if (!in_array($perPage, [20, 50, 100])) {
            $perPage = 20;
        }
        $items = $query->paginate($perPage)->withQueryString();
        
        return view('items.index', compact('items', 'sort', 'direction', 'perPage'));
    }

    public function sync(Request $request)
    {
        $sapController = app(SapServiceLayerController::class);
        return $sapController->syncItems();
    }

    public function create()
    {
        $itemGroups = \App\Models\ItemGroup::pluck('group_name', 'group_name');
        return view('items.create', compact('itemGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_code' => 'required|string|max:50|unique:items,item_code',
            'item_name' => 'required|string|max:255',
            'foreign_name' => 'nullable|string|max:255',
            'uom' => 'nullable|string|max:50',
            'item_group' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::create([
                'item_code' => $validated['item_code'],
                'item_name' => $validated['item_name'],
                'foreign_name' => $validated['foreign_name'] ?? null,
                'uom' => $validated['uom'] ?? null,
                'item_group' => $validated['item_group'] ?? null,
                'is_active' => $request->has('is_active'),
                'sync_status' => 'Draft',
            ]);

            DB::commit();

            if ($request->has('instant_sync') && $request->instant_sync) {
                try {
                    $config = Config::first();
                    $sap = new SapService($config);
                    $this->pushToSap($item, $sap);
                    return redirect()->route('items.index')->with('success', 'Item created and instantly synced to SAP!');
                } catch (\Exception $e) {
                    return redirect()->route('items.index')->with('warning', 'Item created locally but instant sync failed: ' . $e->getMessage());
                }
            }

            return redirect()->route('items.index')->with('success', 'Item created successfully and saved as Draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create item: ' . $e->getMessage())->withInput();
        }
    }

    public function pushSingle(Item $item)
    {
        try {
            $config = Config::first();
            $sap = new SapService($config);
            $this->pushToSap($item, $sap);
            return redirect()->route('items.index')->with('success', "Item '{$item->item_code}' pushed successfully to SAP!");
        } catch (\Exception $e) {
            return redirect()->route('items.index')->with('error', "Failed to push item to SAP: " . $e->getMessage());
        }
    }

    public function pushToSap(Item $item, SapService $sap)
    {
        try {
            // Find SAP Group Code mapping if possible
            $groupCode = null;
            if ($item->item_group) {
                $sapGroup = \App\Models\ItemGroup::where('group_name', $item->item_group)->first();
                if ($sapGroup && $sapGroup->sap_number) {
                    $groupCode = $sapGroup->sap_number;
                }
            }

            $payload = [
                'ItemCode' => $item->item_code,
                'ItemName' => $item->item_name,
                'ForeignName' => $item->foreign_name,
                'InventoryUOM' => $item->uom,
                'Valid' => $item->is_active ? 'tYES' : 'tNO'
            ];

            if ($groupCode) {
                $payload['ItemsGroupCode'] = $groupCode;
            }

            $response = $sap->post(auth()->user(), 'Items', $payload);
            
            $item->update([
                'sync_status' => 'Synced',
                'sap_status' => 'Created',
                'sync_error' => null
            ]);
            
            SystemLog::logAction('sap', 'Synced Item', "Item {$item->item_code} successfully pushed to SAP.");
        } catch (\Exception $e) {
            $item->update([
                'sync_status' => 'Failed',
                'sync_error' => $e->getMessage()
            ]);
            SystemLog::logAction('sap', 'Sync Item Failed', "Item {$item->item_code} failed: " . $e->getMessage());
            throw $e;
        }
    }
}
