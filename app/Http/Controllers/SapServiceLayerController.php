<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessPartner;
use App\Models\ChartOfAccount;
use App\Models\Config;
use App\Models\CostCenter;
use App\Models\Dimension;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\PurchaseOrder;
use App\Models\Tax;
use App\Models\Uom;
use App\Models\Warehouse;
use App\Models\WithholdingTax;
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

    /**
     * Sync Chart of Accounts from SAP.
     */
    public function syncChartOfAccounts()
    {
        $result = $this->manager->fetchFromSap('ChartOfAccounts');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Chart of Accounts: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['Code'] ?? $data['AcctCode'] ?? null;
            if (!$code) continue;

            ChartOfAccount::updateOrCreate(
                ['acct_code' => $code],
                [
                    'code' => $code,
                    'name' => $data['Name'] ?? $data['AcctName'] ?? $code,
                    'acct_name' => $data['Name'] ?? $data['AcctName'] ?? $code,
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Chart of Accounts from SAP successfully.");
    }

    /**
     * Sync Cost Centers from SAP.
     */
    public function syncCostCenters()
    {
        $result = $this->manager->fetchFromSap('ProfitCenters');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Cost Centers: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['CenterCode'] ?? $data['Code'] ?? null;
            if (!$code) continue;

            CostCenter::updateOrCreate(
                ['center_code' => $code],
                [
                    'center_name' => $data['CenterName'] ?? $data['Name'] ?? $code,
                    'dimension_code' => $data['InWhichDimension'] ?? 1,
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Cost Centers from SAP successfully.");
    }

    /**
     * Sync Dimensions from SAP.
     */
    public function syncDimensions()
    {
        $result = $this->manager->fetchFromSap('Dimensions');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Dimensions: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['DimensionCode'] ?? $data['Code'] ?? null;
            if (!$code) continue;

            Dimension::updateOrCreate(
                ['dimension_code' => $code],
                [
                    'dimension_name' => $data['DimensionName'] ?? $data['Name'] ?? "Dimension {$code}",
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Dimensions from SAP successfully.");
    }

    /**
     * Sync Item Groups from SAP.
     */
    public function syncItemGroups()
    {
        $result = $this->manager->fetchFromSap('ItemGroups');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Item Groups: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = (string) ($data['Number'] ?? $data['GroupCode'] ?? '');
            if (empty($code)) continue;

            ItemGroup::updateOrCreate(
                ['group_code' => $code],
                [
                    'group_name' => $data['GroupName'] ?? "Group {$code}",
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Item Groups from SAP successfully.");
    }

    /**
     * Sync Taxes (VatGroups) from SAP.
     */
    public function syncTaxes()
    {
        $result = $this->manager->fetchFromSap('VatGroups');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Taxes from SAP: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['Code'] ?? null;
            if (!$code) continue;

            Tax::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['Name'] ?? $code,
                    'rate' => $data['VatGroups_Lines'][0]['Rate'] ?? $data['Rate'] ?? 0,
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Tax Codes from SAP successfully.");
    }

    /**
     * Sync UOMs from SAP.
     */
    public function syncUoms()
    {
        $result = $this->manager->fetchFromSap('UnitOfMeasurements');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync UOMs: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['Code'] ?? $data['UomCode'] ?? null;
            if (!$code) continue;

            Uom::updateOrCreate(
                ['code' => $code],
                [
                    'name' => $data['Name'] ?? $data['UomName'] ?? $code,
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} UOMs from SAP successfully.");
    }

    /**
     * Sync Warehouses from SAP.
     */
    public function syncWarehouses()
    {
        $result = $this->manager->fetchFromSap('Warehouses');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Warehouses: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['WarehouseCode'] ?? $data['Code'] ?? null;
            if (!$code) continue;

            Warehouse::updateOrCreate(
                ['whs_code' => $code],
                [
                    'whs_name' => $data['WarehouseName'] ?? $data['Name'] ?? $code,
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Warehouses from SAP successfully.");
    }

    /**
     * Sync Withholding Taxes from SAP.
     */
    public function syncWithholdingTaxes()
    {
        $result = $this->manager->fetchFromSap('WithholdingTaxCodes');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Withholding Taxes: ' . $result['message']);
        }

        $count = 0;
        foreach ($result['data'] as $data) {
            $code = $data['WTCode'] ?? $data['Code'] ?? null;
            if (!$code) continue;

            WithholdingTax::updateOrCreate(
                ['wt_code' => $code],
                [
                    'wt_name' => $data['WTName'] ?? $data['Name'] ?? $code,
                    'rate' => $data['Rate'] ?? 0,
                    'sync_status' => 'Synced',
                    'sap_status' => 'Created',
                ]
            );
            $count++;
        }

        return back()->with('success', "Synced {$count} Withholding Tax Codes from SAP successfully.");
    }

    /**
     * Sync Period Indicators from SAP (Config Settings).
     */
    public function syncPeriodIndicators()
    {
        $result = $this->manager->fetchFromSap('PeriodIndicators');
        if (!$result['success']) {
            return back()->with('error', 'Failed to sync Period Indicators: ' . $result['message']);
        }

        $indicators = array_column($result['data'], 'PeriodIndicatorName');
        $firstIndicator = reset($indicators);

        if ($firstIndicator) {
            $config = Config::firstOrCreate([]);
            $config->update(['period_indicator' => $firstIndicator]);
        }

        return back()->with('success', 'Period Indicators synced from SAP successfully.');
    }
}
