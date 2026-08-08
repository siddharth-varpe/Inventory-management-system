@extends('layouts.app')

@section('title', 'Procurement Desk - StockManager ERP')

@section('header', 'Order Supplies Desk (Procurement PMS)')
@section('subheader', 'Central procurement command tower for requisition approvals, PO tracking, and live low-stock purchase recommendations.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Order Supplies</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="dashboard" />
    </div>

    <!-- Right Column: Workspace Desk -->
    <div class="col-12 col-lg-9">
        <!-- KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Active Suppliers</div>
                    <div class="fs-4 fw-bold text-body mt-1">{{ number_format($kpis['active_suppliers']) }}</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Pending PR Approvals</div>
                    <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($kpis['pending_requisitions']) }}</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Active Purchase Orders</div>
                    <div class="fs-4 fw-bold text-primary mt-1">{{ number_format($kpis['open_purchase_orders']) }}</div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Completed Orders</div>
                    <div class="fs-4 fw-bold text-success mt-1">{{ number_format($kpis['completed_orders']) }}</div>
                </div>
            </div>
        </div>

        <!-- Live Low-Stock Procurement Recommendations Widget (SSOT Product Master) -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold text-body mb-0">Live Low-Stock Purchase Recommendations</h5>
                    <span class="text-muted small">Automatically generated when Master Product available stock $\le$ reorder level</span>
                </div>
                <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill fw-bold">
                    {{ $purchaseRecommendations->count() }} Products Require Replenishment
                </span>
            </div>

            @if($purchaseRecommendations->isEmpty())
                <x-empty-state title="Stock Levels Optimal" message="All master products are currently above their reorder thresholds." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small">
                                <th>SKU / Product</th>
                                <th>Category</th>
                                <th>Current Stock</th>
                                <th>Reorder Level</th>
                                <th>Recommended Qty</th>
                                <th>Quick Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseRecommendations as $prod)
                                @php
                                    $recQty = max(1, ($prod->max_stock ?? 100) - $prod->available_stock);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-body">{{ $prod->name }}</div>
                                        <code class="small text-muted">{{ $prod->sku }}</code>
                                    </td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $prod->category->name ?? 'General' }}</span></td>
                                    <td><span class="badge bg-danger-subtle text-danger fw-bold fs-6">{{ $prod->available_stock }} {{ $prod->unit->short_name ?? 'Units' }}</span></td>
                                    <td class="fw-semibold">{{ $prod->reorder_level }}</td>
                                    <td class="fw-bold text-primary">{{ $recQty }} {{ $prod->unit->short_name ?? 'Units' }}</td>
                                    <td>
                                        <form action="{{ route('procurement.requisitions.store') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $prod->id }}">
                                            <input type="hidden" name="quantity" value="{{ $recQty }}">
                                            <input type="hidden" name="priority" value="urgent">
                                            <input type="hidden" name="purpose" value="Auto-generated low stock replenishment for {{ $prod->sku }}">
                                            <button type="submit" class="btn btn-sm btn-primary rounded-3 fw-bold">+ Generate PR</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="row g-4">
            <!-- Recent Requisitions -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-body mb-0">Pending Requisitions</h6>
                        <a href="{{ route('procurement.requisitions.index') }}" class="btn btn-sm btn-outline-primary rounded-3">View All &rarr;</a>
                    </div>
                    @if($recentRequisitions->isEmpty())
                        <x-empty-state title="No Pending Requisitions" message="All internal purchasing requisitions have been processed." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>PR No</th>
                                        <th>Product & SKU</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentRequisitions as $pr)
                                        @php
                                            $item = $pr->items->first();
                                            $prod = $item?->product;
                                        @endphp
                                        <tr>
                                            <td><code>{{ $pr->requisition_number }}</code></td>
                                            <td>
                                                <div class="fw-semibold text-body small">{{ $prod->name ?? 'Product Item' }}</div>
                                                <code class="small text-muted" style="font-size: 0.7rem;">{{ $prod->sku ?? 'N/A' }}</code>
                                            </td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $pr->status }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-body mb-0">Active Purchase Orders</h6>
                        <a href="{{ route('procurement.purchase-orders.index') }}" class="btn btn-sm btn-outline-primary rounded-3">View All &rarr;</a>
                    </div>
                    @if($recentOrders->isEmpty())
                        <x-empty-state title="No Active Orders" message="Issued purchase orders will appear here." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>PO No</th>
                                        <th>Product & SKU</th>
                                        <th>Supplier</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $po)
                                        @php
                                            $item = $po->items->first();
                                            $prod = $item?->product;
                                        @endphp
                                        <tr>
                                            <td><code>{{ $po->po_number }}</code></td>
                                            <td>
                                                <div class="fw-semibold text-body small">{{ $prod->name ?? 'Product Item' }}</div>
                                                <code class="small text-muted" style="font-size: 0.7rem;">{{ $prod->sku ?? 'N/A' }}</code>
                                            </td>
                                            <td class="fw-semibold small">{{ $po->supplier->name ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
