@extends('layouts.app')

@section('title', 'Manage Stock Workspace - StockManager ERP')

@section('header', 'Manage Stock Workspace')
@section('subheader', 'Centralized Operational Control Station for Catalog, Stock Movements, Expiry & Adjustments.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Manage Stock Workspace</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Workspace Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Dynamic Workspace Content Area -->
    <div class="col-12 col-lg-9">
        <!-- KPI Cards Grid -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-4">
                <x-kpi-card title="Total Products" value="{{ $metrics['total_products'] }}" badge="Catalog" subtitle="Active SKUs in Master DB" />
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <x-kpi-card title="Active Products" value="{{ $metrics['active_products'] }}" badge="Active" badgeBg="bg-success-subtle text-success" subtitle="Ready for transaction" />
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <x-kpi-card title="Low Stock Alerts" value="{{ $metrics['low_stock'] }}" badge="Low Stock" badgeBg="bg-warning-subtle text-warning-emphasis" subtitle="Below reorder threshold" />
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <x-kpi-card title="Out of Stock" value="{{ $metrics['out_of_stock'] }}" badge="Critical" badgeBg="bg-danger-subtle text-danger" subtitle="Zero physical balance" />
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <x-kpi-card title="Expiring Soon" value="{{ $metrics['expiring_soon'] }}" badge="30 Days" badgeBg="bg-danger-subtle text-danger" subtitle="Action required" />
            </div>
            <div class="col-12 col-sm-6 col-xl-4">
                <x-kpi-card title="Inventory Value" value="₹{{ number_format($metrics['inventory_value'], 2) }}" badge="Valuation" badgeBg="bg-info-subtle text-info" subtitle="Weighted avg valuation" />
            </div>
        </div>

        <!-- Feeds & Actions Section -->
        <div class="row g-4 mb-4">
            <!-- Recent Activity Feed (Stock Receipts) -->
            <div class="col-12 col-xl-7">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-body mb-0">Recent Stock Receipts</h5>
                        <a href="{{ route('stock.receive.index') }}" class="btn btn-link btn-sm text-decoration-none">View All</a>
                    </div>
                    @if($recentReceipts->isEmpty())
                        <x-empty-state title="No Stock Receipts" message="Stock receipts from suppliers will appear here." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Ref No</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Total Cost</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentReceipts as $receipt)
                                    <tr>
                                        <td><code>{{ $receipt->reference_no }}</code></td>
                                        <td class="fw-semibold text-body">{{ $receipt->product->name ?? 'Deleted Product' }}</td>
                                        <td class="fw-bold text-success">+{{ $receipt->quantity }}</td>
                                        <td class="fw-semibold">₹{{ number_format((float)$receipt->total_cost, 2) }}</td>
                                        <td class="text-muted small">{{ $receipt->created_at->format('M d, H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- Recent Stock Adjustments -->
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-body mb-0">Recent Stock Adjustments</h5>
                        <a href="{{ route('stock.adjustments.index') }}" class="btn btn-link btn-sm text-decoration-none">View All</a>
                    </div>
                    @if($recentAdjustments->isEmpty())
                        <x-empty-state title="No Stock Adjustments" message="Inventory loss and adjustment logs will appear here." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Ref No</th>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAdjustments as $adj)
                                    <tr>
                                        <td><code>{{ $adj->reference_no }}</code></td>
                                        <td class="fw-semibold text-body">{{ $adj->product->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $adj->type }}</span></td>
                                        <td class="fw-bold {{ $adj->quantity > 0 ? 'text-success' : 'text-danger' }}">{{ $adj->quantity > 0 ? '+'.$adj->quantity : $adj->quantity }}</td>
                                        <td>
                                            @if($adj->status === 'approved')
                                                <span class="badge bg-success-subtle text-success">Approved</span>
                                            @elseif($adj->status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning-emphasis">Pending</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions & Alerts Sidebar -->
            <div class="col-12 col-xl-5">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                    <h5 class="fw-bold text-body mb-3">Quick Inventory Actions</h5>
                    <div class="d-grid gap-2">
                        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2"/></svg>
                            <span>Add New Product Master</span>
                        </a>
                        <a href="{{ route('stock.receive.index') }}" class="btn btn-outline-success btn-sm rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-down" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/></svg>
                            <span>Receive Supplier Stock</span>
                        </a>
                        <a href="{{ route('stock.opening-stock.index') }}" class="btn btn-outline-info btn-sm rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-archive-fill" viewBox="0 0 16 16"><path d="M12.643 15C13.979 15 15 13.845 15 12.5V5H1v7.5C1 13.845 2.021 15 3.357 15zM5.5 7h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1M.8 1a.8.8 0 0 0-.8.8V3a.8.8 0 0 0 .8.8h14.4A.8.8 0 0 0 16 3V1.8a.8.8 0 0 0-.8-.8z"/></svg>
                            <span>Opening Stock Setup</span>
                        </a>
                        <a href="{{ route('stock.adjustments.index') }}" class="btn btn-outline-warning btn-sm rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8zM11.5 12a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1z"/></svg>
                            <span>Adjust Stock Level</span>
                        </a>
                        <a href="{{ route('stock.barcodes.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-qr-code-scan" viewBox="0 0 16 16"><path d="M0.5 0.5A.5.5 0 0 0 0 1v3.5a.5.5 0 0 0 1 0V1h3.5a.5.5 0 0 0 0-1zm15 0A.5.5 0 0 0 15 1v3.5a.5.5 0 0 0 1 0V1h-3.5a.5.5 0 0 0 0-1zM0.5 15.5A.5.5 0 0 1 0 15v-3.5a.5.5 0 0 1 1 0V15h3.5a.5.5 0 0 1 0 1zm15 0A.5.5 0 0 1 15 15v-3.5a.5.5 0 0 1 1 0V15h-3.5a.5.5 0 0 1 0 1z"/></svg>
                            <span>Barcode Center</span>
                        </a>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                    <h5 class="fw-bold text-body mb-3">Critical Reorder Alerts</h5>
                    @if($lowStockProducts->isEmpty())
                        <div class="text-center text-muted small py-3">No low stock alerts. All inventory levels normal.</div>
                    @else
                        <div class="list-group list-group-flush border-0">
                            @foreach($lowStockProducts->take(4) as $p)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 border-0 px-0">
                                <div>
                                    <div class="fw-bold text-body small">{{ $p->name }}</div>
                                    <code class="small text-muted">{{ $p->sku }}</code>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-danger">{{ $p->physical_stock }} {{ $p->unit->short_name ?? 'units' }}</span>
                                    <div class="text-muted small" style="font-size: 0.7rem;">Min: {{ $p->min_stock }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Category Breakdown Summary -->
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                    <h5 class="fw-bold text-body mb-3">Category Breakdown</h5>
                    @if($categoryBreakdown->isEmpty())
                        <div class="text-center text-muted small py-2">No categories defined yet.</div>
                    @else
                        <ul class="list-group list-group-flush border-0">
                            @foreach($categoryBreakdown->take(5) as $cat)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2 border-0">
                                <span class="fw-semibold text-body small">{{ $cat->name }}</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill">{{ $cat->products_count }} SKUs</span>
                            </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
