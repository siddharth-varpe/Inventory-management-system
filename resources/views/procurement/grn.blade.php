@extends('layouts.app')

@section('title', 'Goods Receipt Notes - Order Supplies PMS')

@section('header', 'Goods Receipt Note (GRN) Receiving Workspace')
@section('subheader', 'Verify incoming deliveries against active PO contracts, record partial or full receipts, and trigger automated warehouse put-away tasks.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Order Supplies</a></li>
    <li class="breadcrumb-item active" aria-current="page">GRN Receiving</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="grn" />
    </div>

    <!-- Right Column: GRN Workspace -->
    <div class="col-12 col-lg-9">
        <!-- Navigation Tabs -->
        <ul class="nav nav-pills mb-4 gap-2" id="grnTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-3 px-4 fw-bold" id="pending-tab" data-bs-toggle="pill" data-bs-target="#pendingQueue" type="button" role="tab">
                    Pending Delivery Queue <span class="badge bg-danger ms-2">{{ $orders->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-3 px-4 fw-bold" id="history-tab" data-bs-toggle="pill" data-bs-target="#grnHistory" type="button" role="tab">
                    Completed GRN Logs <span class="badge bg-secondary ms-2">{{ $grnHistory->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="grnTabContent">
            <!-- Pending Delivery Queue -->
            <div class="tab-pane fade show active" id="pendingQueue" role="tabpanel">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-body mb-0">Active Purchase Orders Awaiting Receipt</h5>
                            <span class="text-muted small">Only POs with un-received stock balances appear in this queue</span>
                        </div>
                    </div>

                    @if($orders->isEmpty())
                        <x-empty-state title="Pending Queue Clear" message="All active purchase orders have been fully received and moved to historical logs." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>PO Reference</th>
                                        <th>Product</th>
                                        <th>Supplier</th>
                                        <th>Ordered Qty</th>
                                        <th>Received Qty</th>
                                        <th>Remaining Qty</th>
                                        <th>Status</th>
                                        <th>Quick Receive</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $po)
                                        @php
                                            $totOrdered = $po->items->sum('quantity_ordered');
                                            $totReceived = $po->items->sum('quantity_received');
                                            $totRemaining = max(0, $totOrdered - $totReceived);

                                            $firstItem = $po->items->first();
                                            $firstProduct = $firstItem?->product;
                                            $extraItemCount = max(0, $po->items->count() - 1);
                                        @endphp
                                        <tr>
                                            <td>
                                                <code class="fw-bold text-primary">{{ $po->po_number }}</code>
                                                <div class="small text-muted">₹{{ number_format((float)$po->total_amount, 2) }}</div>
                                            </td>
                                            <!-- Product Column (Clickable to view line items modal) -->
                                            <td style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#poProductModal-{{ $po->id }}" title="Click to inspect all PO line items">
                                                <div class="fw-bold text-body hover-primary">
                                                    {{ $firstProduct->name ?? 'Product Item' }}
                                                </div>
                                                @if($firstProduct && $firstProduct->sku)
                                                    <code class="small text-muted d-block" style="font-size: 0.75rem;">{{ $firstProduct->sku }}</code>
                                                @endif
                                                @if($extraItemCount > 0)
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0 mt-1" style="font-size: 0.7rem;">
                                                        +{{ $extraItemCount }} more {{ Str::plural('item', $extraItemCount) }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-body">{{ $po->supplier->name ?? 'N/A' }}</td>
                                            <td class="fw-semibold">{{ number_format($totOrdered) }}</td>
                                            <td class="fw-semibold text-success">{{ number_format($totReceived) }}</td>
                                            <td class="fw-bold text-danger fs-6">{{ number_format($totRemaining) }}</td>
                                            <td>
                                                <span class="badge {{ $po->status === 'partial_received' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-info-subtle text-info' }}">
                                                    {{ strtoupper(str_replace('_', ' ', $po->status)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <form action="{{ route('procurement.grn.store') }}" method="POST" class="d-flex align-items-center gap-2">
                                                    @csrf
                                                    <input type="hidden" name="po_id" value="{{ $po->id }}">
                                                    <input type="number" name="received_qty" class="form-control form-control-sm rounded-3" style="width: 80px;" value="{{ $totRemaining }}" min="1" max="{{ $totRemaining }}" title="Quantity to receive this batch">
                                                    <button type="submit" class="btn btn-sm btn-success rounded-3 fw-bold text-nowrap">
                                                        Log GRN &rarr;
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Completed GRN History -->
            <div class="tab-pane fade" id="grnHistory" role="tabpanel">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-body mb-0">Historical Goods Receipt Notes (GRN)</h5>
                            <span class="text-muted small">Permanent historical receipt audit logs</span>
                        </div>
                    </div>

                    @if($grnHistory->isEmpty())
                        <x-empty-state title="No GRN Records" message="Completed goods receipt notes will appear here." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>GRN Number</th>
                                        <th>PO Reference</th>
                                        <th>Supplier</th>
                                        <th>Challan No</th>
                                        <th>Received By</th>
                                        <th>Received Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($grnHistory as $grn)
                                        <tr>
                                            <td><code class="fw-bold text-success">{{ $grn->grn_number }}</code></td>
                                            <td><code>{{ $grn->purchaseOrder->po_number ?? 'PO Ref' }}</code></td>
                                            <td class="fw-semibold">{{ $grn->supplier->name ?? 'N/A' }}</td>
                                            <td><span class="badge bg-secondary-subtle text-secondary">{{ $grn->delivery_challan_no }}</span></td>
                                            <td class="small">{{ $grn->receivedBy->name ?? 'System Admin' }}</td>
                                            <td class="small text-muted">{{ $grn->created_at->format('M d, Y H:i') }}</td>
                                            <td><span class="badge bg-success-subtle text-success">COMPLETED</span></td>
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

<!-- Modals for PO Line Item Inspection -->
@foreach($orders as $po)
<div class="modal fade" id="poProductModal-{{ $po->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-translucent">
            <div class="modal-header border-bottom-0">
                <div>
                    <h5 class="modal-title fw-bold">PO Items Breakdown &mdash; {{ $po->po_number }}</h5>
                    <span class="text-muted small">Supplier: <strong>{{ $po->supplier->name ?? 'N/A' }}</strong></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-0">
                <div class="table-responsive mb-3">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light small text-muted">
                            <tr>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Ordered Qty</th>
                                <th>Received Qty</th>
                                <th>Remaining Qty</th>
                                <th>Unit Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($po->items as $item)
                                @php
                                    $rem = max(0, $item->quantity_ordered - $item->quantity_received);
                                @endphp
                                <tr>
                                    <td class="fw-bold text-body">{{ $item->product->name ?? 'Product Item' }}</td>
                                    <td><code>{{ $item->product->sku ?? 'N/A' }}</code></td>
                                    <td class="fw-semibold">{{ number_format($item->quantity_ordered) }}</td>
                                    <td class="fw-semibold text-success">{{ number_format($item->quantity_received) }}</td>
                                    <td class="fw-bold text-danger">{{ number_format($rem) }}</td>
                                    <td>₹{{ number_format((float)$item->unit_cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
