<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Role::create([
            'name' => 'Super Admin',
            'permissions' => [
                'Administrator.Config',
                'Administrator.Roles',
                'Administrator.Users',
                'Administrator.Logs',
                'Administrator.Items',
                'Administrator.Uoms',
                'Administrator.Warehouses',
                'Administrator.ChartOfAccounts',
                'Administrator.Dimensions',
                'Administrator.CostCenters',
                'Administrator.Taxes',
                'Administrator.WithholdingTaxes',
                'Administrator.Branches',
                'Administrator.BusinessPartners',
                'Purchase.PurchaseRequest',
                'Purchase.PurchaseQuotation',
                'Purchase.PurchaseOrder',
                'Approval.Stages',
                'Approval.Templates',
                'Approval.Decisions',
                'Scheduler.MasterData',
                'Scheduler.Document',
            ],
        ]);
    }
}
