@extends('layouts.app')

@section('title', 'Inventory Reports & Analytics - StockManager ERP')

@section('header', 'Inventory Reports & Analytics')
@section('subheader', 'Comprehensive valuation models, stock ledgers, movement velocity, and loss analysis.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Inventory Reports</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Reports Workspace -->
    <div class="col-12 col-lg-9">
        <!-- Top Metrics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Total Cost Valuation</div>
                    <div class="fs-4 fw-bold text-body mt-1">₹{{ number_format((float)$metrics['total_inventory_value'], 2) }}</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Total Selling Valuation</div>
                    <div class="fs-4 fw-bold text-success mt-1">₹{{ number_format((float)$metrics['total_selling_value'], 2) }}</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Low Stock Products</div>
                    <div class="fs-4 fw-bold text-warning-emphasis mt-1">{{ $metrics['low_stock_count'] }} SKUs</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Dead Stock Items</div>
                    <div class="fs-4 fw-bold text-danger mt-1">{{ $metrics['dead_stock_count'] }} SKUs</div>
                </div>
            </div>
        </div>

        <!-- 8 Reports Navigation & View -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <ul class="nav nav-pills gap-1" id="reportNav">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'valuation' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'valuation']) }}">1. Valuation</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'ledger' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'ledger']) }}">2. Stock Ledger</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'low_stock' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'low_stock']) }}">3. Low Stock</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'dead_stock' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'dead_stock']) }}">4. Dead Stock</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'fast_moving' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'fast_moving']) }}">5. Fast Moving</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'slow_moving' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'slow_moving']) }}">6. Slow Moving</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'adjustments' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'adjustments']) }}">7. Adjustments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeReport === 'expiry' ? 'active' : '' }} rounded-3 small fw-bold" href="{{ route('stock.reports.index', ['tab' => 'expiry']) }}">8. Expiry Schedule</a>
                    </li>
                </ul>

                <a href="{{ route('stock.reports.export', ['type' => $activeReport]) }}" class="btn btn-outline-success btn-sm rounded-3 fw-bold d-flex align-items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>
                    <span>Export CSV</span>
                </a>
            </div>

            <!-- Live Filter Search Bar -->
            <div class="mb-4">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                    </span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="reportTableSearchInput" placeholder="Filter report data by Product, SKU, Ref, Category, Type..." onkeyup="filterReportTable(this.value)">
                </div>
            </div>

            <!-- Report Content Panels -->
            @if($activeReport === 'valuation')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="reportDataTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Physical Stock</th>
                                <th>Unit Cost (WAC)</th>
                                <th>Total Cost Valuation</th>
                                <th>Selling Price</th>
                                <th>Potential Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData['products'] as $p)
                            <tr>
                                <td><code>{{ $p->sku }}</code></td>
                                <td class="fw-bold text-body">{{ $p->name }}</td>
                                <td>{{ $p->category->name ?? 'Uncategorized' }}</td>
                                <td class="fw-bold text-center">{{ $p->physical_stock }}</td>
                                <td>₹{{ number_format((float)$p->cost_price, 2) }}</td>
                                <td class="fw-bold">₹{{ number_format($p->physical_stock * $p->cost_price, 2) }}</td>
                                <td class="text-success">₹{{ number_format((float)$p->selling_price, 2) }}</td>
                                <td class="fw-semibold text-primary">₹{{ number_format(($p->selling_price - $p->cost_price) * $p->physical_stock, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($activeReport === 'ledger')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="reportDataTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>Date</th>
                                <th>Ref No</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Transaction Type</th>
                                <th>Qty</th>
                                <th>Unit Cost</th>
                                <th>Total Amount</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $row)
                            <tr>
                                <td class="text-muted small">{{ $row['date']->format('Y-m-d H:i') }}</td>
                                <td><code>{{ $row['reference_no'] }}</code></td>
                                <td class="fw-bold text-body">{{ $row['product_name'] }}</td>
                                <td><code>{{ $row['sku'] }}</code></td>
                                <td><span class="badge bg-secondary-subtle text-secondary">{{ $row['type'] }}</span></td>
                                <td class="fw-bold {{ str_contains($row['quantity'], '+') ? 'text-success' : 'text-danger' }}">{{ $row['quantity'] }}</td>
                                <td>₹{{ number_format((float)$row['unit_cost'], 2) }}</td>
                                <td class="fw-bold">₹{{ number_format((float)$row['total_amount'], 2) }}</td>
                                <td class="text-muted small">{{ $row['user'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @elseif($activeReport === 'low_stock')
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="reportDataTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>SKU</th>
                                <th>Product Name</th>
                                <th>Category</th>
                                <th>Warehouse Location</th>
                                <th>Current Stock</th>
                                <th>Min Stock</th>
                                <th>Reorder Level</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $p)
                            <tr>
                                <td><code>{{ $p->sku }}</code></td>
                                <td class="fw-bold text-body">{{ $p->name }}</td>
                                <td>{{ $p->category->name ?? 'Uncategorized' }}</td>
                                <td>{{ $p->warehouse_location ?? 'Main' }}</td>
                                <td class="fw-bold text-danger">{{ $p->physical_stock }}</td>
                                <td>{{ $p->min_stock }}</td>
                                <td>{{ $p->reorder_level }}</td>
                                <td><span class="badge bg-warning-subtle text-warning-emphasis">Low Stock</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="reportDataTable">
                        <thead>
                            <tr class="text-muted small">
                                <th>Product / Reference</th>
                                <th>Category / Type</th>
                                <th>Value / Qty</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reportData as $item)
                            <tr>
                                <td class="fw-bold text-body">{{ $item->name ?? $item->reference_no ?? 'N/A' }}</td>
                                <td>{{ $item->category->name ?? $item->type ?? 'N/A' }}</td>
                                <td class="fw-bold">{{ $item->physical_stock ?? $item->quantity ?? 0 }}</td>
                                <td><span class="badge bg-primary-subtle text-primary">Active</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function filterReportTable(query) {
    const term = query.toLowerCase().trim();
    const table = document.getElementById('reportDataTable');
    if (!table) return;
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(term)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}
</script>
@endsection
