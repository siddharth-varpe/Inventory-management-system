@extends('layouts.app')

@section('title', 'Transport Department - StockManager ERP')

@section('header', 'Transport Department')
@section('subheader', 'Fleet Operations & Delivery Queue')

@section('content')
<div class="row g-4">

    <!-- Top Header Strip & Live Sync Status -->
    <div class="col-12">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace fs-7 d-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px;" role="status"></span>
                            🟢 System Synchronized
                        </span>
                    </div>
                    <h3 class="fw-black text-body mb-0">Transport Operations Control</h3>
                    <p class="text-muted small mb-0 mt-1">Manage delivery orders, fleet assignment, drivers roster, and vehicle availability.</p>
                </div>
            </div>

            <!-- PRIMARY WORKSPACE NAVIGATION BAR (4 CLEAN SECTIONS ONLY) -->
            <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top border-translucent">
                <a href="{{ route('transport.index', ['tab' => 'overview']) }}" 
                   class="btn btn-sm rounded-pill px-4 py-2 fw-bold {{ $activeTab === 'overview' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                    📊 Overview
                </a>
                <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" 
                   class="btn btn-sm rounded-pill px-4 py-2 fw-bold {{ $activeTab === 'delivery-orders' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                    📦 Delivery Orders ({{ $requests->total() }})
                </a>
                <a href="{{ route('transport.index', ['tab' => 'drivers']) }}" 
                   class="btn btn-sm rounded-pill px-4 py-2 fw-bold {{ $activeTab === 'drivers' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                    👤 Drivers ({{ $allDrivers->count() }})
                </a>
                <a href="{{ route('transport.index', ['tab' => 'vehicles']) }}" 
                   class="btn btn-sm rounded-pill px-4 py-2 fw-bold {{ $activeTab === 'vehicles' ? 'btn-primary shadow-sm' : 'btn-outline-secondary' }}">
                    🚛 Vehicles ({{ $allVehicles->count() }})
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Alert Messages -->
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-0">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-0">
                {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- SECTION 1: OVERVIEW -->
    <!-- ========================================================================= -->
    @if($activeTab === 'overview')
        <div class="col-12">
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
                                            <td class="text-muted">{{ $req->city }}</td>
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

    <!-- ========================================================================= -->
    <!-- SECTION 2: DELIVERY ORDERS -->
    <!-- ========================================================================= -->
    @if($activeTab === 'delivery-orders')
        @include('transport.partials.delivery-orders')
    @endif

    <!-- ========================================================================= -->
    <!-- SECTION 3: DRIVERS -->
    <!-- ========================================================================= -->
    @if($activeTab === 'drivers')
        @include('transport.partials.driver-master')
    @endif

    <!-- ========================================================================= -->
    <!-- SECTION 4: VEHICLES -->
    <!-- ========================================================================= -->
    @if($activeTab === 'vehicles')
        @include('transport.partials.vehicle-master')
    @endif

</div>
@endsection
