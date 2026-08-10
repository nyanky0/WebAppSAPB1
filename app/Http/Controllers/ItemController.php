<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $items = $query->paginate(20)->withQueryString();
        
        return view('items.index', compact('items', 'sort', 'direction'));
    }

    public function sync(Request $request)
    {
        set_time_limit(300); // Allow script to run longer for syncing
        
        try {
            $config = Config::first();
            if (!$config || !$config->base_url || !$config->database || !$config->period_indicator) {
                return redirect()->back()->with('error', 'SAP Configuration is incomplete. Please setup the connection first.');
            }
            
            $sap = new SapService($config);
            $user = auth()->user();
            
            // 1. Fetch ItemGroups first to build a mapping dictionary
            $itemGroups = [];
            try {
                // Fetch item groups, this is usually small enough for one request
                $groupResponse = $sap->get($user, 'ItemGroups?$select=Number,GroupName');
                if (isset($groupResponse['value'])) {
                    foreach ($groupResponse['value'] as $group) {
                        $itemGroups[$group['Number']] = $group['GroupName'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch ItemGroups: ' . $e->getMessage());
                // Continue even if item groups fail
            }
            
            // 2. Fetch Items using Pagination Loop
            $itemsSynced = 0;
            $nextLink = '/Items?$select=ItemCode,ItemName,ForeignName,InventoryUOM,ItemsGroupCode,Valid';
            
            DB::beginTransaction();
            
            try {
                while ($nextLink) {
                    // Extract just the path + query since the nextLink might be a full URL from SAP
                    $path = $nextLink;
                    if (strpos($nextLink, 'http') === 0) {
                        $parsedUrl = parse_url($nextLink);
                        $path = $parsedUrl['path'] . (isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '');
                        // Strip /b1s/v1/ or /b1s/v2/ prefix to avoid double prefixes
                        $path = preg_replace('/^\/b1s\/v[12]\//', '/', $path);
                    }
                    
                    // If the path doesn't start with a slash, make sure we format it correctly
                    $path = ltrim($path, '/');

                    $response = $sap->get($user, $path);
                    
                    if (isset($response['value']) && is_array($response['value'])) {
                        foreach ($response['value'] as $sapItem) {
                            $itemGroupCode = $sapItem['ItemsGroupCode'] ?? null;
                            $groupName = $itemGroups[$itemGroupCode] ?? $itemGroupCode;
                            
                            $isActive = true;
                            if (isset($sapItem['Valid'])) {
                                $isActive = ($sapItem['Valid'] === 'tYES' || $sapItem['Valid'] === 'Y');
                            }
                            
                            Item::updateOrCreate(
                                ['item_code' => $sapItem['ItemCode']],
                                [
                                    'item_name' => $sapItem['ItemName'] ?? null,
                                    'foreign_name' => $sapItem['ForeignName'] ?? null,
                                    'uom' => $sapItem['InventoryUOM'] ?? null,
                                    'item_group' => $groupName,
                                    'is_active' => $isActive
                                ]
                            );
                            
                            $itemsSynced++;
                        }
                    }
                    
                    // Handle pagination
                    if (isset($response['odata.nextLink'])) {
                        $nextLink = $response['odata.nextLink'];
                    } else if (isset($response['@odata.nextLink'])) {
                        $nextLink = $response['@odata.nextLink'];
                    } else {
                        $nextLink = null;
                    }
                }
                
                DB::commit();
                
                return redirect()->route('items.index')->with('success', "Successfully synced {$itemsSynced} items from SAP Business One.");
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Item Sync Error: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Error syncing items: ' . $e->getMessage());
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
