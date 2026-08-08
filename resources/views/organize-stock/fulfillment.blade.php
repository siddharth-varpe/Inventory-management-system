@extends('layouts.app')

@section('title', 'Pick & Pack Fulfillment Station - Organize Stock')

@section('header', 'Pick & Pack Fulfillment Station')
@section('subheader', 'Single execution workspace for live Warehouse Outbound Execution: Picking, Barcode Verification, Exception Handling, and Package Sealing')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pick & Pack Station</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Unified Fulfillment Execution Station Workspace -->
    <div class="col-12 col-lg-9">

        <!-- Header Controls & Status Filters (Queue Workspaces) -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark font-monospace px-2.5 py-1 rounded-pill">LIVE WAREHOUSE QUEUE</span>
                    <h5 class="fw-bold text-body mb-0">Outbound Execution Queue</h5>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('organize.fulfillment.index', array_filter(['search' => $search])) }}" class="btn btn-sm rounded-pill {{ !$statusFilter ? 'btn-primary' : 'btn-outline-secondary' }}">Active Tasks</a>
                    <a href="{{ route('organize.fulfillment.index', array_filter(['status' => 'picking', 'search' => $search])) }}" class="btn btn-sm rounded-pill {{ $statusFilter === 'picking' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">In Picking</a>
                    <a href="{{ route('organize.fulfillment.index', array_filter(['status' => 'completed', 'search' => $search])) }}" class="btn btn-sm rounded-pill {{ $statusFilter === 'completed' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }}">Completed / Ready</a>
                </div>
            </div>
        </div>

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                {{ session('error') }}
            </div>
        @endif

        <!-- Master-Detail Single Screen Workspace -->
        <x-master-detail-layout queueTitle="WAREHOUSE TASKS QUEUE" queueSubtitle="Sorted by Priority (Urgent > High > Normal > FIFO)">
            <x-slot:queueContent>
                @if($tasks->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="fs-2 mb-1">📭</div>
                        <div class="fw-bold">No tasks found in this queue.</div>
                        <div class="small text-muted">Tasks automatically populate when Sales Orders are created.</div>
                    </div>
                @else
                    <div id="fulfillmentQueueContainer">
                        @foreach($tasks as $t)
                            @php
                                $isLockedByOther = ($t->assigned_user_id && $t->assigned_user_id !== (auth()->id() ?? 1) && $t->status === 'picking');
                                $cardUrl = route('organize.fulfillment.index', array_filter(['task_id' => $t->id, 'status' => $statusFilter, 'search' => $search]));
                            @endphp
                            <div class="card p-3 rounded-4 mb-2 shadow-sm border-translucent position-relative task-queue-item {{ ($selectedTask->id ?? 0) === $t->id ? 'border-primary bg-primary-subtle' : 'bg-body' }}" style="cursor: pointer;" onclick="saveQueueScroll(); window.location.href='{{ $cardUrl }}'">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold font-monospace text-primary small">{{ $t->task_number }}</span>
                                    <x-priority-badge :priority="$t->priority" />
                                </div>
                                <div class="fw-bold text-body mb-0">{{ $t->order_reference }}</div>
                                <div class="small text-muted">{{ $t->customer_name ?? 'B2B Customer' }}</div>

                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-translucent small">
                                    <span class="text-secondary font-monospace">Items: {{ $t->items->count() }}</span>
                                    <x-status-badge :status="$t->status" />
                                </div>

                                @if($isLockedByOther)
                                    <div class="mt-1">
                                        <span class="badge bg-danger text-white small">🔒 Locked by {{ $t->assignedUser->name ?? 'Operator' }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-slot:queueContent>

            <x-slot:canvasContent>
                @if($selectedTask)
                    @php
                        $isCompleted = ($selectedTask->status === 'completed');
                        $totalItemsCount = $selectedTask->items->count();
                        $verifiedItemsCount = $isCompleted ? $totalItemsCount : $selectedTask->items->where('is_verified', true)->count();
                        $progressPct = $isCompleted ? 100 : round(($verifiedItemsCount / max(1, $totalItemsCount)) * 100);
                        $isAllVerified = ($isCompleted || $verifiedItemsCount === $totalItemsCount);
                    @endphp

                    <!-- Execution Station Header -->
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 border-bottom pb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary font-monospace fs-6 px-2.5 py-1 rounded-pill">{{ $selectedTask->task_number }}</span>
                                <x-priority-badge :priority="$selectedTask->priority" />
                                <x-status-badge :status="$selectedTask->status" />
                            </div>
                            <h3 class="fw-bold text-body mb-0">Sales Order {{ $selectedTask->order_reference }}</h3>
                            <div class="text-muted small mt-1">
                                Customer: <strong>{{ $selectedTask->customer_name }}</strong> | Warehouse: <strong>{{ $selectedTask->warehouse->name ?? 'Main Warehouse' }}</strong>
                            </div>
                        </div>

                        <!-- Progress Bar & Counter (Single Source of Truth) -->
                        <div style="min-width: 220px;">
                            <div class="d-flex justify-content-between small fw-bold mb-1">
                                <span>Verification Progress</span>
                                <span class="{{ $isAllVerified ? 'text-success' : 'text-primary' }}">{{ $verifiedItemsCount }} / {{ $totalItemsCount }} ({{ $progressPct }}%)</span>
                            </div>
                            <div class="progress rounded-pill" style="height: 10px;">
                                <div class="progress-bar {{ $isAllVerified ? 'bg-success' : 'bg-primary' }} {{ !$isCompleted ? 'progress-bar-striped progress-bar-animated' : '' }}" role="progressbar" style="width: {{ $progressPct }}%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Keyboard Wedge Barcode Scanner Mode Panel (Active Mode vs Read-Only Completed Notice) -->
                    @if(!$isCompleted)
                        <div class="card p-3 rounded-4 shadow-sm border-primary mb-4 bg-body-tertiary">
                            <form id="barcodeScanForm" method="POST" action="{{ route('organize.fulfillment.barcode', $selectedTask->id) }}">
                                @csrf
                                <div class="d-flex align-items-center gap-3">
                                    <div class="fs-3 text-primary">📷</div>
                                    <div class="flex-grow-1">
                                        <label class="form-label fw-bold text-body small mb-1">Barcode / SKU Keyboard Wedge Scanner Input</label>
                                        <input type="text" id="barcodeScannerInput" name="barcode" class="form-control form-control-lg font-monospace rounded-3 border-primary" placeholder="Scan SKU / Barcode here or type SKU and press Enter..." autofocus autocomplete="off">
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold px-4">Verify Scan</button>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="card p-3 rounded-4 shadow-sm border-success mb-4 bg-success-subtle">
                            <div class="d-flex align-items-center gap-3">
                                <div class="fs-3 text-success">✓</div>
                                <div>
                                    <h6 class="fw-bold text-success mb-0">Warehouse Fulfillment Completed</h6>
                                    <span class="small text-success-emphasis">This task was completed on <strong>{{ $selectedTask->completed_at ? $selectedTask->completed_at->format('d M Y, h:i A') : 'N/A' }}</strong> and is in read-only audit mode.</span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Item Verification Checklist Table -->
                    <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                        <div class="card-header bg-transparent border-bottom border-translucent p-3 d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold text-body mb-0">Item Pick & Bin Coordinates Checklist</h6>
                            <span class="small text-muted">{{ $isCompleted ? 'Historical Verified Line Items' : 'Scan SKU barcode or click \'Verify\' per item line' }}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Status</th>
                                        <th>SKU / Barcode</th>
                                        <th>Product Name</th>
                                        <th>Bin Coordinates</th>
                                        <th>Req Qty</th>
                                        <th>Stock Balances</th>
                                        <th class="pe-3 text-end">Verification</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($selectedTask->items as $item)
                                        @php
                                            $itemVerified = $isCompleted || $item->is_verified || ((int)$item->picked_quantity > 0 && (int)$item->picked_quantity >= (int)$item->requested_quantity);
                                        @endphp
                                        <tr class="{{ $itemVerified ? 'table-success-subtle' : '' }}">
                                            <td class="ps-3">
                                                @if($itemVerified)
                                                    <span class="badge bg-success rounded-circle p-1.5 fs-6" title="Verified">✓</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary rounded-circle p-1.5" title="Pending">○</span>
                                                @endif
                                            </td>
                                            <td class="font-monospace fw-bold text-primary">
                                                {{ $item->product->sku ?? 'N/A' }}
                                                @if($item->product && $item->product->barcode)
                                                    <div class="small text-muted font-monospace"><code>{{ $item->product->barcode }}</code></div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold text-body">{{ $item->product->name ?? 'Product' }}</div>
                                            </td>
                                            <td>
                                                <span class="badge bg-body-tertiary text-body border font-monospace">{{ $item->location_coordinate ?? 'WH01-MAIN-A01' }}</span>
                                            </td>
                                            <td class="fw-bold fs-6">{{ $item->requested_quantity }}</td>
                                            <td class="small text-muted">
                                                Phys: <strong>{{ $item->product->physical_stock ?? 0 }}</strong> | Res: <strong>{{ $item->product->reserved_stock ?? 0 }}</strong>
                                            </td>
                                            <td class="pe-3 text-end">
                                                @if(!$isCompleted)
                                                    <div class="d-flex justify-content-end gap-1">
                                                        @if(!$itemVerified)
                                                            <form method="POST" action="{{ route('organize.fulfillment.barcode', $selectedTask->id) }}" class="d-inline">
                                                                @csrf
                                                                <input type="hidden" name="barcode" value="{{ $item->product->sku }}">
                                                                <button type="submit" class="btn btn-sm btn-success rounded-3 fw-bold">Verify Item</button>
                                                            </form>
                                                        @else
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Verified</span>
                                                        @endif

                                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-3" data-bs-toggle="modal" data-bs-target="#exceptionModal{{ $item->id }}">
                                                            Report Exception
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 fw-bold">Verified</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Action Area: Active Packaging Assembly Form vs Official Warehouse Execution Completion Stamp -->
                    @if(!$isCompleted)
                        <!-- Active Packaging Assembly Form -->
                        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-bold text-body mb-0">📦 Package Assembly & Final Warehouse Handoff</h5>
                                    <span class="small text-muted">Enter packaging specifications to seal package and declare Ready for Dispatch to Transport Department.</span>
                                </div>
                                <div>
                                    @if($isAllVerified)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-6">ALL ITEMS VERIFIED</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-6">VERIFY ALL ITEMS TO UNLOCK DISPATCH</span>
                                    @endif
                                </div>
                            </div>

                            <form method="POST" action="{{ route('organize.fulfillment.seal-ready', $selectedTask->id) }}">
                                @csrf
                                <fieldset {{ !$isAllVerified ? 'disabled' : '' }}>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold text-body small">Package Type *</label>
                                            <select name="package_type" class="form-select rounded-3" required>
                                                <option value="Carton">Standard Carton</option>
                                                <option value="Bag">Poly Bag</option>
                                                <option value="Crate">Wooden Crate</option>
                                                <option value="Pallet">Shipping Pallet</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold text-body small">Gross Weight (kg) *</label>
                                            <input type="number" step="0.1" name="weight_kg" class="form-control rounded-3" value="2.5" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold text-body small">Package Count *</label>
                                            <input type="number" name="package_count" class="form-control rounded-3" value="1" min="1" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold text-body small">Packing Notes / Handling Instructions</label>
                                            <input type="text" name="packing_notes" class="form-control rounded-3" placeholder="e.g. Sealed in double corrugated box with bubble wrap. Fragile stickers applied.">
                                        </div>

                                        <!-- Main One-Action Execution Button -->
                                        <div class="col-12 text-end mt-4">
                                            @if($isAllVerified)
                                                <button type="submit" class="btn btn-success btn-lg rounded-3 px-5 py-3 fw-black text-uppercase shadow-lg d-inline-flex align-items-center gap-2">
                                                    <span>🔒 Seal Package & Ready for Dispatch</span>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0z"/><path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/></svg>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-secondary btn-lg rounded-3 px-5 py-3 fw-bold" disabled>
                                                    🔒 Verify All Items to Enable Seal & Ready for Dispatch
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                    @else
                        <!-- Official Enterprise Warehouse Fulfillment Completion Stamp -->
                        <div class="card p-4 rounded-4 shadow-sm border-success bg-success-subtle text-center mb-4 transition-all hover-shadow" style="cursor: default; border: 1.5px solid #198754;">
                            <div class="d-inline-flex align-items-center justify-content-center bg-success text-white rounded-circle mb-3 shadow-sm" style="width: 64px; height: 64px; font-size: 2rem;">
                                ✓
                            </div>
                            <h3 class="fw-black text-success mb-1 tracking-tight">✓ ORDER FULFILLED</h3>
                            <p class="text-success-emphasis fw-semibold mb-3">
                                Warehouse execution completed successfully. Package verified, sealed and handed over to the Transport Department.
                            </p>

                            <!-- Official Stamp Execution Details Grid -->
                            <div class="row g-3 text-start bg-body rounded-4 p-4 border border-success-subtle shadow-sm mx-auto" style="max-width: 750px;">
                                <div class="col-6 col-md-4">
                                    <span class="text-muted small fw-semibold d-block">Completed Date & Time</span>
                                    <strong class="text-body small">{{ $selectedTask->completed_at ? $selectedTask->completed_at->format('d M Y, h:i A') : now()->format('d M Y, h:i A') }}</strong>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="text-muted small fw-semibold d-block">Assigned Operator</span>
                                    <strong class="text-body small">{{ $selectedTask->assignedUser->name ?? 'Enterprise Administrator' }}</strong>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="text-muted small fw-semibold d-block">Pick Task ID</span>
                                    <code class="text-primary fw-bold small">{{ $selectedTask->task_number }}</code>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="text-muted small fw-semibold d-block">Sales Order ID</span>
                                    <strong class="text-body small">{{ $selectedTask->order_reference }}</strong>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="text-muted small fw-semibold d-block">Warehouse Location</span>
                                    <strong class="text-body small">{{ $selectedTask->warehouse->name ?? 'Main Distribution Center' }}</strong>
                                </div>
                                <div class="col-6 col-md-4">
                                    <span class="text-muted small fw-semibold d-block">Final Verification</span>
                                    <span class="badge bg-success text-white fw-bold">100% Completed</span>
                                </div>
                                <div class="col-12 border-top border-translucent pt-3 mt-2">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between small">
                                        <span class="text-muted">Transport Department Status: <strong class="text-success">Ready For Dispatch</strong></span>
                                        <span class="text-muted">Package Specs: <strong>Standard Carton (2.5 kg)</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                @else
                    <div class="text-center text-muted py-5">
                        <div class="fs-1 mb-2">📦</div>
                        <h5>Select a Warehouse Execution Task</h5>
                        <p class="small text-muted mb-0">Select an active task from the left queue panel to open the Pick & Pack Fulfillment Station.</p>
                    </div>
                @endif
            </x-slot:canvasContent>
        </x-master-detail-layout>

    </div>
</div>

@push('scripts')
<script>
function saveQueueScroll() {
    var queueEl = document.querySelector('.master-detail-queue') || document.getElementById('fulfillmentQueueContainer');
    if (queueEl) {
        sessionStorage.setItem('fulfillment_queue_scroll', queueEl.scrollTop);
    }
    sessionStorage.setItem('fulfillment_window_scroll', window.scrollY);
}

document.addEventListener('DOMContentLoaded', function() {
    var savedQueueScroll = sessionStorage.getItem('fulfillment_queue_scroll');
    var queueEl = document.querySelector('.master-detail-queue') || document.getElementById('fulfillmentQueueContainer');
    if (savedQueueScroll && queueEl) {
        queueEl.scrollTop = parseInt(savedQueueScroll, 10);
    }

    var savedWindowScroll = sessionStorage.getItem('fulfillment_window_scroll');
    if (savedWindowScroll) {
        window.scrollTo(0, parseInt(savedWindowScroll, 10));
    }
});
</script>
@endpush
@endsection
