@extends('layouts.app')

@section('title', 'Sales Order ' . $order->order_number . ' - Details')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="orders" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Action Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary font-monospace px-2.5 py-1 rounded-pill fs-6">{{ $order->order_number }}</span>
                        @if($order->status === 'reserved')
                            <span class="badge bg-success fs-6">INVENTORY RESERVED</span>
                        @elseif($order->status === 'approved')
                            <span class="badge bg-info fs-6">APPROVED</span>
                        @elseif($order->status === 'cancelled')
                            <span class="badge bg-secondary fs-6">CANCELLED</span>
                        @else
                            <span class="badge bg-primary fs-6">{{ strtoupper(str_replace('_', ' ', $order->status)) }}</span>
                        @endif
                    </div>
                    <h3 class="fw-bold text-body mb-0">{{ $order->customer->company_name ?? 'N/A' }}</h3>
                    <p class="text-muted small mb-0 mt-1">
                        Order Date: {{ $order->order_date->format('d M Y') }} | Warehouse: {{ $order->warehouse->name ?? 'Main Warehouse' }}
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if(in_array($order->status, ['draft', 'pending_approval']))
                        <form method="POST" action="{{ route('sales.orders.approve', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-success rounded-3 fw-bold px-3">Approve Order & Reserve Stock</button>
                        </form>
                    @endif

                    @if(!in_array($order->status, ['cancelled', 'completed', 'closed']))
                        <button type="button" class="btn btn-outline-danger rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">Cancel Order</button>
                    @endif

                    <a href="{{ route('sales.orders.index') }}" class="btn btn-outline-secondary rounded-3">Back to Queue</a>
                </div>
            </div>
        </div>

        <!-- Order Detail Grid -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <!-- Line Items Table -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">Sales Order Line Items & ATP Breakdown</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">SKU</th>
                                    <th>Product Name</th>
                                    <th>Ordered</th>
                                    <th>Reserved</th>
                                    <th>Backorder</th>
                                    <th>Unit Price</th>
                                    <th class="pe-3 text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php
                                        $p = $item->product;
                                        $physical = $p->physical_stock ?? 0;
                                        $reserved = $p->reserved_stock ?? 0;
                                        $available = max(0, $physical - $reserved);
                                    @endphp
                                    <tr>
                                        <td class="ps-3 font-monospace text-primary fw-bold">{{ $p->sku ?? 'N/A' }}</td>
                                        <td>
                                            <div class="fw-bold text-body">{{ $p->name ?? 'Product' }}</div>
                                            <div class="small text-muted">ATP: Available ({{ $available }}) = Phys ({{ $physical }}) - Res ({{ $reserved }})</div>
                                        </td>
                                        <td class="fw-bold">{{ $item->ordered_qty }}</td>
                                        <td><span class="badge bg-success">{{ $item->reserved_qty }}</span></td>
                                        <td>
                                            @if($item->backorder_qty > 0)
                                                <span class="badge bg-warning text-dark">{{ $item->backorder_qty }}</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>₹{{ number_format((float)$item->unit_price, 2) }}</td>
                                        <td class="pe-3 text-end fw-bold">₹{{ number_format((float)$item->line_total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Reservations Log Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">Active Inventory Reservations Log</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Product</th>
                                    <th>Reserved Qty</th>
                                    <th>Status</th>
                                    <th class="pe-3 text-end">Reserved Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($order->reservations as $res)
                                    <tr>
                                        <td class="ps-3 fw-bold text-body">{{ $res->product->name ?? 'Product' }}</td>
                                        <td class="fw-bold text-success">{{ $res->reserved_qty }} Units</td>
                                        <td>
                                            @if($res->status === 'active')
                                                <span class="badge bg-success">ACTIVE</span>
                                            @else
                                                <span class="badge bg-secondary">{{ strtoupper($res->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 text-end small text-muted">{{ $res->reserved_at ? $res->reserved_at->format('d M Y, h:i A') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No inventory reservations created yet. Order must be approved to allocate stock.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Financial Summary Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">Order Financial Summary</h6>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-borderless table-sm mb-0">
                            <tr><td class="text-muted">Subtotal:</td><td class="text-end fw-bold">₹{{ number_format((float)$order->subtotal, 2) }}</td></tr>
                            <tr><td class="text-muted">Order Discount:</td><td class="text-end fw-bold text-danger">-₹{{ number_format((float)$order->order_discount_amount, 2) }}</td></tr>
                            <tr class="border-top"><td class="text-muted">Taxable Amount:</td><td class="text-end fw-bold">₹{{ number_format((float)$order->taxable_amount, 2) }}</td></tr>
                            @if((float)$order->igst_amount > 0)
                                <tr><td class="text-muted">IGST:</td><td class="text-end fw-semibold text-info">₹{{ number_format((float)$order->igst_amount, 2) }}</td></tr>
                            @else
                                <tr><td class="text-muted">CGST (9%):</td><td class="text-end fw-semibold text-info">₹{{ number_format((float)$order->cgst_amount, 2) }}</td></tr>
                                <tr><td class="text-muted">SGST (9%):</td><td class="text-end fw-semibold text-info">₹{{ number_format((float)$order->sgst_amount, 2) }}</td></tr>
                            @endif
                            <tr class="border-top"><td class="fw-bold">Grand Total:</td><td class="text-end fs-4 fw-black text-success">₹{{ number_format((float)$order->grand_total, 2) }}</td></tr>
                        </table>
                    </div>
                </div>

                <!-- LINKED ENTERPRISE ERP DOCUMENTS & LIVE ORDER TRACKING STATUS CENTER -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">🔗 Linked Enterprise ERP Status Center</h6>
                    </div>
                    <div class="card-body p-3">

                        <!-- 1. Warehouse Fulfillment Module Card (Single Source of Truth) -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-body">Warehouse Execution Task</span>
                                @if($pickingTask)
                                    <span class="badge bg-info-subtle text-info-emphasis font-monospace" id="wh-card-tasknum">{{ $pickingTask->task_number }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Awaiting Task</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                Current Status: 
                                @if($pickingTask)
                                    <span class="badge bg-warning-subtle text-warning-emphasis text-capitalize" id="wh-card-status">{{ ucfirst(str_replace('_', ' ', $pickingTask->status)) }}</span>
                                @else
                                    <span class="text-muted fw-semibold">Pending Queue</span>
                                @endif
                            </div>

                            @if($pickingTask)
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                        <span class="text-muted fw-semibold">Fulfillment Progress</span>
                                        <span class="fw-bold text-body" id="wh-card-count">{{ $pickingTask->verified_items_count }} / {{ $pickingTask->total_items_count }} Verified ({{ $pickingTask->completion_percentage }}%)</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $pickingTask->progress_color_class }}" id="wh-card-progressbar" role="progressbar" style="width: {{ $pickingTask->completion_percentage }}%; transition: width 0.4s ease;" aria-valuenow="{{ $pickingTask->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-3 fw-bold w-100 py-1.5" data-bs-toggle="modal" data-bs-target="#warehouseStatusModal">
                                Warehouse Status
                            </button>
                        </div>

                        <!-- 2. Transport & Logistics Module Card (Single Source of Truth) -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="fw-bold small text-body">Transport & Logistics</span>
                                @if($transportRequest)
                                    <span class="badge bg-rose-subtle text-rose font-monospace" id="trp-card-reqnum">{{ $transportRequest->request_number }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">Awaiting Dispatch</span>
                                @endif
                            </div>
                            <div class="small text-muted mb-2">
                                Current Status: 
                                @if($transportRequest)
                                    <span class="badge {{ $transportRequest->status === 'ready_for_dispatch' ? 'bg-success text-white' : 'bg-secondary-subtle text-secondary' }} text-capitalize" id="trp-card-status">
                                        {{ ucfirst(str_replace('_', ' ', $transportRequest->status)) }}
                                    </span>
                                @else
                                    <span class="text-muted fw-semibold">Pending Packaging</span>
                                @endif
                            </div>

                            @if($transportRequest)
                                <div class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                        <span class="text-muted fw-semibold">Logistics Progress</span>
                                        <span class="fw-bold text-body" id="trp-card-pct">{{ $transportRequest->completion_percentage }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar {{ $transportRequest->progress_color_class }}" id="trp-card-progressbar" role="progressbar" style="width: {{ $transportRequest->completion_percentage }}%; transition: width 0.4s ease;" aria-valuenow="{{ $transportRequest->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-rose text-dark rounded-3 fw-bold w-100 py-1.5" data-bs-toggle="modal" data-bs-target="#transportStatusModal">
                                Transport Status
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- Modal: Cancel Order -->
<div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.orders.cancel', $order->id) }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Cancel Sales Order</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <p class="small text-muted">Cancelling this Sales Order will immediately release all active inventory reservations back to available stock.</p>
                    <label class="form-label small fw-bold">Cancellation Reason <span class="text-danger">*</span></label>
                    <textarea name="reason" class="form-control" required rows="3" placeholder="Enter reason for cancelling order..."></textarea>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-danger rounded-3 fw-bold">Confirm Cancellation & Release Stock</button></div>
            </form>
        </div>
    </div>
</div>

<!-- LINKED WAREHOUSE & TRANSPORT LIVE STATUS MODALS -->
<x-linked-status-modals
    :pickingTask="$pickingTask"
    :transportRequest="$transportRequest"
    :orderRef="$order->order_number"
    :liveStatusUrl="route('sales.orders.live-status', $order->id)"
/>
@endsection
