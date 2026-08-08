<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BarcodeCenterController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpeningStockController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockBatchSerialController;
use App\Http\Controllers\StockExpiryController;
use App\Http\Controllers\StockImportExportController;
use App\Http\Controllers\StockReceiveController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\StockSettingsController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\UnitController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - StockManager Enterprise ERP
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Authenticated Enterprise Application Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // Main Enterprise Landing Dashboard (Central Control Tower)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Organization Infrastructure
    Route::resource('companies', CompanyController::class)->except(['create', 'show', 'edit']);
    Route::resource('branches', BranchController::class)->except(['create', 'show', 'edit']);
    Route::resource('departments', DepartmentController::class)->except(['create', 'show', 'edit']);

    // Master Data Configuration (Admin Control Tower)
    Route::get('/categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
    Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])->name('categories.restore');
    Route::resource('categories', CategoryController::class)->except(['create', 'show', 'edit']);

    Route::post('/brands/{id}/restore', [BrandController::class, 'restore'])->name('brands.restore');
    Route::resource('brands', BrandController::class)->except(['create', 'show', 'edit']);

    Route::resource('units', UnitController::class)->except(['create', 'show', 'edit']);
    Route::resource('taxes', TaxController::class)->except(['create', 'show', 'edit']);
    Route::resource('attributes', ProductAttributeController::class)->except(['create', 'show', 'edit']);

    // =========================================================================
    // Manage Stock Operational Portal (Single Entry Product Catalog Workspace)
    // =========================================================================
    Route::get('/stock', [ProductController::class, 'index'])->name('stock.dashboard');
    Route::get('/stock/catalog', [ProductController::class, 'index'])->name('stock.catalog');
    
    // Product Management Actions
    Route::post('/stock/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
    Route::post('/stock/products/{product}/archive', [ProductController::class, 'archive'])->name('products.archive');
    Route::post('/stock/products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');

    // Single Product Catalog CRUD
    Route::resource('/stock/products', ProductController::class);

    // Operational Receive & Opening Stock
    Route::get('/stock/receive', [StockReceiveController::class, 'index'])->name('stock.receive.index');
    Route::post('/stock/receive', [StockReceiveController::class, 'store'])->name('stock.receive.store');

    Route::get('/stock/opening-stock', [OpeningStockController::class, 'index'])->name('stock.opening-stock.index');
    Route::post('/stock/opening-stock', [OpeningStockController::class, 'store'])->name('stock.opening-stock.store');
    Route::post('/stock/opening-stock/bulk', [OpeningStockController::class, 'bulkUpload'])->name('stock.opening-stock.bulk');

    // Stock Adjustments & High-Value Approvals
    Route::get('/stock/adjustments', [StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
    Route::post('/stock/adjustments', [StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');
    Route::post('/stock/adjustments/{id}/approve', [StockAdjustmentController::class, 'approve'])->name('stock.adjustments.approve');
    Route::post('/stock/adjustments/{id}/reject', [StockAdjustmentController::class, 'reject'])->name('stock.adjustments.reject');

    // Batches & Expiry Management
    Route::get('/stock/batches', [StockBatchSerialController::class, 'indexBatches'])->name('stock.batches.index');
    Route::get('/stock/expiry', [StockExpiryController::class, 'index'])->name('stock.expiry.index');
    Route::post('/stock/expiry/action', [StockExpiryController::class, 'processAction'])->name('stock.expiry.action');

    // Barcode Center & Label Print
    Route::get('/stock/barcodes', [BarcodeCenterController::class, 'index'])->name('stock.barcodes.index');
    Route::post('/stock/barcodes/print', [BarcodeCenterController::class, 'printLabels'])->name('stock.barcodes.print');

    // Import / Export
    Route::get('/stock/import-export', [StockImportExportController::class, 'index'])->name('stock.import-export.index');
    Route::get('/stock/export', [StockImportExportController::class, 'export'])->name('stock.export');
    Route::post('/stock/import', [StockImportExportController::class, 'import'])->name('stock.import');

    // Reports (Role-Based)
    Route::get('/stock/reports', [StockReportController::class, 'index'])->name('stock.reports.index');
    Route::get('/stock/reports/export', [StockReportController::class, 'export'])->name('stock.reports.export');

    // Stock Portal Settings
    Route::get('/stock/settings', [StockSettingsController::class, 'index'])->name('stock.settings.index');
    Route::put('/stock/settings', [StockSettingsController::class, 'update'])->name('stock.settings.update');

    // Admin Control Tower & Master Configuration
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Activity & Audit Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    // Notifications Center
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

// Include Authentication Routes
require __DIR__.'/auth.php';

// Include Admin Routes
require __DIR__.'/admin.php';

// Include Independent Organize Stock Portal Routes
require __DIR__.'/organize-stock.php';

// Include Order Supplies Portal (Procurement) Routes
require __DIR__.'/procurement.php';

// Include Sales & CRM Portal Routes
require __DIR__.'/sales.php';

// Include Transport Department Portal Routes
require __DIR__.'/transport.php';

// Include Driver Terminal Portal Routes
require __DIR__.'/driver.php';
