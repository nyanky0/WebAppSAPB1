<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        // 1. Seed UoMs & UoM Groups
        \App\Models\Uom::updateOrCreate(['code' => 'PCS'], ['name' => 'Pieces', 'sync_status' => 'Synced']);
        \App\Models\Uom::updateOrCreate(['code' => 'BOX'], ['name' => 'Boxes', 'sync_status' => 'Synced']);
        \App\Models\Uom::updateOrCreate(['code' => 'KG'], ['name' => 'Kilograms', 'sync_status' => 'Synced']);
        \App\Models\Uom::updateOrCreate(['code' => 'SET'], ['name' => 'Sets', 'sync_status' => 'Synced']);

        \App\Models\UomGroup::updateOrCreate(
            ['group_code' => 'GRP-BOX10'],
            [
                'group_name' => 'Box of 10 Pieces Group',
                'base_uom' => 'PCS',
                'conversions' => [
                    ['alt_uom' => 'BOX', 'base_qty' => 10, 'alt_qty' => 1]
                ],
                'sync_status' => 'Synced'
            ]
        );

        // 2. Seed Item Groups
        \App\Models\ItemGroup::updateOrCreate(['sap_number' => 100], ['group_name' => 'Items', 'default_uom_group' => 'Manual', 'default_uom' => 'PCS', 'sync_status' => 'Synced']);
        \App\Models\ItemGroup::updateOrCreate(['sap_number' => 101], ['group_name' => 'Services', 'default_uom_group' => 'Manual', 'default_uom' => 'SET', 'sync_status' => 'Synced']);

        // 3. Seed Items
        \App\Models\Item::updateOrCreate(
            ['item_code' => 'A00001'],
            [
                'item_name' => 'JB-100 Office Desk',
                'uom' => 'PCS',
                'purchasing_uom' => 'PCS',
                'inventory_uom' => 'PCS',
                'sales_uom' => 'PCS',
                'uom_group_type' => 'Manual',
                'item_group' => 'Items',
                'is_active' => true,
                'sync_status' => 'Synced'
            ]
        );

        // 4. Seed Warehouses & Bin Locations
        $wh1 = \App\Models\Warehouse::updateOrCreate(
            ['whs_code' => '01'],
            [
                'whs_name' => 'General Warehouse',
                'is_active' => true,
                'location' => 'Main Headquarter',
                'bin_enabled' => true,
                'sync_status' => 'Synced'
            ]
        );
        \App\Models\BinLocation::updateOrCreate(['abs_entry' => 1], ['bin_code' => '01-SYSTEM-BIN-LOCATION', 'whs_code' => '01', 'is_active' => true, 'sync_status' => 'Synced']);
        \App\Models\BinLocation::updateOrCreate(['abs_entry' => 2], ['bin_code' => '01-A1-01', 'whs_code' => '01', 'is_active' => true, 'sync_status' => 'Synced']);

        \App\Models\Warehouse::updateOrCreate(
            ['whs_code' => '02'],
            [
                'whs_name' => 'Secondary Warehouse',
                'is_active' => true,
                'location' => 'Sub-Branch Factory',
                'bin_enabled' => false,
                'sync_status' => 'Synced'
            ]
        );

        // 5. Seed Taxes
        \App\Models\Tax::updateOrCreate(['code' => 'VAT11'], ['name' => 'Value Added Tax 11%', 'rate' => 11.00, 'locked' => false]);
        \App\Models\Tax::updateOrCreate(['code' => 'EXEMPT'], ['name' => 'Exempt Tax', 'rate' => 0.00, 'locked' => false]);

        // 6. Seed Business Partners
        \App\Models\BusinessPartner::updateOrCreate(
            ['bp_code' => 'V10000'],
            [
                'name' => 'Acme Supplies Co.',
                'type' => 'Vendor',
                'contact_persons' => ['John Smith', 'Jane Doe'],
                'sync_status' => 'Synced'
            ]
        );

        // 7. Seed Chart of Accounts
        \App\Models\ChartOfAccount::updateOrCreate(
            ['code' => '100000'],
            [
                'name' => 'Cash & Bank Accounts',
                'external_code' => '100-000',
                'currency' => 'USD',
                'levels' => 1,
                'account_type' => 'Title',
                'is_control_account' => false,
                'is_cash_account' => true,
                'is_active' => true,
                'category' => 'Assets',
                'sync_status' => 'Synced'
            ]
        );
        \App\Models\ChartOfAccount::updateOrCreate(
            ['code' => '101100'],
            [
                'name' => 'Main Operating Cash Account',
                'external_code' => '101-100',
                'currency' => 'USD',
                'levels' => 2,
                'account_type' => 'Postable',
                'is_control_account' => false,
                'is_cash_account' => true,
                'is_active' => true,
                'category' => 'Assets',
                'sync_status' => 'Synced'
            ]
        );
    }
}
