@extends('layouts.app')

@section('title', 'Operational WMS Reports - Organize Stock Portal')

@section('header', 'Warehouse Operational Reports')
@section('subheader', 'Facility occupancy metrics, storage bin utilization, put-away velocity, and internal movement audit logs.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Operational Reports</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Operational Reports Workspace -->
    <div class="col-12 col-lg-9">
        <!-- Metric Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Total Facilities</div>
                    <div class="fs-3 fw-bold text-body mt-1">{{ $metrics['total_warehouses'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Active WMS Hubs</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Storage Bin Occupancy</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ $metrics['occupied_bins'] }} / {{ $metrics['total_bins'] }}</div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Active storage slots</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Put-Away Success Rate</div>
                    <div class="fs-3 fw-bold text-success mt-1">
                        {{ $metrics['total_putaways'] > 0 ? round(($metrics['completed_putaways'] / $metrics['total_putaways']) * 100) : 100 }}%
                    </div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Completed assignments</span>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Picking Fulfillment Rate</div>
                    <div class="fs-3 fw-bold text-info mt-1">
                        {{ $metrics['total_picks'] > 0 ? round(($metrics['completed_picks'] / $metrics['total_picks']) * 100) : 100 }}%
                    </div>
                    <span class="text-muted small" style="font-size: 0.75rem;">Completed pick tasks</span>
                </div>
            </div>
        </div>

        <!-- Facility Occupancy Utilization Breakdown Table -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <h5 class="fw-bold text-body mb-3">Facility Infrastructure & Occupancy Utilization Report</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Warehouse Facility</th>
                            <th>Code / Type</th>
                            <th>Total Capacity</th>
                            <th>Occupied Capacity</th>
                            <th>Occupancy Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warehouses as $wh)
                        <tr>
                            <td>
                                <strong class="text-body fs-6">{{ $wh->name }}</strong>
                                <div class="text-muted small">{{ $wh->city ?? 'Main Warehouse' }}</div>
                            </td>
                            <td>
                                <code>{{ $wh->code }}</code>
                                <div class="text-muted small">{{ ucfirst(str_replace('_', ' ', $wh->type ?? 'Storage')) }}</div>
                            </td>
                            <td class="fw-semibold">{{ number_format($wh->total_capacity) }} {{ $wh->capacity_unit ?? 'sqft' }}</td>
                            <td class="fw-semibold">{{ number_format($wh->occupied_capacity) }} {{ $wh->capacity_unit ?? 'sqft' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1 rounded-pill" style="height: 8px;">
                                        <div class="progress-bar {{ $wh->occupancy_percentage >= 80 ? 'bg-danger' : ($wh->occupancy_percentage >= 50 ? 'bg-warning' : 'bg-success') }}" role="progressbar" style="width: {{ min($wh->occupancy_percentage, 100) }}%;"></div>
                                    </div>
                                    <strong class="small {{ $wh->occupancy_percentage >= 80 ? 'text-danger' : 'text-success' }}">{{ $wh->occupancy_percentage }}%</strong>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success">Healthy</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Internal Movement Audit Log -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Recent Stock Transfer Movement Log</h5>
            @if($transfers->isEmpty())
                <x-empty-state title="No Recent Transfers" message="No internal inventory transfers recorded recently." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead>
                            <tr class="text-muted">
                                <th>Transfer Code</th>
                                <th>Item Transferred</th>
                                <th>Source &rarr; Destination</th>
                                <th>Qty</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $tr)
                            <tr>
                                <td><code>{{ $tr->transfer_number }}</code></td>
                                <td class="fw-bold">{{ $tr->product->name ?? 'Product' }}</td>
                                <td>{{ $tr->fromWarehouse->code ?? 'WH1' }} &rarr; {{ $tr->toWarehouse->code ?? 'WH2' }}</td>
                                <td class="fw-bold text-primary">{{ $tr->quantity }}</td>
                                <td><span class="badge bg-success-subtle text-success">Completed</span></td>
                                <td class="text-muted">{{ $tr->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
