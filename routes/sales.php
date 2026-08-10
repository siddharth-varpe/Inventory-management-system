<?php

use App\Modules\Sales\Controllers\SalesDashboardController;
use App\Modules\Sales\Controllers\CustomerController;
use App\Modules\Sales\Controllers\CustomerGroupController;
use App\Modules\Sales\Controllers\CustomerCategoryController;
use App\Modules\Sales\Controllers\TerritoryController;
use App\Modules\Sales\Controllers\QuotationController;
use App\Modules\Sales\Controllers\SalesOrderController;
use App\Modules\Sales\Controllers\CrmLeadController;
use App\Modules\Sales\Controllers\CrmActivityController;
use App\Modules\Sales\Controllers\SalesReportController;
use App\Modules\Sales\Controllers\CceCommunicationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sales & CRM Portal Routes - StockManager Enterprise ERP
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('sales')->name('sales.')->group(function () {

    // Sales CRM Dashboard
    Route::get('/', [SalesDashboardController::class, 'index'])->name('dashboard');

    // Commercial Reports Center
    Route::get('reports', [SalesReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [SalesReportController::class, 'export'])->name('reports.export');

    // Customer Communication Engine (CCE Phase 3) Endpoints
    Route::post('cce/launch', [CceCommunicationController::class, 'launch'])->name('cce.launch');
    Route::post('cce/dispatch', [CceCommunicationController::class, 'dispatch'])->name('cce.dispatch');
    Route::post('cce/{record}/retry', [CceCommunicationController::class, 'retry'])->name('cce.retry');
    Route::get('cce/{record}/preview', [CceCommunicationController::class, 'preview'])->name('cce.preview');
    Route::post('cce/{record}/track', [CceCommunicationController::class, 'track'])->name('cce.track');
    Route::get('cce/reminders', [CceCommunicationController::class, 'reminders'])->name('cce.reminders');

    // CRM Lead Management & Opportunity Pipeline
    Route::get('leads/pipeline', [CrmLeadController::class, 'pipeline'])->name('leads.pipeline');
    Route::get('leads', [CrmLeadController::class, 'index'])->name('leads.index');
    Route::post('leads', [CrmLeadController::class, 'store'])->name('leads.store');
    Route::get('leads/{lead}', [CrmLeadController::class, 'show'])->name('leads.show');
    Route::post('leads/{lead}/status', [CrmLeadController::class, 'updateStatus'])->name('leads.status');

    // CRM Activity, Followup & Meeting Loggers
    Route::post('activities', [CrmActivityController::class, 'storeActivity'])->name('activities.store');
    Route::post('followups', [CrmActivityController::class, 'storeFollowup'])->name('followups.store');
    Route::post('meetings', [CrmActivityController::class, 'storeMeeting'])->name('meetings.store');

    // Live 3-Panel Sales Workspace
    Route::get('workspace', [QuotationController::class, 'workspace'])->name('workspace');

    // Quotations Engine
    Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
    Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
    Route::get('quotations/search-products', [QuotationController::class, 'searchProducts'])->name('quotations.search-products');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
    Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
    Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
    Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
    Route::get('quotations/{quotation}/live-status', [QuotationController::class, 'liveStatus'])->name('quotations.live-status');
    Route::post('quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
    Route::post('quotations/{quotation}/reject', [QuotationController::class, 'reject'])->name('quotations.reject');
    Route::post('quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');
    Route::post('quotations/{quotation}/convert', [SalesOrderController::class, 'createFromQuotation'])->name('quotations.convert');

    // Sales Orders & Reservation Engine
    Route::get('orders', [SalesOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
    Route::get('orders/{order}/live-status', [SalesOrderController::class, 'liveStatus'])->name('orders.live-status');
    Route::post('orders/{order}/approve', [SalesOrderController::class, 'approve'])->name('orders.approve');
    Route::post('orders/{order}/cancel', [SalesOrderController::class, 'cancel'])->name('orders.cancel');

    // Customer Master CRUD & Sub-actions
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/addresses', [CustomerController::class, 'addAddress'])->name('customers.addresses.store');
    Route::post('customers/{customer}/contacts', [CustomerController::class, 'addContact'])->name('customers.contacts.store');
    Route::post('customers/{customer}/notes', [CustomerController::class, 'addNote'])->name('customers.notes.store');
    Route::post('customers/{customer}/documents', [CustomerController::class, 'uploadDocument'])->name('customers.documents.store');

    // Customer Groups
    Route::get('groups', [CustomerGroupController::class, 'index'])->name('groups.index');
    Route::post('groups', [CustomerGroupController::class, 'store'])->name('groups.store');
    Route::delete('groups/{group}', [CustomerGroupController::class, 'destroy'])->name('groups.destroy');

    // Customer Categories
    Route::get('categories', [CustomerCategoryController::class, 'index'])->name('categories.index');
    Route::post('categories', [CustomerCategoryController::class, 'store'])->name('categories.store');
    Route::delete('categories/{category}', [CustomerCategoryController::class, 'destroy'])->name('categories.destroy');

    // Territories
    Route::get('territories', [TerritoryController::class, 'index'])->name('territories.index');
    Route::post('territories', [TerritoryController::class, 'store'])->name('territories.store');
    Route::delete('territories/{territory}', [TerritoryController::class, 'destroy'])->name('territories.destroy');
});
