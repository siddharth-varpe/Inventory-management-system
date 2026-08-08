@extends('layouts.app')

@section('title', 'Purchase Orders - Order Supplies PMS')

@section('header', 'Purchase Orders Workspace')
@section('subheader', 'Contractual purchase order issuance, inbound shipment dispatch tracking, and arrival management.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Order Supplies</a></li>
    <li class="breadcrumb-item active" aria-current="page">Purchase Orders</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="purchase-orders" />
    </div>

    <!-- Right Column: PO Workspace -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold text-body mb-1">Purchase Order Register</h5>
                    <p class="text-muted small mb-0">Legal purchase contracts issued to registered vendors</p>
                </div>
                <button type="button" class="btn btn-primary rounded-3 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#createPoModal">
                    + Create Purchase Order
                </button>
            </div>

            @if($orders->isEmpty())
                <x-empty-state title="No Purchase Orders" message="Click '+ Create Purchase Order' to issue contracts to suppliers." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>PO Number</th>
                                <th>Product Name & SKU</th>
                                <th>Supplier</th>
                                <th>Total Amount</th>
                                <th>PO Status</th>
                                <th>Shipment Transit</th>
                                <th>Inbound Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $po)
                                @php
                                    $firstItem = $po->items->first();
                                    $firstProduct = $firstItem?->product;
                                    $extraItemCount = max(0, $po->items->count() - 1);
                                @endphp
                                <tr>
                                    <td>
                                        <code class="fw-bold text-primary">{{ $po->po_number }}</code>
                                        <div class="small text-muted">{{ $po->created_at->format('Y-m-d') }}</div>
                                    </td>
                                    <!-- Product Name & SKU Column -->
                                    <td>
                                        <div class="fw-bold text-body">{{ $firstProduct->name ?? 'Product Item' }}</div>
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
                                    <td class="fw-bold text-primary">₹{{ number_format((float)$po->total_amount, 2) }}</td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($po->status) }}</span></td>
                                    <td>
                                        @if($po->shipment_status === 'pending_dispatch')
                                            <span class="badge bg-secondary-subtle text-secondary">Pending Dispatch</span>
                                        @elseif($po->shipment_status === 'in_transit')
                                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold">In Transit</span>
                                            <div class="small text-muted" style="font-size: 0.75rem;">Eta: {{ $po->expected_delivery_date?->format('M d, Y') ?? '1 Week' }}</div>
                                        @elseif($po->shipment_status === 'arrived')
                                            <span class="badge bg-info-subtle text-info fw-bold">ARRIVED (In GRN Queue)</span>
                                            <div class="small text-muted" style="font-size: 0.75rem;">Arr: {{ $po->actual_arrival_date?->format('M d H:i') }}</div>
                                        @elseif($po->shipment_status === 'completed')
                                            <span class="badge bg-success-subtle text-success fw-bold">Fully Received</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($po->shipment_status === 'pending_dispatch' && !in_array($po->status, ['completed', 'closed', 'cancelled']))
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#dispatchModal-{{ $po->id }}">
                                                Dispatch Shipment &rarr;
                                            </button>
                                        @elseif($po->shipment_status === 'in_transit')
                                            <form action="{{ route('procurement.purchase-orders.arrive', $po->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-info text-white rounded-3 fw-bold">
                                                    Mark Arrived &rarr;
                                                </button>
                                            </form>
                                        @elseif($po->shipment_status === 'arrived')
                                            <a href="{{ route('procurement.grn.index') }}" class="btn btn-sm btn-success rounded-3 fw-bold">
                                                Log GRN Receipt &rarr;
                                            </a>
                                        @else
                                            <span class="text-muted small fst-italic">Completed</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>{{ $orders->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Create PO -->
<div class="modal fade" id="createPoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-translucent">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Issue Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('procurement.purchase-orders.store') }}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Select Vendor / Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" class="form-select rounded-3" required>
                                @foreach($suppliers as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Select Product Item <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select rounded-3" required>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control rounded-3" value="50" min="1" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Agreed Unit Cost (₹) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="unit_cost" class="form-control rounded-3" value="150.00" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 mt-3">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold">Issue Purchase Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dispatch Modals for Pending Dispatch POs -->
@foreach($orders as $po)
    @if($po->shipment_status === 'pending_dispatch')
    <div class="modal fade" id="dispatchModal-{{ $po->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow-sm border-translucent">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold">Dispatch Inbound Shipment &mdash; {{ $po->po_number }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('procurement.purchase-orders.dispatch', $po->id) }}" method="POST">
                    @csrf
                    <div class="modal-body py-0">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Supplier Lead Time (Days)</label>
                                <input type="number" name="lead_time_days" class="form-control rounded-3" value="7" min="1" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Carrier / Logistics Provider</label>
                                <input type="text" name="carrier_name" class="form-control rounded-3" value="Express Logistics Hub">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Tracking Reference No</label>
                                <input type="text" name="tracking_reference" class="form-control rounded-3" value="TRK-{{ strtoupper(uniqid()) }}">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label fw-semibold">Vehicle Number</label>
                                <input type="text" name="vehicle_number" class="form-control rounded-3" value="MH-04-EX-9988">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 mt-3">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-3 fw-bold">Confirm Dispatch & Start Transit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach
@endsection
