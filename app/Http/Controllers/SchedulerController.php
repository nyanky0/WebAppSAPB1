<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Tax;
use App\Models\BusinessPartner;
use App\Models\PurchaseRequest;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Support\Facades\Log;

class SchedulerController extends Controller
{
    public function masterData(Request $request)
    {
        $statuses = $request->input('statuses', ['Draft', 'Failed']);
        
        $items = collect();
        $itemGroups = collect();
        $taxes = collect();
        $businessPartners = collect();

        if (in_array('Items', $request->input('types', ['Items', 'ItemGroups', 'Taxes', 'BusinessPartners']))) {
            $items = Item::whereIn('sync_status', $statuses)->get();
        }

        if (in_array('ItemGroups', $request->input('types', ['Items', 'ItemGroups', 'Taxes', 'BusinessPartners']))) {
            $itemGroups = ItemGroup::whereIn('sync_status', $statuses)->get();
        }

        if (in_array('Taxes', $request->input('types', ['Items', 'ItemGroups', 'Taxes', 'BusinessPartners']))) {
            $taxes = Tax::whereIn('sync_status', $statuses)->get();
        }

        if (in_array('BusinessPartners', $request->input('types', ['Items', 'ItemGroups', 'Taxes', 'BusinessPartners']))) {
            $businessPartners = BusinessPartner::whereIn('sync_status', $statuses)->get();
        }

        return view('scheduler.master-data', compact('items', 'itemGroups', 'taxes', 'businessPartners', 'statuses'));
    }

    public function document(Request $request)
    {
        $statuses = $request->input('statuses', ['Draft', 'Failed']);
        
        $purchaseRequests = PurchaseRequest::with('lines')->whereIn('sync_status', $statuses)->latest()->get();

        return view('scheduler.document', compact('purchaseRequests', 'statuses'));
    }
    
    public function syncNow(Request $request)
    {
        set_time_limit(300);
        
        $type = $request->input('type');
        $id = $request->input('id');
        
        try {
            $config = Config::first();
            if (!$config || !$config->base_url) {
                return back()->with('error', 'SAP Configuration missing.');
            }
            
            $sap = new SapService($config);
            
            if ($type === 'PurchaseRequest') {
                $model = PurchaseRequest::findOrFail($id);
                $controller = new \App\Http\Controllers\PurchaseRequestController();
                $controller->pushToSap($model, $sap);
            } elseif ($type === 'Item') {
                $model = Item::findOrFail($id);
                $controller = new \App\Http\Controllers\ItemController();
                $controller->pushToSap($model, $sap);
            } elseif ($type === 'ItemGroup') {
                $model = ItemGroup::findOrFail($id);
                $controller = new \App\Http\Controllers\ItemGroupController();
                $controller->pushToSap($model, $sap);
            } elseif ($type === 'Tax') {
                $model = Tax::findOrFail($id);
                $controller = new \App\Http\Controllers\TaxController();
                $controller->pushToSap($model, $sap);
            } elseif ($type === 'BusinessPartner') {
                $model = BusinessPartner::findOrFail($id);
                $controller = new \App\Http\Controllers\BusinessPartnerController();
                $controller->pushToSap($model, $sap);
            } else {
                return back()->with('error', 'Unknown type: ' . $type);
            }
            
            return back()->with('success', 'Successfully synchronized ' . $type . '!');
            
        } catch (\Exception $e) {
            Log::error('Manual Sync Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to synchronize: ' . $e->getMessage());
        }
    }

    public function syncAllMasterData(Request $request)
    {
        set_time_limit(300);
        $syncedCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            $config = Config::first();
            if (!$config || !$config->base_url) {
                return back()->with('error', 'SAP Configuration missing.');
            }

            $sap = new SapService($config);
            
            // 1. Sync Item Groups
            $itemGroups = ItemGroup::where('sync_status', '!=', 'Synced')->get();
            $igController = new \App\Http\Controllers\ItemGroupController();
            foreach ($itemGroups as $ig) {
                try {
                    $igController->pushToSap($ig, $sap);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "ItemGroup {$ig->group_name}: " . $e->getMessage();
                }
            }

            // 2. Sync Items
            $items = Item::where('sync_status', '!=', 'Synced')->get();
            $itemController = new \App\Http\Controllers\ItemController();
            foreach ($items as $item) {
                try {
                    $itemController->pushToSap($item, $sap);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Item {$item->item_code}: " . $e->getMessage();
                }
            }

            // 3. Sync Taxes
            $taxes = Tax::where('sync_status', '!=', 'Synced')->get();
            $taxController = new \App\Http\Controllers\TaxController();
            foreach ($taxes as $tax) {
                try {
                    $taxController->pushToSap($tax, $sap);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "Tax {$tax->code}: " . $e->getMessage();
                }
            }

            // 4. Sync Business Partners
            $bps = BusinessPartner::where('sync_status', '!=', 'Synced')->get();
            $bpController = new \App\Http\Controllers\BusinessPartnerController();
            foreach ($bps as $bp) {
                try {
                    $bpController->pushToSap($bp, $sap);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "BusinessPartner {$bp->bp_code}: " . $e->getMessage();
                }
            }

            $msg = "Batch Master Data Sync finished. Synced: {$syncedCount}. Failed: {$failedCount}.";
            if ($failedCount > 0) {
                return back()->with('warning', $msg . " Errors: " . implode(' | ', array_slice($errors, 0, 3)));
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Sync All Master Data Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to batch sync master data: ' . $e->getMessage());
        }
    }

    public function syncAllDocuments(Request $request)
    {
        set_time_limit(300);
        $syncedCount = 0;
        $failedCount = 0;
        $errors = [];

        try {
            $config = Config::first();
            if (!$config || !$config->base_url) {
                return back()->with('error', 'SAP Configuration missing.');
            }

            $sap = new SapService($config);

            $purchaseRequests = PurchaseRequest::with('lines')->where('sync_status', '!=', 'Synced')->get();
            $prController = new \App\Http\Controllers\PurchaseRequestController();
            foreach ($purchaseRequests as $pr) {
                try {
                    $prController->pushToSap($pr, $sap);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = "PR #{$pr->id}: " . $e->getMessage();
                }
            }

            $msg = "Batch Document Sync finished. Synced: {$syncedCount}. Failed: {$failedCount}.";
            if ($failedCount > 0) {
                return back()->with('warning', $msg . " Errors: " . implode(' | ', array_slice($errors, 0, 3)));
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            Log::error('Sync All Documents Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to batch sync documents: ' . $e->getMessage());
        }
    }
}
