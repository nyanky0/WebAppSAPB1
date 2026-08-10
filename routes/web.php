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

    Route::get('/config', [ConfigController::class, 'index'])->name('config.index');
    Route::post('/config', [ConfigController::class, 'update'])->name('config.update');
    Route::post('/api/config/fetch-period', [ConfigController::class, 'fetchPeriodIndicator'])->name('api.config.fetch-period');
    Route::post('/api/config/fetch-databases', [ConfigController::class, 'fetchDatabases'])->name('api.config.fetch-databases');
    
    Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    Route::get('/items', [\App\Http\Controllers\ItemController::class, 'index'])->name('items.index');
    Route::post('/items/sync', [\App\Http\Controllers\ItemController::class, 'sync'])->name('items.sync');
    
    Route::get('/purchase-request', [PurchaseRequestController::class, 'index'])->name('purchase-request.index');
    Route::get('/purchase-request/create', [PurchaseRequestController::class, 'create'])->name('purchase-request.create');
    Route::get('/api/vendors', [PurchaseRequestController::class, 'getVendors'])->name('api.vendors');
    Route::get('/api/purchase-request/series', [PurchaseRequestController::class, 'getSeries'])->name('api.purchase-request.series');
});
