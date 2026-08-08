@extends('layouts.app')

@section('title', 'Transport Department - StockManager ERP')

@section('header', 'Transport Department')
@section('subheader', 'Fleet Operations & Logistics Management')

@push('styles')
<style>
    /* Transport Department Full-Width Enterprise Shell */
    .transport-workspace-wrapper {
        width: 100%;
        min-height: calc(100vh - 120px);
        padding-inline: clamp(12px, 1.2vw, 24px);
        padding-block: 0.5rem 2rem;
    }

    .transport-app-shell {
        display: grid;
        grid-template-columns: 250px minmax(0, 1fr);
        gap: 1.5rem;
        width: 100%;
        align-items: start;
    }

    @media (max-width: 991.98px) {
        .transport-app-shell {
            grid-template-columns: 1fr;
        }
    }

    /* Status Cards Grid (6 Columns on Desktop) */
    .status-cards-grid {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.875rem;
        width: 100%;
    }

    @media (max-width: 1199.98px) {
        .status-cards-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .status-cards-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Filter Bar Flex Layout */
    .filter-bar-flex {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
        width: 100%;
    }

    .filter-search-wrap {
        flex: 2 1 280px;
    }

    .filter-select-wrap {
        flex: 1 1 160px;
    }

    .filter-actions-wrap {
        flex: 0 0 auto;
        display: flex;
        gap: 0.5rem;
        margin-left: auto;
    }

    @media (max-width: 767.98px) {
        .filter-search-wrap, .filter-select-wrap, .filter-actions-wrap {
            flex: 1 1 100%;
            margin-left: 0;
        }
    }

    /* Delivery Card Internal Grid */
    .delivery-order-card {
        width: 100%;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .card-footer-grid {
        display: grid;
        grid-template-columns: 1.5fr 1.5fr 1fr auto;
        gap: 1.25rem;
        align-items: center;
        width: 100%;
    }

    @media (max-width: 991.98px) {
        .card-footer-grid {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .card-footer-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="transport-workspace-wrapper">
    <div class="transport-app-shell">

        <!-- LEFT SIDEBAR NAVIGATION PANEL -->
        <div class="sidebar-column">
            <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body sticky-top" style="top: 1rem; z-index: 100;">
                <div class="mb-3 pb-2 border-bottom border-translucent">
                    <div class="small fw-black text-primary text-uppercase font-monospace tracking-wider mb-1">StockManager ERP</div>
                    <h6 class="fw-black text-body mb-0">TRANSPORT DEPARTMENT</h6>
                </div>

                <nav class="nav nav-pills flex-column gap-1">
                    <!-- CATEGORY: MAIN -->
                    <div class="text-muted font-monospace text-uppercase small px-2 mt-2 mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">MAIN</div>
                    
                    <a href="{{ route('transport.index', ['tab' => 'overview']) }}" 
                       class="nav-link rounded-3 px-3 py-2 fw-bold d-flex align-items-center gap-2 {{ $activeTab === 'overview' ? 'active bg-primary text-white shadow-sm' : 'text-body hover-bg-tertiary' }}">
                        <span>🏠</span> Overview
                    </a>

                    <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" 
                       class="nav-link rounded-3 px-3 py-2 fw-bold d-flex align-items-center justify-content-between gap-2 {{ $activeTab === 'delivery-orders' ? 'active bg-primary text-white shadow-sm' : 'text-body hover-bg-tertiary' }}">
                        <span class="d-flex align-items-center gap-2">
                            <span>📦</span> Delivery Orders
                        </span>
                        <span class="badge {{ $activeTab === 'delivery-orders' ? 'bg-white text-primary' : 'bg-body-tertiary text-muted' }} rounded-pill" style="font-size: 0.7rem;">
                            {{ $statusCounts['all'] ?? $requests->total() }}
                        </span>
                    </a>

                    <!-- CATEGORY: FLEET -->
                    <div class="text-muted font-monospace text-uppercase small px-2 mt-3 mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">FLEET</div>

                    <a href="{{ route('transport.index', ['tab' => 'drivers']) }}" 
                       class="nav-link rounded-3 px-3 py-2 fw-bold d-flex align-items-center justify-content-between gap-2 {{ $activeTab === 'drivers' ? 'active bg-primary text-white shadow-sm' : 'text-body hover-bg-tertiary' }}">
                        <span class="d-flex align-items-center gap-2">
                            <span>👨‍✈️</span> Drivers
                        </span>
                        <span class="badge {{ $activeTab === 'drivers' ? 'bg-white text-primary' : 'bg-body-tertiary text-muted' }} rounded-pill" style="font-size: 0.7rem;">
                            {{ $allDrivers->count() }}
                        </span>
                    </a>

                    <a href="{{ route('transport.index', ['tab' => 'vehicles']) }}" 
                       class="nav-link rounded-3 px-3 py-2 fw-bold d-flex align-items-center justify-content-between gap-2 {{ $activeTab === 'vehicles' ? 'active bg-primary text-white shadow-sm' : 'text-body hover-bg-tertiary' }}">
                        <span class="d-flex align-items-center gap-2">
                            <span>🚚</span> Vehicles
                        </span>
                        <span class="badge {{ $activeTab === 'vehicles' ? 'bg-white text-primary' : 'bg-body-tertiary text-muted' }} rounded-pill" style="font-size: 0.7rem;">
                            {{ $allVehicles->count() }}
                        </span>
                    </a>

                    <!-- CATEGORY: OPERATIONS -->
                    <div class="text-muted font-monospace text-uppercase small px-2 mt-3 mb-1" style="font-size: 0.65rem; letter-spacing: 0.05em;">OPERATIONS</div>

                    <a href="{{ route('transport.index', ['tab' => 'active']) }}" 
                       class="nav-link rounded-3 px-3 py-2 fw-bold d-flex align-items-center justify-content-between gap-2 {{ $activeTab === 'active' ? 'active bg-primary text-white shadow-sm' : 'text-body hover-bg-tertiary' }}">
                        <span class="d-flex align-items-center gap-2">
                            <span>🚛</span> Active Deliveries
                        </span>
                        <span class="badge {{ $activeTab === 'active' ? 'bg-white text-primary' : 'bg-body-tertiary text-muted' }} rounded-pill" style="font-size: 0.7rem;">
                            {{ $statusCounts['active'] ?? 0 }}
                        </span>
                    </a>

                    <a href="{{ route('transport.index', ['tab' => 'history']) }}" 
                       class="nav-link rounded-3 px-3 py-2 fw-bold d-flex align-items-center gap-2 {{ $activeTab === 'history' ? 'active bg-primary text-white shadow-sm' : 'text-body hover-bg-tertiary' }}">
                        <span>📋</span> History
                    </a>
                </nav>

                <!-- SIDEBAR WIDGET: TODAY AT A GLANCE -->
                <div class="mt-4 pt-3 border-top border-translucent">
                    <div class="small fw-black text-muted text-uppercase font-monospace mb-2" style="font-size: 0.65rem; letter-spacing: 0.05em;">TODAY AT A GLANCE</div>
                    <div class="p-2 bg-body-tertiary rounded-3 border border-translucent">
                        <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                            <span class="text-muted">Total Orders</span>
                            <span class="fw-bold font-monospace text-body">{{ $statusCounts['all'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                            <span class="text-muted">Ready for Assignment</span>
                            <span class="fw-bold font-monospace text-success">{{ $statusCounts['ready'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                            <span class="text-muted">Active Deliveries</span>
                            <span class="fw-bold font-monospace text-warning">{{ $statusCounts['active'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                            <span class="text-muted">Completed</span>
                            <span class="fw-bold font-monospace text-info">{{ $statusCounts['completed'] ?? 0 }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center small">
                            <span class="text-muted">Cancelled</span>
                            <span class="fw-bold font-monospace text-danger">{{ $statusCounts['cancelled'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN CONTENT WORKSPACE -->
        <div class="main-workspace-column w-100" style="min-width: 0;">
            <!-- Flash Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success border-0 rounded-3 shadow-sm mb-3">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-3">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 1. OVERVIEW -->
            @if($activeTab === 'overview')
                <div>
                    <!-- Minimal Operational Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body text-center h-100">
                                <div class="text-muted small fw-semibold">Awaiting Warehouse</div>
                                <div class="fs-3 fw-black text-warning mt-1">{{ $ordersAwaitingWarehouseCount ?? 0 }}</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">Picking & Packaging</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body text-center h-100">
                                <div class="text-muted small fw-semibold">Ready for Assignment</div>
                                <div class="fs-3 fw-black text-success mt-1">{{ $ordersReadyAssignmentCount ?? 0 }}</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">Requires Driver/Vehicle</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body text-center h-100">
                                <div class="text-muted small fw-semibold">Assigned Orders</div>
                                <div class="fs-3 fw-black text-info mt-1">{{ $ordersAssignedCount ?? 0 }}</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">Planned for Dispatch</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body text-center h-100">
                                <div class="text-muted small fw-semibold">Active Deliveries</div>
                                <div class="fs-3 fw-black text-primary mt-1">{{ $activeDeliveriesCount ?? 0 }}</div>
                                <div class="small text-muted" style="font-size: 0.75rem;">Dispatched / In Transit</div>
                            </div>
                        </div>
                    </div>

                    <!-- Operational Activity Highlights -->
                    <div class="row g-4">
                        <!-- Delivery Orders Action Queue Highlight -->
                        <div class="col-md-7">
                            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                    <h5 class="fw-bold text-body mb-0">⚡ Orders Requiring Attention</h5>
                                    <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'ready_for_assignment']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        View Full Queue &rarr;
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0 small">
                                        <thead>
                                            <tr class="text-muted font-monospace">
                                                <th>Order ID</th>
                                                <th>Customer</th>
                                                <th>Destination</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($requests->take(5) as $req)
                                                <tr>
                                                    <td class="fw-bold font-monospace text-primary">{{ $req->order_reference }}</td>
                                                    <td class="fw-bold text-body">{{ $req->customer_name }}</td>
                                                    <td class="text-muted">{{ $req->delivery_city }}</td>
                                                    <td><span class="badge rounded-pill {{ $req->priority_badge_class }}">{{ strtoupper($req->priority ?? 'NORMAL') }}</span></td>
                                                    <td><span class="badge rounded-pill {{ $req->status_badge_class }}">{{ $req->status_label }}</span></td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-4">No active delivery orders.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Fleet Readiness Summary -->
                        <div class="col-md-5">
                            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                                <h5 class="fw-bold text-body mb-3 border-bottom pb-2">🚛 Fleet Readiness Snapshot</h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent text-center">
                                            <div class="text-muted small">Available Drivers</div>
                                            <div class="fs-4 fw-bold text-success mt-1">👤 {{ $allDrivers->where('status', 'available')->count() }}</div>
                                            <div class="small text-muted" style="font-size: 0.75rem;">Of {{ $allDrivers->count() }} total</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent text-center">
                                            <div class="text-muted small">Available Vehicles</div>
                                            <div class="fs-4 fw-bold text-primary mt-1">🚛 {{ $allVehicles->where('status', 'available')->count() }}</div>
                                            <div class="small text-muted" style="font-size: 0.75rem;">Of {{ $allVehicles->count() }} total</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 pt-3 border-top border-translucent d-flex gap-2">
                                    <a href="{{ route('transport.index', ['tab' => 'drivers']) }}" class="btn btn-sm btn-outline-secondary w-50 rounded-pill fw-bold">Manage Drivers</a>
                                    <a href="{{ route('transport.index', ['tab' => 'vehicles']) }}" class="btn btn-sm btn-outline-secondary w-50 rounded-pill fw-bold">Manage Vehicles</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- 2. DELIVERY ORDERS -->
            @if($activeTab === 'delivery-orders')
                @include('transport.partials.delivery-orders')
            @endif

            <!-- 3. DRIVERS -->
            @if($activeTab === 'drivers')
                @include('transport.partials.driver-master')
            @endif

            <!-- 4. VEHICLES -->
            @if($activeTab === 'vehicles')
                @include('transport.partials.vehicle-master')
            @endif

            <!-- 5. ACTIVE DELIVERIES -->
            @if($activeTab === 'active')
                @include('transport.partials.active-deliveries')
            @endif

            <!-- 6. HISTORY -->
            @if($activeTab === 'history')
                @include('transport.partials.history')
            @endif
        </div>

    </div>
</div>
@endsection
