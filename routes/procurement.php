<?php

declare(strict_types=1);

use App\Modules\Procurement\Controllers\GrnController;
use App\Modules\Procurement\Controllers\ProcurementDashboardController;
use App\Modules\Procurement\Controllers\ProcurementReportController;
use App\Modules\Procurement\Controllers\PurchaseOrderController;
use App\Modules\Procurement\Controllers\RequisitionController;
use App\Modules\Procurement\Controllers\SupplierController;
use App\Modules\Procurement\Controllers\VendorPerformanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Order Supplies Portal (Enterprise Procurement Management System) Routes
|--------------------------------------------------------------------------
|
| Route Prefix: /procurement
| Name Prefix: procurement.
|
*/

Route::middleware(['auth', 'verified'])->prefix('procurement')->name('procurement.')->group(function () {
    // 🏠 Procurement Workspace Desk
    Route::get('/', [ProcurementDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [ProcurementDashboardController::class, 'index']);

    // 🏢 Supplier Management Workspace
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');

    // 📝 Purchase Requisitions Workspace
    Route::get('/requisitions', [RequisitionController::class, 'index'])->name('requisitions.index');
    Route::post('/requisitions', [RequisitionController::class, 'store'])->name('requisitions.store');
    Route::post('/requisitions/{id}/approve', [RequisitionController::class, 'approve'])->name('requisitions.approve');
    Route::post('/requisitions/{id}/reject', [RequisitionController::class, 'reject'])->name('requisitions.reject');

    // 📜 Purchase Orders Workspace
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store');
    Route::post('/purchase-orders/{id}/dispatch', [PurchaseOrderController::class, 'dispatchShipment'])->name('purchase-orders.dispatch');
    Route::post('/purchase-orders/{id}/arrive', [PurchaseOrderController::class, 'markArrived'])->name('purchase-orders.arrive');

    // 📦 Goods Receipt Notes (GRN) Workspace
    Route::get('/grn', [GrnController::class, 'index'])->name('grn.index');
    Route::post('/grn', [GrnController::class, 'store'])->name('grn.store');

    // ⭐ Vendor Performance Workspace
    Route::get('/vendor-performance', [VendorPerformanceController::class, 'index'])->name('vendor-performance.index');

    // 📈 Operational Procurement Reports Workspace
    Route::get('/reports', [ProcurementReportController::class, 'index'])->name('reports.index');
});
