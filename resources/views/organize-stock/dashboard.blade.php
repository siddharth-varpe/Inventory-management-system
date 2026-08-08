@extends('layouts.app')

@section('title', 'Workspace - Organize Stock Portal - StockManager ERP')

@section('header', 'Warehouse Operational Workspace')
@section('subheader', 'Task-oriented operational desk for put-away storage, pick & pack execution, inventory movements, and exception triage.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Workspace</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Operational Workspace -->
    <div class="col-12 col-lg-9">
        <!-- Operational Queue Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('organize.putaway.index') }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body hover-shadow transition-all">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted small fw-semibold">Pending Put-Away</div>
                            <span class="fs-4">📥</span>
                        </div>
                        <div class="fs-3 fw-bold text-warning-emphasis mt-1">{{ $kpis['pending_putaway'] }} Tasks</div>
                        <span class="text-muted small" style="font-size: 0.75rem;">Received inventory to bin</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('organize.fulfillment.index') }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body hover-shadow transition-all">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted small fw-semibold">Pick & Pack Tasks</div>
                            <span class="fs-4">📦</span>
                        </div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ $kpis['pending_picks'] }} Tasks</div>
                        <span class="text-muted small" style="font-size: 0.75rem;">Fulfillment line items</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('organize.fulfillment.index') }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body hover-shadow transition-all">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted small fw-semibold">Completed / Ready</div>
                            <span class="fs-4">📤</span>
                        </div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ $kpis['pending_dispatch'] }} Ready</div>
                        <span class="text-muted small" style="font-size: 0.75rem;">Handed off to Transport</span>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('organize.exceptions.index') }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body hover-shadow transition-all">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="text-muted small fw-semibold">Open Exceptions</div>
                            <span class="fs-4">⚠️</span>
                        </div>
                        <div class="fs-3 fw-bold text-danger mt-1">{{ $kpis['today_exceptions'] }} Issues</div>
                        <span class="text-muted small" style="font-size: 0.75rem;">Damaged/lost triage</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Quick Operational Actions Bar -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <h6 class="fw-bold text-body mb-3">Warehouse Operational Desk</h6>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('organize.putaway.index') }}" class="btn btn-primary rounded-3 px-3 fw-bold">📥 Execute Put-Away Storage</a>
                <a href="{{ route('organize.fulfillment.index') }}" class="btn btn-success rounded-3 px-3 fw-bold">📦 Pick & Pack Fulfillment Station</a>
                <a href="{{ route('organize.transfers.index') }}" class="btn btn-outline-secondary rounded-3 px-3 fw-bold">🔄 Transfer Inventory</a>
                <a href="{{ route('organize.exceptions.index') }}" class="btn btn-outline-danger rounded-3 px-3 fw-bold">⚠️ Report Exception</a>
            </div>
        </div>

        <!-- Operational Task Queues Section -->
        <div class="row g-4 mb-4">
            <!-- Pending Put-Away Queue -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-body mb-0">Pending Put-Away Tasks</h6>
                        <a href="{{ route('organize.putaway.index') }}" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">View All &rarr;</a>
                    </div>
                    @if($recentPutaways->isEmpty())
                        <x-empty-state title="No Pending Put-Away Tasks" message="All received stock items have been assigned to bin storage locations." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr class="text-muted">
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPutaways as $pa)
                                    <tr>
                                        <td>
                                            <strong class="text-body d-block">{{ $pa->product->name ?? 'Item' }}</strong>
                                            <code class="text-muted" style="font-size: 0.75rem;">{{ $pa->product->sku ?? '' }}</code>
                                        </td>
                                        <td class="fw-bold">{{ $pa->quantity }}</td>
                                        <td>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">Pending Bin</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('organize.putaway.index') }}" class="btn btn-xs btn-primary rounded-3 px-2 py-1 fw-semibold">Assign Bin</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Active Picking Queue -->
            <div class="col-12 col-xl-6">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h6 class="fw-bold text-body mb-0">Active Pick & Pack Tasks</h6>
                        <a href="{{ route('organize.fulfillment.index') }}" class="btn btn-sm btn-link text-decoration-none fw-bold p-0">View All &rarr;</a>
                    </div>
                    @if($recentPicks->isEmpty())
                        <x-empty-state title="No Active Picking Tasks" message="No pending pick orders currently assigned in the queue." />
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr class="text-muted">
                                        <th>Pick Code</th>
                                        <th>Order Ref</th>
                                        <th>Priority</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPicks as $pick)
                                    <tr>
                                        <td>
                                            <code>{{ $pick->task_number }}</code>
                                        </td>
                                        <td class="fw-semibold">{{ $pick->order_reference }}</td>
                                        <td>
                                            <span class="badge {{ $pick->priority === 'urgent' ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-secondary' }}">
                                                {{ ucfirst($pick->priority) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('organize.fulfillment.index', ['task_id' => $pick->id]) }}" class="btn btn-xs btn-success rounded-3 px-2 py-1 fw-semibold">Open Station</a>
                                        </td>
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
