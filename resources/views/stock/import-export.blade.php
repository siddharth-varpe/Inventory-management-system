@extends('layouts.app')

@section('title', 'Import & Export Station - StockManager ERP')

@section('header', 'Import & Export Workspace')
@section('subheader', 'Bulk import master catalog data or export inventory valuation, stock ledgers, and product records.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Import & Export</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Import Export Area -->
    <div class="col-12 col-lg-9">
        <div class="row g-4">
            <!-- Catalog CSV Import Card -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="p-2 bg-primary-subtle text-primary rounded-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-arrow-up-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M6.354 9.854a.5.5 0 0 1-.708-.708l2-2a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 8.707V12.5a.5.5 0 0 1-1 0V8.707z"/></svg>
                        </div>
                        <h5 class="fw-bold text-body mb-0">Import Product Master CSV</h5>
                    </div>
                    <p class="text-muted small mb-3">Upload your product catalog CSV. Existing SKUs will be updated, while new SKUs will be registered with auto-categorization.</p>
                    
                    <form action="{{ route('stock.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select CSV / Excel File <span class="text-danger">*</span></label>
                            <input type="file" name="import_file" class="form-control rounded-3" accept=".csv, .txt" required>
                        </div>
                        
                        <div class="p-3 bg-body-tertiary rounded-3 border mb-4">
                            <div class="fw-bold small text-body mb-1">Expected CSV Column Headers:</div>
                            <code class="small text-wrap">name, sku, code, barcode, category, brand, purchase_price, cost_price, selling_price, physical_stock, warehouse</code>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 fw-bold">Process Catalog Import</button>
                    </form>
                </div>
            </div>

            <!-- Catalog CSV Export Card -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="p-2 bg-success-subtle text-success rounded-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-file-earmark-arrow-down-fill" viewBox="0 0 16 16"><path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0M9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1M8 6a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L7.5 10.293V6.5A.5.5 0 0 1 8 6"/></svg>
                        </div>
                        <h5 class="fw-bold text-body mb-0">Export Inventory Data</h5>
                    </div>
                    <p class="text-muted small mb-4">Export complete catalog master data with prices, stock levels, warehouse locations, and categories into CSV format.</p>

                    <div class="d-grid gap-2">
                        <a href="{{ route('stock.export') }}" class="btn btn-success rounded-3 py-2 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                            <span>Download Full Catalog CSV</span>
                        </a>

                        <a href="{{ route('stock.reports.export', ['type' => 'valuation']) }}" class="btn btn-outline-secondary rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <span>Export Inventory Valuation CSV</span>
                        </a>

                        <a href="{{ route('stock.reports.export', ['type' => 'ledger']) }}" class="btn btn-outline-secondary rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <span>Export Stock Ledger CSV</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
