<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessPartner;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Services\SapServiceLayerManager;
use Exception;
use Illuminate\Http\Request;

class SapServiceLayerController extends Controller
{
    protected $manager;

    public function __construct(SapServiceLayerManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Sync Purchase Order to SAP.
     */
    public function syncPurchaseOrder($id)
    {
        $po = PurchaseOrder::with('lines')->findOrFail($id);
        $result = $this->manager->pushPurchaseOrder($po);

        if ($result['success']) {
            return back()->with('success', 'Purchase Order synced to SAP successfully!');
        }

        return back()->with('error', 'Failed to sync Purchase Order: ' . $result['message']);
    }

    /**
     * Sync Master Data Items from SAP.
     */
    public function syncItems()
    {
        $result = $this->manager->fetchFromSap('Items');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Items from SAP: ' . $result['message']);
        }

        $items = $result['data'];
        $count = 0;

        foreach ($items as $data) {
            Item::updateOrCreate(
                ['item_code' => $data['ItemCode']],
                [
                    'item_name' => $data['ItemName'] ?? null,
                    'foreign_name' => $data['ForeignName'] ?? null,
                    'items_group_code' => $data['ItemsGroupCode'] ?? null,
                    'customs_group_code' => $data['CustomsGroupCode'] ?? null,
                    'sales_uom' => $data['SalesUnit'] ?? null,
                    'inventory_uom' => $data['InventoryUOM'] ?? null,
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} items from SAP successfully.");
    }

    /**
     * Sync Master Data Business Partners from SAP.
     */
    public function syncBusinessPartners()
    {
        $result = $this->manager->fetchFromSap('BusinessPartners');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Business Partners: ' . $result['message']);
        }

        $bps = $result['data'];
        $count = 0;

        foreach ($bps as $data) {
            BusinessPartner::updateOrCreate(
                ['card_code' => $data['CardCode']],
                [
                    'card_name' => $data['CardName'] ?? null,
                    'card_type' => $data['CardType'] ?? null,
                    'group_code' => $data['GroupCode'] ?? null,
                    'phone1' => $data['Phone1'] ?? null,
                    'email' => $data['EmailAddress'] ?? null,
                    'currency' => $data['Currency'] ?? null,
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Business Partners from SAP successfully.");
    }

    /**
     * Sync Master Data Branches from SAP.
     */
    public function syncBranches()
    {
        $result = $this->manager->fetchFromSap('Branches');
        if (!$result['success']) {
            $result = $this->manager->fetchFromSap('BusinessPlaces');
        }

        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Branches from SAP: ' . $result['message']);
        }

        $branches = $result['data'];
        $count = 0;

        foreach ($branches as $data) {
            $code = (string) ($data['Code'] ?? $data['BPLID'] ?? $data['Name'] ?? '');
            if (empty($code)) continue;

            Branch::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['Name'] ?? $data['BPLName'] ?? $code,
                    'description' => $data['Description'] ?? $data['BPLName'] ?? $code,
                    'disabled' => ($data['Disabled'] ?? 'tNO') === 'tYES',
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Branches from SAP successfully.");
    }
}
