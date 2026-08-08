@extends('layouts.app')

@section('title', 'Warehouse Operations - Organize Stock')

@section('header', 'Warehouse Execution Station')
@section('subheader', 'Live Pick, Pack & Dispatch Tasks generated automatically from Sales Orders')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Warehouse Execution</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Operational Queues & Task Execution Canvas -->
    <div class="col-12 col-lg-9">

        <!-- Operational Queue Navigation Tabs -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                <div class="nav nav-pills gap-2">
                    <a href="{{ route('organize.picking.index', ['tab' => 'pending_pick']) }}" class="nav-link rounded-pill fw-bold {{ $tab === 'pending_pick' ? 'active bg-primary' : 'bg-body-tertiary text-body' }}">
                        <span>📦</span> 1. Pending Pick Tasks
                    </a>
                    <a href="{{ route('organize.picking.index', ['tab' => 'packing']) }}" class="nav-link rounded-pill fw-bold {{ $tab === 'packing' ? 'active bg-warning text-dark' : 'bg-body-tertiary text-body' }}">
                        <span>🏷️</span> 2. Packing Queue
                    </a>
                    <a href="{{ route('organize.picking.index', ['tab' => 'dispatch']) }}" class="nav-link rounded-pill fw-bold {{ $tab === 'dispatch' ? 'active bg-success text-white' : 'bg-body-tertiary text-body' }}">
                        <span>🚀</span> 3. Dispatch Queue
                    </a>
                    <a href="{{ route('organize.picking.index', ['tab' => 'history']) }}" class="nav-link rounded-pill fw-bold {{ $tab === 'history' ? 'active bg-secondary text-white' : 'bg-body-tertiary text-body' }}">
                        <span>📜</span> History
                    </a>
                </div>
            </div>
        </div>

        <!-- Master-Detail Execution Workspace -->
        <x-master-detail-layout queueTitle="{{ strtoupper(str_replace('_', ' ', $tab)) }} QUEUE" queueSubtitle="Live Sales Order Tasks">
            <x-slot:queueContent>
                @if($tasks->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="fs-2 mb-1">📭</div>
                        <div class="fw-bold">No tasks in {{ str_replace('_', ' ', $tab) }} queue.</div>
                        <div class="small text-muted">Tasks automatically appear when Sales Orders are created.</div>
                    </div>
                @else
                    @foreach($tasks as $t)
                        <x-task-card 
                            :title="'Task ' . $t->task_number"
                            :subtitle="$t->order_reference . ' | ' . ($t->customer_name ?: 'B2B Customer')"
                            :priority="$t->priority"
                            :status="$t->status"
                            :active="($selectedTask->id ?? 0) === $t->id"
                            :url="route('organize.picking.index', ['tab' => $tab, 'task_id' => $t->id])"
                        />
                    @endforeach
                @endif
            </x-slot:queueContent>

            <x-slot:canvasContent>
                @if($selectedTask)
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-1 fw-bold">Task #{{ $selectedTask->task_number }}</span>
                            <h4 class="fw-bold text-body mb-0">Sales Order Reference: {{ $selectedTask->order_reference }}</h4>
                            <div class="text-muted small">Customer: <strong>{{ $selectedTask->customer_name ?? 'General Customer' }}</strong> | Warehouse: {{ $selectedTask->warehouse->name ?? 'Main Warehouse' }}</div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <x-priority-badge :priority="$selectedTask->priority" />
                            <x-status-badge :status="$selectedTask->status" />
                        </div>
                    </div>

                    <!-- STEP 1: PENDING PICK TAB EXECUTION -->
                    @if($tab === 'pending_pick')
                        @if($selectedTask->status === 'pending')
                            <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <h6 class="fw-bold mb-0">Task Ready for Picking</h6>
                                    <span class="small">Click 'Start Picking' to assign yourself as operator and locate warehouse bin coordinates.</span>
                                </div>
                                <form method="POST" action="{{ route('organize.picking.start', $selectedTask->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark px-4 py-2">Start Picking &rarr;</button>
                                </form>
                            </div>
                        @endif

                        <!-- Items Bin Location Checklist -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-body mb-2">Item Bin Location Checklist</h6>
                            <p class="text-muted small mb-3">Operator must verify items against designated warehouse bin coordinates before completing picking.</p>

                            <x-checklist :items="$selectedTask->items" :taskId="$selectedTask->id" />
                        </div>

                        <!-- Complete Picking Card -->
                        <div class="card p-3 rounded-4 bg-body-tertiary border">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-body">Picking Execution Status</div>
                                    <div class="text-muted small">
                                        @if($selectedTask->is_all_verified)
                                            <span class="text-success fw-bold">✔ All checklist items verified. Ready for Packing Queue!</span>
                                        @else
                                            <span class="text-danger fw-semibold">✖ Verification incomplete. Verify all line items above.</span>
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    @if($selectedTask->is_all_verified || $selectedTask->status === 'picking')
                                        <form action="{{ route('organize.picking.complete', $selectedTask->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold d-flex align-items-center gap-2">
                                                <span>Complete Picking & Move to Packing Queue</span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button class="btn btn-secondary rounded-3 px-4 py-2 fw-bold" disabled>Verify All Items to Complete</button>
                                    @endif
                                </div>
                            </div>
                        </div>

                    <!-- STEP 2: PACKING QUEUE TAB EXECUTION -->
                    @elseif($tab === 'packing')
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <h6 class="fw-bold mb-1">🏷️ Packing Station Verification</h6>
                            <span class="small">Verify picked goods, package in protective containers, print shipping labels, and attach commercial invoices.</span>
                        </div>

                        <!-- Packed Items Table -->
                        <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                            <div class="card-header bg-transparent border-bottom border-translucent p-3">
                                <h6 class="fw-bold text-body mb-0">Verified Picked Items for Packing</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">SKU</th>
                                            <th>Product Name</th>
                                            <th>Bin Location</th>
                                            <th class="pe-3 text-end">Quantity</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedTask->items as $item)
                                            <tr>
                                                <td class="ps-3 font-monospace text-primary fw-bold">{{ $item->product->sku ?? 'N/A' }}</td>
                                                <td><div class="fw-bold text-body">{{ $item->product->name ?? 'Product' }}</div></td>
                                                <td><code>{{ $item->location_coordinate }}</code></td>
                                                <td class="pe-3 text-end fw-bold">{{ $item->requested_quantity }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Complete Packing Form -->
                        <div class="card p-3 rounded-4 bg-body-tertiary border">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold text-body">Confirm Package Assembly & Labeling</div>
                                    <div class="text-muted small">Generates shipping label code and moves task into Dispatch Queue.</div>
                                </div>
                                <form method="POST" action="{{ route('organize.picking.complete-packing', $selectedTask->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning text-dark rounded-3 px-4 py-2 fw-bold d-flex align-items-center gap-2">
                                        <span>Complete Packing & Move to Dispatch Queue</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                    <!-- STEP 3: DISPATCH QUEUE TAB EXECUTION -->
                    @elseif($tab === 'dispatch')
                        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
                            <h5 class="fw-bold text-body mb-3">🚀 Execute Final Goods Dispatch</h5>
                            <p class="text-muted small mb-3">Entering transport details will update Sales Order status to <strong>DISPATCHED</strong> and decrement physical & reserved stock balances.</p>

                            <form method="POST" action="{{ route('organize.picking.dispatch', $selectedTask->id) }}">
                                @csrf
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-body small">Transport / Courier Company *</label>
                                        <input type="text" name="carrier" class="form-control rounded-3" placeholder="e.g. BlueDart, DHL, GATI" value="BlueDart Logistics" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-body small">Tracking / AWB Number *</label>
                                        <input type="text" name="tracking_number" class="form-control rounded-3" placeholder="e.g. AWB-99881122" value="AWB-{{ strtoupper(\Illuminate\Support\Str::random(8)) }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-body small">Vehicle Number</label>
                                        <input type="text" name="vehicle_number" class="form-control rounded-3" placeholder="e.g. MH-12-AB-1234" value="MH-12-EX-9988">
                                    </div>
                                    <div class="col-12 text-end mt-4">
                                        <button type="submit" class="btn btn-success rounded-3 px-4 py-2.5 fw-bold fs-6 shadow-sm">
                                            <span>Dispatch Goods & Decrement Physical Inventory &rarr;</span>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <!-- HISTORY TAB -->
                        <div class="alert alert-secondary border-0 rounded-4 shadow-sm">
                            <h6 class="fw-bold mb-1">📜 Completed Operational Task History</h6>
                            <span class="small">Dispatched and completed warehouse execution tasks logged into permanent ERP timeline.</span>
                        </div>
                    @endif

                @else
                    <div class="text-center text-muted py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-card-checklist text-muted mb-2" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
                        <p class="mb-0">Select an operational task from the left panel to execute picking, packing, or dispatch.</p>
                    </div>
                @endif
            </x-slot:canvasContent>
        </x-master-detail-layout>
    </div>
</div>
@endsection
