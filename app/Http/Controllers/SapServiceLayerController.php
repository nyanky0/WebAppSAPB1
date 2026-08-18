<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BusinessPartner;
use App\Models\BpGroup;
use App\Models\ChartOfAccount;
use App\Models\Config;
use App\Models\CostCenter;
use App\Models\Dimension;
use App\Models\Item;
use App\Models\ItemGroup;
use App\Models\PurchaseOrder;
use App\Models\Tax;
use App\Models\Uom;
use App\Models\UomGroup;
use App\Models\Warehouse;
use App\Models\BinLocation;
use App\Models\WithholdingTax;
use App\Models\SystemLog;
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
     * Helper to return JSON for AJAX/API requests or Session Redirects for web views.
     */
    protected function respondWithSyncResult(bool $success, string $message, string $module = 'SAP Sync Engine')
    {
        try {
            SystemLog::create([
                'user_id' => auth()->id(),
                'action' => $success ? 'SUCCESS' : 'ERROR',
                'module' => $module,
                'description' => $message,
                'ip_address' => request()->ip(),
            ]);
        } catch (\Throwable $e) {
            // Silence log creation errors if table structure differs
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
            ], $success ? 200 : 400);
        }

        if ($success) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->with('error', $message);
    }

    /**
     * Sync Purchase Order to SAP.
     */
    public function syncPurchaseOrder($id)
    {
        $po = PurchaseOrder::with('lines')->findOrFail($id);
        $result = $this->manager->pushPurchaseOrder($po);

        return $this->respondWithSyncResult(
            $result['success'],
            $result['success'] ? 'Purchase Order synced to SAP successfully!' : 'Failed to sync Purchase Order: ' . $result['message']
        );
    }

    /**
     * Sync Master Data Items from SAP.
     */
    /**
     * Sync Master Data Items from SAP.
     */
    public function syncItems()
    {
        try {
            $result = $this->manager->fetchFromSap('Items?$select=ItemCode,ItemName,ForeignName,ItemsGroupCode,CustomsGroupCode,SalesUnit,InventoryUOM,UoMGroupEntry');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Items from SAP: ' . $result['message']);
            }

            $items = is_array($result['data']) ? $result['data'] : [];
            $count = 0;

            foreach ($items as $data) {
                Item::updateOrCreate(
                    ['item_code' => $data['ItemCode']],
                    [
                        'item_name' => $data['ItemName'] ?? null,
                        'foreign_name' => $data['ForeignName'] ?? null,
                        'item_group' => (string)($data['ItemsGroupCode'] ?? ''),
                        'customs_group_code' => $data['CustomsGroupCode'] ?? null,
                        'uom' => $data['InventoryUOM'] ?? null,
                        'sales_uom' => $data['SalesUnit'] ?? null,
                        'inventory_uom' => $data['InventoryUOM'] ?? null,
                        'uom_group' => (string)($data['UoMGroupEntry'] ?? ''),
                        'sync_status' => 'Synced',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} items from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Items sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Master Data Business Partners from SAP.
     */
    public function syncBusinessPartners()
    {
        try {
            $result = $this->manager->fetchAllFromSap('BusinessPartners?$select=CardCode,CardName,CardType,GroupCode,Phone1,EmailAddress,Currency,ContactEmployees');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Business Partners: ' . $result['message']);
            }

            $bps = is_array($result['data']) ? $result['data'] : [];
            $count = 0;

            foreach ($bps as $data) {
                BusinessPartner::updateOrCreate(
                    ['card_code' => $data['CardCode']],
                    [
                        'bp_code' => $data['CardCode'],
                        'card_code' => $data['CardCode'],
                        'card_name' => $data['CardName'] ?? null,
                        'bp_name' => $data['CardName'] ?? null,
                        'name' => $data['CardName'] ?? null,
                        'card_type' => $data['CardType'] ?? null,
                        'type' => ($data['CardType'] ?? '') === 'cSupplier' ? 'Vendor' : 'Customer',
                        'group_code' => $data['GroupCode'] ?? null,
                        'phone1' => $data['Phone1'] ?? null,
                        'email' => $data['EmailAddress'] ?? null,
                        'currency' => $data['Currency'] ?? null,
                        'contact_persons' => $data['ContactEmployees'] ?? null,
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Business Partners from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Business Partners sync error: ' . $e->getMessage());
        }
    }

    public function syncBpGroups()
    {
        try {
            $result = $this->manager->fetchAllFromSap('BusinessPartnerGroups?$select=Code,Name,Type');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync BP Groups: ' . $result['message']);
            }

            $count = 0;
            foreach ($result['data'] as $data) {
                if (empty($data['Code'])) continue;
                BpGroup::updateOrCreate(
                    ['code' => $data['Code']],
                    [
                        'name' => $data['Name'] ?? null,
                        'type' => $data['Type'] ?? null,
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} BP Groups from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'BP Groups sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Master Data Branches from SAP.
     */
    public function syncBranches()
    {
        try {
            $result = $this->manager->fetchAllFromSap('Branches?$select=Code,Name,Description');

            if (!$result['success']) {
                $result = $this->manager->fetchAllFromSap('BusinessPlaces?$select=BPLID,BPLName,Disabled');
            }

            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Branches from SAP: ' . $result['message']);
            }

            $branches = is_array($result['data']) ? $result['data'] : [];
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

            return $this->respondWithSyncResult(true, "Synced {$count} Branches from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Branches sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Chart of Accounts from SAP.
     */
    public function syncChartOfAccounts()
    {
        try {
            $result = $this->manager->fetchAllFromSap('ChartOfAccounts?$select=Code,Name');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Chart of Accounts: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = $data['Code'] ?? $data['AcctCode'] ?? null;
                if (!$code) continue;

                ChartOfAccount::updateOrCreate(
                    ['code' => $code],
                    [
                        'code' => $code,
                        'acct_code' => $code,
                        'name' => $data['Name'] ?? $data['AcctName'] ?? $code,
                        'acct_name' => $data['Name'] ?? $data['AcctName'] ?? $code,
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Chart of Accounts from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Chart of Accounts sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Cost Centers from SAP.
     */
    public function syncCostCenters()
    {
        try {
            $result = $this->manager->fetchFromSap('ProfitCenters?$select=CenterCode,CenterName,InWhichDimension');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Cost Centers: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
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

            return $this->respondWithSyncResult(true, "Synced {$count} Cost Centers from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Cost Centers sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Dimensions from SAP.
     */
    public function syncDimensions()
    {
        try {
            $result = $this->manager->fetchAllFromSap('Dimensions?$select=DimensionCode,DimensionName,DimensionDescription,IsActive');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Dimensions: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = $data['DimensionCode'] ?? $data['Code'] ?? null;
                if (!$code) continue;

                Dimension::updateOrCreate(
                    ['dimension_code' => $code],
                    [
                        'dimension_name' => $data['DimensionDescription'] ?? $data['DimensionName'] ?? "Dimension {$code}",
                        'is_active' => ($data['IsActive'] ?? 'tYES') === 'tYES',
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Dimensions from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Dimensions sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Item Groups from SAP.
     */
    public function syncItemGroups()
    {
        try {
            $result = $this->manager->fetchFromSap('ItemGroups?$select=Number,GroupName');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Item Groups: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = (string) ($data['Number'] ?? $data['ItmsGrpCod'] ?? $data['GroupCode'] ?? '');
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

            return $this->respondWithSyncResult(true, "Synced {$count} Item Groups from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Item Groups sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Taxes (VatGroups) from SAP.
     * Fetches from VatGroups (OVTG equivalent) and VatGroups_Lines (VTG1 equivalent)
     * to properly handle multiple tax rates with effective dates.
     */
    public function syncTaxes()
    {
        try {
            $result = $this->manager->fetchAllFromSap('VatGroups?$select=Code,Name,Inactive,VatGroups_Lines');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Taxes from SAP: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = $data['Code'] ?? null;
                if (!$code) continue;

                $name = $data['Name'] ?? $code;
                $locked = ($data['Inactive'] ?? 'tNO') === 'tYES';
                $lines = $data['VatGroups_Lines'] ?? [];

                // If no lines, create a single record with default rate
                if (empty($lines)) {
                    Tax::updateOrCreate(
                        ['code' => $code, 'date_from' => null],
                        [
                            'name' => $name,
                            'rate' => $data['Rate'] ?? 0,
                            'date_from' => null,
                            'date_to' => null,
                            'locked' => $locked,
                            'sync_status' => 'Synced',
                            'sap_status' => 'Created',
                        ]
                    );
                    $count++;
                } else {
                    // Create/update a record for each rate line with its effective dates
                    foreach ($lines as $line) {
                        $rate = $line['Rate'] ?? 0;
                        $dateFrom = $line['DateFrom'] ?? $line['dateFrom'] ?? null;
                        $dateTo = $line['DateTo'] ?? $line['dateTo'] ?? null;

                        // Format dates if they exist
                        if ($dateFrom) {
                            $dateFrom = date('Y-m-d', strtotime($dateFrom));
                        }
                        if ($dateTo) {
                            $dateTo = date('Y-m-d', strtotime($dateTo));
                        }

                        Tax::updateOrCreate(
                            ['code' => $code, 'date_from' => $dateFrom],
                            [
                                'name' => $name,
                                'rate' => $rate,
                                'date_from' => $dateFrom,
                                'date_to' => $dateTo,
                                'locked' => $locked,
                                'sync_status' => 'Synced',
                                'sap_status' => 'Created',
                            ]
                        );
                        $count++;
                    }
                }
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Tax Codes from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Taxes sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync UOMs from SAP.
     */
    public function syncUoms()
    {
        try {
            $result = $this->manager->fetchAllFromSap('UnitOfMeasurements?$select=AbsEntry,Code,Name');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync UOMs: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = $data['Code'] ?? $data['UomCode'] ?? null;
                if (!$code) continue;

                Uom::updateOrCreate(
                    ['code' => $code],
                    [
                        'abs_entry' => $data['AbsEntry'] ?? null,
                        'name' => $data['Name'] ?? $data['UomName'] ?? $code,
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} UOMs from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'UOMs sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Warehouses from SAP.
     */
    public function syncWarehouses()
    {
        try {
            $result = $this->manager->fetchAllFromSap('Warehouses?$select=WarehouseCode,WarehouseName,EnableBinLocations');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Warehouses: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = $data['WarehouseCode'] ?? $data['Code'] ?? null;
                if (!$code) continue;

                Warehouse::updateOrCreate(
                    ['whs_code' => $code],
                    [
                        'whs_name' => $data['WarehouseName'] ?? $data['Name'] ?? $code,
                        'bin_enabled' => ($data['EnableBinLocations'] ?? 'tNO') === 'tYES',
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Warehouses from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Warehouses sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Bin Locations from SAP.
     */
    public function syncBinLocations()
    {
        try {
            $result = $this->manager->fetchAllFromSap('BinLocations?$select=AbsEntry,BinCode,Warehouse,Inactive');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Bin Locations: ' . $result['message']);
            }

            $count = 0;
            foreach ($result['data'] as $data) {
                if (empty($data['AbsEntry'])) continue;
                BinLocation::updateOrCreate(
                    ['abs_entry' => $data['AbsEntry']],
                    [
                        'bin_code' => $data['BinCode'] ?? null,
                        'whs_code' => $data['Warehouse'] ?? null,
                        'is_active' => ($data['Inactive'] ?? 'tNO') === 'tNO',
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Bin Locations from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Bin Locations sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync UoM Groups from SAP.
     */
    public function syncUomGroups()
    {
        try {
            $result = $this->manager->fetchAllFromSap('UnitOfMeasurementGroups?$select=AbsEntry,Code,Name,BaseUoM');
            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync UoM Groups: ' . $result['message']);
            }

            $count = 0;
            foreach ($result['data'] as $data) {
                if (empty($data['Code'])) continue;
                UomGroup::updateOrCreate(
                    ['group_code' => $data['Code']],
                    [
                        'abs_entry' => $data['AbsEntry'] ?? null,
                        'group_name' => $data['Name'] ?? null,
                        'base_uom' => $data['BaseUoM'] ?? null,
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} UoM Groups from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'UoM Groups sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Withholding Taxes from SAP.
     */
    public function syncWithholdingTaxes()
    {
        try {
            $result = $this->manager->fetchAllFromSap('WithholdingTaxCodes?$select=WTCode,WTName,Rate,Category,Account,Inactive');
            if (!$result['success']) {
                $result = $this->manager->fetchAllFromSap('WithholdingTax?$select=WTCode,WTName,Rate,Category,Account,Inactive');
            }

            if (!$result['success']) {
                return $this->respondWithSyncResult(false, 'Failed to sync Withholding Taxes: ' . $result['message']);
            }

            $count = 0;
            $items = is_array($result['data']) ? $result['data'] : [];
            foreach ($items as $data) {
                $code = $data['WTCode'] ?? $data['Code'] ?? null;
                if (!$code) continue;

                WithholdingTax::updateOrCreate(
                    ['code' => $code],
                    [
                        'code' => $code,
                        'wt_code' => $code,
                        'name' => $data['WTName'] ?? $data['Name'] ?? $code,
                        'wt_name' => $data['WTName'] ?? $data['Name'] ?? $code,
                        'rate' => $data['Rate'] ?? 0,
                        'category' => $data['Category'] ?? null,
                        'gl_account' => $data['Account'] ?? null,
                        'inactive' => ($data['Inactive'] ?? 'tNO') === 'tYES',
                        'sync_status' => 'Synced',
                        'sap_status' => 'Created',
                    ]
                );
                $count++;
            }

            return $this->respondWithSyncResult(true, "Synced {$count} Withholding Tax Codes from SAP successfully.");
        } catch (\Throwable $e) {
            return $this->respondWithSyncResult(false, 'Withholding Taxes sync error: ' . $e->getMessage());
        }
    }

    /**
     * Sync Period Indicators from SAP (Config Settings).
     */
    public function syncPeriodIndicators()
    {
        $result = $this->manager->fetchFromSap('PeriodIndicators');
        if (!$result['success']) {
            return $this->respondWithSyncResult(false, 'Failed to sync Period Indicators: ' . $result['message']);
        }

        $indicators = array_column($result['data'], 'PeriodIndicatorName');
        $firstIndicator = reset($indicators);

        if ($firstIndicator) {
            $config = Config::firstOrCreate([]);
            $config->update(['period_indicator' => $firstIndicator]);
        }

        return $this->respondWithSyncResult(true, 'Period Indicators synced from SAP successfully.');
    }
}
