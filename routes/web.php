<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseRequestController;

Route::get('/', function () {
    return redirect('/login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/widgets', [DashboardController::class, 'updateWidgets'])->name('dashboard.widgets');

    Route::get('/config/missing', function() {
        return view('config.missing');
    })->name('config.missing');
    
    Route::get('/config', [ConfigController::class, 'index'])->name('config.index');
    Route::post('/config', [ConfigController::class, 'update'])->name('config.update');
    Route::post('/config/personal', [ConfigController::class, 'updatePersonal'])->name('config.updatePersonal');
    Route::post('/api/config/fetch-period', [ConfigController::class, 'fetchPeriodIndicator'])->name('api.config.fetch-period');
    Route::post('/api/config/fetch-databases', [ConfigController::class, 'fetchDatabases'])->name('api.config.fetch-databases');
    
    Route::get('/logs', [\App\Http\Controllers\SystemLogController::class, 'index'])->name('logs.index');
    Route::get('/taxes', [\App\Http\Controllers\TaxController::class, 'index'])->name('taxes.index');
    Route::get('/taxes/create', [\App\Http\Controllers\TaxController::class, 'create'])->name('taxes.create');
    Route::post('/taxes', [\App\Http\Controllers\TaxController::class, 'store'])->name('taxes.store');
    Route::post('/taxes/sync', [\App\Http\Controllers\TaxController::class, 'sync'])->name('taxes.sync');

    Route::get('/business-partners', [\App\Http\Controllers\BusinessPartnerController::class, 'index'])->name('business-partners.index');
    Route::get('/business-partners/create', [\App\Http\Controllers\BusinessPartnerController::class, 'create'])->name('business-partners.create');
    Route::post('/business-partners', [\App\Http\Controllers\BusinessPartnerController::class, 'store'])->name('business-partners.store');
    Route::post('/business-partners/sync', [\App\Http\Controllers\BusinessPartnerController::class, 'sync'])->name('business-partners.sync');

    Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    Route::get('/item-groups', [\App\Http\Controllers\ItemGroupController::class, 'index'])->name('item-groups.index');
    Route::get('/item-groups/create', [\App\Http\Controllers\ItemGroupController::class, 'create'])->name('item-groups.create');
    Route::post('/item-groups', [\App\Http\Controllers\ItemGroupController::class, 'store'])->name('item-groups.store');
    Route::post('/item-groups/sync', [\App\Http\Controllers\ItemGroupController::class, 'sync'])->name('item-groups.sync');
    Route::post('/item-groups/{group}/push', [\App\Http\Controllers\ItemGroupController::class, 'pushSingle'])->name('item-groups.push');
    
    Route::get('/items', [\App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::get('/items/create', [\App\Http\Controllers\ItemController::class, 'create'])->name('items.create');
    Route::post('/items', [\App\Http\Controllers\ItemController::class, 'store'])->name('items.store');
    Route::post('/items/sync', [\App\Http\Controllers\ItemController::class, 'sync'])->name('items.sync');
    Route::post('/items/{item}/push', [\App\Http\Controllers\ItemController::class, 'pushSingle'])->name('items.push');

    Route::get('/uoms', [\App\Http\Controllers\UomController::class, 'index'])->name('uoms.index');
    Route::get('/uoms/create', [\App\Http\Controllers\UomController::class, 'create'])->name('uoms.create');
    Route::post('/uoms', [\App\Http\Controllers\UomController::class, 'store'])->name('uoms.store');
    Route::post('/uoms/sync', [\App\Http\Controllers\UomController::class, 'sync'])->name('uoms.sync');

    Route::get('/chart-of-accounts', [\App\Http\Controllers\ChartOfAccountController::class, 'index'])->name('chart-of-accounts.index');
    Route::post('/chart-of-accounts/sync', [\App\Http\Controllers\ChartOfAccountController::class, 'sync'])->name('chart-of-accounts.sync');

    Route::get('/warehouses', [\App\Http\Controllers\WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('/warehouses/create', [\App\Http\Controllers\WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('/warehouses', [\App\Http\Controllers\WarehouseController::class, 'store'])->name('warehouses.store');
    Route::post('/warehouses/sync', [\App\Http\Controllers\WarehouseController::class, 'sync'])->name('warehouses.sync');

    Route::get('/dimensions', [\App\Http\Controllers\DimensionController::class, 'index'])->name('dimensions.index');
    Route::post('/dimensions/sync', [\App\Http\Controllers\DimensionController::class, 'sync'])->name('dimensions.sync');

    Route::get('/cost-centers', [\App\Http\Controllers\CostCenterController::class, 'index'])->name('cost-centers.index');
    Route::post('/cost-centers/sync', [\App\Http\Controllers\CostCenterController::class, 'sync'])->name('cost-centers.sync');

    Route::get('/withholding-taxes', [\App\Http\Controllers\WithholdingTaxController::class, 'index'])->name('withholding-taxes.index');
    Route::post('/withholding-taxes/sync', [\App\Http\Controllers\WithholdingTaxController::class, 'sync'])->name('withholding-taxes.sync');

    // Dedicated SAP Service Layer Controller Sync Routes
    Route::post('/sap/sync/purchase-orders/{id}', [\App\Http\Controllers\SapServiceLayerController::class, 'syncPurchaseOrder'])->name('sap.sync.po');
    Route::post('/sap/sync/items', [\App\Http\Controllers\SapServiceLayerController::class, 'syncItems'])->name('sap.sync.items');
    Route::post('/sap/sync/business-partners', [\App\Http\Controllers\SapServiceLayerController::class, 'syncBusinessPartners'])->name('sap.sync.bp');
    Route::post('/sap/sync/branches', [\App\Http\Controllers\SapServiceLayerController::class, 'syncBranches'])->name('sap.sync.branches');

    Route::get('/branches', [\App\Http\Controllers\BranchController::class, 'index'])->name('branches.index');
    Route::post('/branches/sync', [\App\Http\Controllers\BranchController::class, 'sync'])->name('branches.sync');
    
    Route::get('/purchase-request', [\App\Http\Controllers\PurchaseRequestController::class, 'index'])->name('purchase-request.index');
    Route::get('/purchase-request/create', [\App\Http\Controllers\PurchaseRequestController::class, 'create'])->name('purchase-request.create');
    Route::get('/purchase-request/{id}/duplicate', [\App\Http\Controllers\PurchaseRequestController::class, 'duplicate'])->name('purchase-request.duplicate');
    Route::post('/purchase-request', [\App\Http\Controllers\PurchaseRequestController::class, 'store'])->name('purchase-request.store');
    Route::get('/purchase-request/{id}', [\App\Http\Controllers\PurchaseRequestController::class, 'show'])->name('purchase-request.show');
    Route::get('/api/items/{code}/stock', [\App\Http\Controllers\PurchaseRequestController::class, 'getItemStock'])->name('api.item-stock');
    Route::get('/api/sap/vendors', [\App\Http\Controllers\PurchaseRequestController::class, 'getVendors'])->name('api.vendors');
    Route::get('/api/sap/items', [\App\Http\Controllers\PurchaseRequestController::class, 'getItems'])->name('api.items');
    Route::get('/api/sap/accounts', [\App\Http\Controllers\PurchaseRequestController::class, 'getAccounts'])->name('api.accounts');

    // Purchase Quotations
    Route::get('/purchase-quotation', [\App\Http\Controllers\PurchaseQuotationController::class, 'index'])->name('purchase-quotation.index');
    Route::get('/purchase-quotation/create', [\App\Http\Controllers\PurchaseQuotationController::class, 'create'])->name('purchase-quotation.create');
    Route::post('/purchase-quotation', [\App\Http\Controllers\PurchaseQuotationController::class, 'store'])->name('purchase-quotation.store');
    Route::get('/purchase-quotation/{id}', [\App\Http\Controllers\PurchaseQuotationController::class, 'show'])->name('purchase-quotation.show');
    Route::get('/api/requisitions/vendor-eligible', [\App\Http\Controllers\PurchaseQuotationController::class, 'getRequisitionsByVendor'])->name('api.requisitions.eligible');

    // Approval Engine
    Route::get('/approvals/stages', [\App\Http\Controllers\ApprovalStageController::class, 'index'])->name('approvals.stages.index');
    Route::post('/approvals/stages', [\App\Http\Controllers\ApprovalStageController::class, 'store'])->name('approvals.stages.store');
    Route::put('/approvals/stages/{id}', [\App\Http\Controllers\ApprovalStageController::class, 'update'])->name('approvals.stages.update');
    Route::delete('/approvals/stages/{id}', [\App\Http\Controllers\ApprovalStageController::class, 'destroy'])->name('approvals.stages.destroy');

    Route::get('/approvals/templates', [\App\Http\Controllers\ApprovalTemplateController::class, 'index'])->name('approvals.templates.index');
    Route::get('/approvals/templates/create', [\App\Http\Controllers\ApprovalTemplateController::class, 'create'])->name('approvals.templates.create');
    Route::post('/approvals/templates', [\App\Http\Controllers\ApprovalTemplateController::class, 'store'])->name('approvals.templates.store');
    Route::get('/approvals/templates/{id}/edit', [\App\Http\Controllers\ApprovalTemplateController::class, 'edit'])->name('approvals.templates.edit');
    Route::put('/approvals/templates/{id}', [\App\Http\Controllers\ApprovalTemplateController::class, 'update'])->name('approvals.templates.update');
    Route::delete('/approvals/templates/{id}', [\App\Http\Controllers\ApprovalTemplateController::class, 'destroy'])->name('approvals.templates.destroy');

    Route::get('/approvals/decisions', [\App\Http\Controllers\ApprovalDecisionController::class, 'index'])->name('approvals.decisions.index');
    Route::get('/approvals/decisions/{id}', [\App\Http\Controllers\ApprovalDecisionController::class, 'show'])->name('approvals.decisions.show');
    Route::post('/approvals/decisions/{id}/vote', [\App\Http\Controllers\ApprovalDecisionController::class, 'vote'])->name('approvals.decisions.vote');

    Route::get('/purchase-order', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('purchase-order.index');
    Route::get('/purchase-order/create', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('purchase-order.create');
    Route::post('/purchase-order', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('purchase-order.store');
    Route::get('/purchase-order/{id}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('purchase-order.show');
    Route::post('/purchase-order/{purchaseOrder}/sync', [\App\Http\Controllers\PurchaseOrderController::class, 'sync'])->name('purchase-order.sync');

    Route::get('/scheduler/master-data', [\App\Http\Controllers\SchedulerController::class, 'masterData'])->name('scheduler.master-data');
    Route::post('/scheduler/master-data/sync-all', [\App\Http\Controllers\SchedulerController::class, 'syncAllMasterData'])->name('scheduler.sync-all-master-data');
    Route::get('/scheduler/document', [\App\Http\Controllers\SchedulerController::class, 'document'])->name('scheduler.document');
    Route::post('/scheduler/document/sync-all', [\App\Http\Controllers\SchedulerController::class, 'syncAllDocuments'])->name('scheduler.sync-all-documents');
    Route::post('/scheduler/sync-now', [\App\Http\Controllers\SchedulerController::class, 'syncNow'])->name('scheduler.sync-now');
});
