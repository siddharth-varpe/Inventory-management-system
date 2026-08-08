<?php

declare(strict_types=1);

use App\Modules\OrganizeStock\Controllers\DispatchController;
use App\Modules\OrganizeStock\Controllers\ExceptionController;
use App\Modules\OrganizeStock\Controllers\LocationController;
use App\Modules\OrganizeStock\Controllers\OrganizeStockDashboardController;
use App\Modules\OrganizeStock\Controllers\FulfillmentStationController;
use App\Modules\OrganizeStock\Controllers\PutAwayController;
use App\Modules\OrganizeStock\Controllers\ReportController;
use App\Modules\OrganizeStock\Controllers\TransferController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Organize Stock Enterprise Portal Routes
|--------------------------------------------------------------------------
|
| Independent application boundary for Warehouse Management System (WMS).
| Route Prefix: /organize-stock
|
*/

Route::middleware(['auth', 'verified'])->prefix('organize-stock')->name('organize.')->group(function () {
    // 🏠 Workspace (Task Desk)
    Route::get('/', [OrganizeStockDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [OrganizeStockDashboardController::class, 'index']);

    // 📦 Pick & Pack Fulfillment Station (Unified Outbound Execution Workspace)
    Route::get('/fulfillment', [FulfillmentStationController::class, 'index'])->name('fulfillment.index');
    Route::post('/fulfillment/{task}/barcode', [FulfillmentStationController::class, 'verifyBarcode'])->name('fulfillment.barcode');
    Route::post('/fulfillment/{task}/exception', [FulfillmentStationController::class, 'reportException'])->name('fulfillment.exception');
    Route::post('/fulfillment/{task}/seal-ready', [FulfillmentStationController::class, 'sealAndMarkReady'])->name('fulfillment.seal-ready');

    // Legacy Endpoint Fallback Handlers (Prevents 404 on any legacy GET/POST picking or dispatch requests)
    Route::match(['get', 'post'], '/picking/{task}/start', function () {
        return redirect()->route('organize.fulfillment.index');
    })->name('picking.start');

    Route::match(['get', 'post'], '/picking/{task}/complete', function () {
        return redirect()->route('organize.fulfillment.index');
    })->name('picking.complete');

    Route::match(['get', 'post'], '/picking/{task}/complete-packing', function () {
        return redirect()->route('organize.fulfillment.index');
    })->name('picking.complete-packing');

    Route::match(['get', 'post'], '/picking/{task}/dispatch', function () {
        return redirect()->route('organize.fulfillment.index');
    })->name('picking.dispatch');

    Route::match(['get', 'post'], '/picking/{any?}', function () {
        return redirect()->route('organize.fulfillment.index');
    })->name('picking.index')->where('any', '.*');

    Route::match(['get', 'post'], '/dispatch/{any?}', function () {
        return redirect()->route('organize.fulfillment.index');
    })->name('dispatch.index')->where('any', '.*');

    // 📥 Put-Away Tasks
    Route::get('/put-away', [PutAwayController::class, 'index'])->name('putaway.index');
    Route::post('/put-away/{id}/assign', [PutAwayController::class, 'assignLocation'])->name('putaway.assign');

    // 🗺️ Warehouse Explorer
    Route::get('/locations', [LocationController::class, 'index'])->name('locations.index');
    Route::post('/locations/warehouse', [LocationController::class, 'storeWarehouse'])->name('locations.store-warehouse');
    Route::post('/locations/bin', [LocationController::class, 'storeBin'])->name('locations.store-bin');

    // 🔄 Transfer Center
    Route::get('/transfers', [TransferController::class, 'index'])->name('transfers.index');
    Route::post('/transfers', [TransferController::class, 'store'])->name('transfers.store');

    // ⚠️ Exception Center
    Route::get('/exceptions', [ExceptionController::class, 'index'])->name('exceptions.index');
    Route::post('/exceptions', [ExceptionController::class, 'store'])->name('exceptions.store');

    // 📊 Operational Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
});
