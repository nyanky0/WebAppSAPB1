<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PurchaseRequest;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\Tax;
use App\Models\BusinessPartner;
use App\Models\Config;
use App\Services\SapService;
use Illuminate\Support\Facades\Log;

class SyncDraftDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SapService $sap): void
    {
        $config = Config::first();
        if (!$config || !$config->scheduler_active) {
            return;
        }

        // 1. Sync Item Groups
        $draftItemGroups = ItemGroup::where('sync_status', 'Draft')->orWhere('sync_status', 'Failed')->get();
        if ($draftItemGroups->isNotEmpty()) {
            $controller = new \App\Http\Controllers\ItemGroupController();
            foreach ($draftItemGroups as $model) {
                try {
                    $controller->pushToSap($model, $sap);
                } catch (\Exception $e) {
                    Log::error("Scheduler failed to sync ItemGroup #{$model->id}: " . $e->getMessage());
                }
            }
        }

        // 2. Sync Items
        $draftItems = Item::where('sync_status', 'Draft')->orWhere('sync_status', 'Failed')->get();
        if ($draftItems->isNotEmpty()) {
            $controller = new \App\Http\Controllers\ItemController();
            foreach ($draftItems as $model) {
                try {
                    $controller->pushToSap($model, $sap);
                } catch (\Exception $e) {
                    Log::error("Scheduler failed to sync Item {$model->item_code}: " . $e->getMessage());
                }
            }
        }

        // 3. Sync Taxes
        $draftTaxes = Tax::where('sync_status', 'Draft')->orWhere('sync_status', 'Failed')->get();
        if ($draftTaxes->isNotEmpty()) {
            $controller = new \App\Http\Controllers\TaxController();
            foreach ($draftTaxes as $model) {
                try {
                    $controller->pushToSap($model, $sap);
                } catch (\Exception $e) {
                    Log::error("Scheduler failed to sync Tax {$model->code}: " . $e->getMessage());
                }
            }
        }

        // 4. Sync Business Partners
        $draftBps = BusinessPartner::where('sync_status', 'Draft')->orWhere('sync_status', 'Failed')->get();
        if ($draftBps->isNotEmpty()) {
            $controller = new \App\Http\Controllers\BusinessPartnerController();
            foreach ($draftBps as $model) {
                try {
                    $controller->pushToSap($model, $sap);
                } catch (\Exception $e) {
                    Log::error("Scheduler failed to sync BP {$model->bp_code}: " . $e->getMessage());
                }
            }
        }

        // 5. Sync Purchase Requests
        $draftPRs = PurchaseRequest::where('sync_status', 'Draft')->orWhere('sync_status', 'Failed')->get();
        if ($draftPRs->isNotEmpty()) {
            $controller = new \App\Http\Controllers\PurchaseRequestController();
            foreach ($draftPRs as $pr) {
                try {
                    $controller->pushToSap($pr, $sap);
                } catch (\Exception $e) {
                    Log::error("Scheduler failed to sync PR #{$pr->id}: " . $e->getMessage());
                }
            }
        }
    }
}
