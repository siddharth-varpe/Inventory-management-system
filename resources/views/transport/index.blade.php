@extends('layouts.app')

@section('title', 'Transport Department - Enterprise TMS Master Blueprint v3.0')

@section('header', 'Transport Department - Enterprise TMS Hub')
@section('subheader', 'Complete Enterprise Transport Management System: Drivers, Vehicles, Trips, Dispatch, Execution, Maintenance & Compliance')

@section('content')
<div class="row g-4">

    <!-- Top Header Strip & Live Sync Status -->
    <div class="col-12">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-danger text-white font-monospace px-3 py-1.5 rounded-pill fs-7">ENTERPRISE TMS MASTER BLUEPRINT v3.0</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace fs-7 d-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px;" role="status"></span>
                            🟢 Enterprise Live Sync Active
                        </span>
                    </div>
                    <h3 class="fw-black text-body mb-0">Enterprise Transport Management System</h3>
                    <p class="text-muted small mb-0 mt-1">Master Data (Drivers & Vehicles), Trip Planning, Dispatch Handover, Active Execution, Maintenance & Compliance.</p>
                </div>

                <!-- Live Fleet Counters Strip -->
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="p-2 px-3 bg-body-tertiary rounded-3 border border-translucent text-center">
                        <div class="text-muted small" style="font-size: 0.7rem;">Available Vehicles</div>
                        <div class="fw-bold text-success fs-6" id="cntAvailableVehicles">🚛 {{ $availableVehicles->count() }} Available</div>
                    </div>
                    <div class="p-2 px-3 bg-body-tertiary rounded-3 border border-translucent text-center">
                        <div class="text-muted small" style="font-size: 0.7rem;">Available Drivers</div>
                        <div class="fw-bold text-primary fs-6" id="cntAvailableDrivers">👤 {{ $availableDrivers->count() }} Standby</div>
                    </div>
                    <div class="p-2 px-3 bg-body-tertiary rounded-3 border border-translucent text-center">
                        <div class="text-muted small" style="font-size: 0.7rem;">Active Dispatched Trips</div>
                        <div class="fw-bold text-warning-emphasis fs-6" id="cntActiveTrips">📦 {{ $activeTrips->count() }} Active</div>
                    </div>
                    <div class="p-2 px-3 bg-body-tertiary rounded-3 border border-translucent text-center">
                        <div class="text-muted small" style="font-size: 0.7rem;">Compliance Alerts</div>
                        <div class="fw-bold text-danger fs-6">⚠️ {{ $complianceAlerts['total_alerts'] ?? 0 }} Alerts</div>
                    </div>
                </div>
            </div>

            <!-- WORKSPACE TABS NAVIGATION BAR (8 WORKSPACES) -->
            <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top border-translucent">
                <a href="{{ route('transport.index', ['tab' => 'dashboard']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'dashboard' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    📊 1. Transport Dashboard
                </a>
                <a href="{{ route('transport.index', ['tab' => 'drivers']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'drivers' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    👤 2. Driver Management ({{ $allDrivers->count() }})
                </a>
                <a href="{{ route('transport.index', ['tab' => 'vehicles']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'vehicles' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    🚛 3. Vehicle Management ({{ $allVehicles->count() }})
                </a>
                <a href="{{ route('transport.index', ['tab' => 'trips']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'trips' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    🗺 4. Trip Management
                </a>
                <a href="{{ route('transport.index', ['tab' => 'dispatch']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'dispatch' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    📦 5. Dispatch Center
                </a>
                <a href="{{ route('transport.index', ['tab' => 'active']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'active' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    🚀 6. Active Trips ({{ $activeTrips->count() }})
                </a>
                <a href="{{ route('transport.index', ['tab' => 'history']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'history' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    📜 7. Trip History Archive
                </a>
                <a href="{{ route('transport.index', ['tab' => 'maintenance']) }}" class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold {{ $activeTab === 'maintenance' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    🔧 8. Maintenance & Compliance ({{ $complianceAlerts['total_alerts'] ?? 0 }})
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
    <!-- WORKSPACE 1: TRANSPORT DASHBOARD -->
    <!-- ========================================================================= -->
    @if($activeTab === 'dashboard')
        <div class="col-12">
            <div class="row g-3">
                <!-- Analytics Metric Cards -->
                <div class="col-md-3">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                        <div class="text-muted small fw-semibold">Trips Closed Today</div>
                        <div class="fs-3 fw-bold text-success mt-1">{{ $analytics['trips_completed_today'] ?? 0 }}</div>
                        <div class="small text-muted">Successfully delivered & closed</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                        <div class="text-muted small fw-semibold">Vehicle Utilization</div>
                        <div class="fs-3 fw-bold text-primary mt-1">{{ $analytics['vehicle_utilization_pct'] ?? 0 }}%</div>
                        <div class="small text-muted">{{ $allVehicles->where('status', 'on_trip')->count() }} of {{ $allVehicles->count() }} active on trip</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                        <div class="text-muted small fw-semibold">Driver Utilization</div>
                        <div class="fs-3 fw-bold text-info mt-1">{{ $analytics['driver_utilization_pct'] ?? 0 }}%</div>
                        <div class="small text-muted">{{ $allDrivers->whereIn('status', ['on_trip', 'on_delivery'])->count() }} of {{ $allDrivers->count() }} active on trip</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                        <div class="text-muted small fw-semibold">Compliance Alerts</div>
                        <div class="fs-3 fw-bold text-danger mt-1">{{ $complianceAlerts['total_alerts'] ?? 0 }}</div>
                        <div class="small text-muted">Expiries & Maintenance Due</div>
                    </div>
                </div>

                <!-- Active Fleet Overview Grid -->
                <div class="col-md-6">
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                        <h5 class="fw-bold text-body mb-3 border-bottom pb-2">🚛 Fleet Vehicle Status Distribution</h5>
                        <div class="row g-2">
                            @foreach($allVehicles as $v)
                                <div class="col-6">
                                    <div class="p-2.5 bg-body-tertiary rounded-3 border border-translucent">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold text-body font-monospace">{{ $v->vehicle_number }}</span>
                                            <span class="badge {{ $v->status_badge_class }}" style="font-size: 0.65rem;">{{ strtoupper($v->status) }}</span>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">{{ $v->vehicle_type }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body h-100">
                        <h5 class="fw-bold text-body mb-3 border-bottom pb-2">👤 Driver Roster & Assignment Status</h5>
                        <div class="row g-2">
                            @foreach($allDrivers as $d)
                                <div class="col-6">
                                    <div class="p-2.5 bg-body-tertiary rounded-3 border border-translucent">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <span class="fw-bold text-body">{{ $d->driver_name }}</span>
                                            <span class="badge {{ $d->status_badge_class }}" style="font-size: 0.65rem;">{{ strtoupper($d->status) }}</span>
                                        </div>
                                        <div class="small text-muted font-monospace" style="font-size: 0.75rem;">{{ $d->driver_code ?? $d->employee_id }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 2: DRIVER MANAGEMENT -->
    <!-- ========================================================================= -->
    @if($activeTab === 'drivers')
        @include('transport.partials.driver-master')
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 3: VEHICLE MANAGEMENT -->
    <!-- ========================================================================= -->
    <!-- ========================================================================= -->
    <!-- WORKSPACE 3: VEHICLE MANAGEMENT (PHASE 2 VEHICLE MASTER) -->
    <!-- ========================================================================= -->
    @if($activeTab === 'vehicles')
        @include('transport.partials.vehicle-master')
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 4: TRIP MANAGEMENT (PLANNING & SCHEDULING) -->
    <!-- ========================================================================= -->
    @if($activeTab === 'trips')
        <div class="col-12">
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                <h5 class="fw-black text-body mb-3 border-bottom pb-2">🗺 Transport Trip Planning & Multi-Order Scheduling</h5>
                <p class="text-muted small mb-4">Trip is the primary operational object (`TRIP-2026-XXXXXX`). Future-proof architecture supports multi-order trips per vehicle journey.</p>

                <div class="row g-3">
                    @foreach($allTrips as $tp)
                        <div class="col-md-6 col-xl-4">
                            <div class="card p-3 rounded-3 border-translucent bg-body-tertiary h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-danger text-white font-monospace fs-6 px-2.5 py-1 rounded-pill">{{ $tp->trip_number }}</span>
                                    <span class="badge {{ $tp->status_badge_class }} px-2.5 py-1">{{ $tp->status_label }}</span>
                                </div>

                                <div class="small mb-1">
                                    <strong>Vehicle:</strong> {{ $tp->vehicle->vehicle_number ?? 'N/A' }} | <strong>Driver:</strong> {{ $tp->driver->driver_name ?? 'N/A' }}
                                </div>
                                <div class="small text-muted mb-2">
                                    Destination: <strong>📍 {{ $tp->destination_city }}</strong> | Created: <strong>{{ $tp->created_at->format('d M, H:i') }}</strong>
                                </div>

                                <div class="p-2 bg-body rounded-2 border border-translucent mb-2 text-center" style="font-size: 0.75rem;">
                                    <div class="row g-1">
                                        <div class="col-4 border-end border-translucent">
                                            <div class="text-muted">Assigned Orders</div>
                                            <div class="fw-bold text-primary">{{ $tp->transportRequests->count() }} Order</div>
                                        </div>
                                        <div class="col-4 border-end border-translucent">
                                            <div class="text-muted">Total Weight</div>
                                            <div class="fw-bold text-body">{{ number_format((float)$tp->total_weight_kg, 1) }} kg</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted">Total Volume</div>
                                            <div class="fw-bold text-body">{{ number_format((float)$tp->total_volume_m3, 2) }} m³</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 pt-2 border-top border-translucent d-flex align-items-center justify-content-between text-muted small">
                                    <span>Manifest: <strong class="text-purple font-monospace">{{ $tp->dispatchManifest->manifest_number ?? 'MAN-Pending' }}</strong></span>
                                    <a href="{{ route('transport.index', ['tab' => 'dispatch', 'search' => $tp->trip_number]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Open Dispatch &rarr;</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 5: DISPATCH CENTER (CUSTODY, CHECKLIST, MANIFEST) -->
    <!-- ========================================================================= -->
    @if($activeTab === 'dispatch')
        <div class="col-12">
            <x-master-detail-layout queueTitle="DISPATCH QUEUE" queueSubtitle="Digital Handover, 9-Point Checklist, Permanent Manifests & Departure Execution">
                <x-slot:queueContent>
                    @if($requests->isEmpty())
                        <div class="text-center text-muted py-5">
                            <div class="fs-2 mb-1">🚚</div>
                            <div class="fw-bold">No transport tasks found.</div>
                            <div class="small text-muted">Tasks automatically populate when the warehouse completes Pick & Pack and seals an order.</div>
                        </div>
                    @else
                        @foreach($requests as $r)
                            <div class="card p-3 rounded-4 mb-2 shadow-sm border-translucent {{ ($selectedTask->id ?? 0) === $r->id ? 'border-primary bg-primary-subtle' : 'bg-body' }}" style="cursor: pointer;" onclick="window.location.href='{{ route('transport.index', ['tab' => 'dispatch', 'task_id' => $r->id, 'status' => request('status'), 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}'">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span class="fw-bold font-monospace text-primary small">{{ $r->order_reference }}</span>
                                    <span class="badge {{ $r->priority_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ strtoupper($r->priority) }}
                                    </span>
                                </div>
                                
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="fw-bold text-body small">{{ $r->customer_name }}</div>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.65rem;">{{ $r->request_number }}</span>
                                </div>

                                <div class="d-flex align-items-center justify-content-between text-muted mt-2 pt-2 border-top border-translucent" style="font-size: 0.75rem;">
                                    <span>📍 {{ $r->city }}</span>
                                    <span>📦 {{ $r->package_count }} Cartons ({{ $r->weight_kg ? number_format((float)$r->weight_kg, 1) . 'kg' : 'N/A' }})</span>
                                </div>

                                @if($r->dispatchManifest)
                                    <div class="mt-2 pt-1 border-top border-translucent d-flex align-items-center justify-content-between">
                                        <span class="badge bg-purple text-white font-monospace" style="font-size: 0.65rem; background-color: #a855f7;">{{ $r->dispatchManifest->manifest_number }}</span>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">Manifest Locked</span>
                                    </div>
                                @endif

                                <div class="mt-2 text-end">
                                    <span class="badge {{ $r->status_badge_class }} rounded-pill px-2 py-1" style="font-size: 0.65rem;">
                                        {{ $r->status_label }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </x-slot:queueContent>

                <x-slot:canvasContent>
                    @if($selectedTask)
                        <!-- Canvas Header -->
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 border-bottom pb-3">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="badge bg-danger font-monospace fs-6 px-2.5 py-1 rounded-pill">{{ $selectedTask->request_number }}</span>
                                    <span class="badge {{ $selectedTask->priority_badge_class }} rounded-pill px-2.5 py-1">{{ strtoupper($selectedTask->priority) }}</span>
                                    <span class="badge {{ $selectedTask->status_badge_class }} rounded-pill px-3 py-1 fs-7">
                                        {{ $selectedTask->status_label }}
                                    </span>
                                </div>
                                <h3 class="fw-bold text-body mb-0">Sales Order {{ $selectedTask->order_reference }}</h3>
                                <div class="text-muted small mt-1">
                                    Customer: <strong>{{ $selectedTask->customer_name }}</strong> | Destination: <strong>{{ $selectedTask->city }}</strong>
                                </div>
                            </div>

                            <div class="text-end">
                                @if($selectedTask->dispatchManifest)
                                    <div class="text-muted small fw-semibold">Permanent Dispatch Manifest</div>
                                    <span class="badge bg-purple text-white font-monospace fs-5 px-3 py-1.5 rounded-pill" style="background-color: #a855f7;">{{ $selectedTask->dispatchManifest->manifest_number }}</span>
                                @elseif($selectedTask->transportTrip)
                                    <div class="text-muted small fw-semibold">Assigned Transport Trip</div>
                                    <span class="badge bg-danger text-white font-monospace fs-5 px-3 py-1.5 rounded-pill">{{ $selectedTask->transportTrip->trip_number }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Step 1: Vehicle & Driver Planning Form -->
                        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                            <h5 class="fw-black text-body mb-3 border-bottom pb-2">Step 1: Vehicle & Driver Planning</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <form method="POST" action="{{ route('transport.assign-vehicle', $selectedTask->id) }}">
                                        @csrf
                                        <label class="form-label small fw-semibold">Assign Vehicle *</label>
                                        <div class="input-group">
                                            <select name="vehicle_id" class="form-select" required>
                                                <option value="" disabled selected>-- Select Vehicle --</option>
                                                @foreach($availableVehicles as $v)
                                                    <option value="{{ $v->id }}" {{ $selectedTask->vehicle_id === $v->id ? 'selected' : '' }}>
                                                        🚛 {{ $v->vehicle_number }} ({{ $v->vehicle_type }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary fw-bold">Assign</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-md-6">
                                    <form method="POST" action="{{ route('transport.assign-driver', $selectedTask->id) }}">
                                        @csrf
                                        <label class="form-label small fw-semibold">Assign Driver *</label>
                                        <div class="input-group">
                                            <select name="driver_id" class="form-select" required>
                                                <option value="" disabled selected>-- Select Driver --</option>
                                                @foreach($availableDrivers as $d)
                                                    <option value="{{ $d->id }}" {{ $selectedTask->driver_id === $d->id ? 'selected' : '' }}>
                                                        👤 {{ $d->driver_name }} ({{ $d->driver_code ?? $d->employee_id }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-primary fw-bold">Assign</button>
                                        </div>
                                    </form>
                                </div>

                                @if($selectedTask->vehicle_id && $selectedTask->driver_id && !$selectedTask->transport_trip_id)
                                    <div class="col-12 mt-3 text-end border-top pt-3">
                                        <form method="POST" action="{{ route('transport.create-trip', $selectedTask->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success fw-bold px-4 rounded-3 text-white">
                                                🗺 Create Transport Trip &rarr;
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Step 2: Digital Handover (Custody Acceptance) -->
                        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-black text-body mb-0">Step 2: Digital Handover (Transport Accepts Custody)</h5>
                                    <span class="small text-muted">Legal and operational transition point transferring inventory ownership from Warehouse to Transport</span>
                                </div>
                                @if($selectedTask->accepted_at)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace">✔ Custody Accepted</span>
                                @endif
                            </div>

                            @if($selectedTask->accepted_at)
                                <div class="alert alert-success border-0 rounded-3 p-3 mb-0 d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="fw-bold fs-6">✔ Custody Accepted by Transport Department</div>
                                        <div class="small text-muted">Accepted By: <strong>{{ $selectedTask->acceptedByUser->name ?? 'Transport Coordinator' }}</strong> | Department: <strong>{{ $selectedTask->acceptance_department }}</strong> | Time: <strong>{{ $selectedTask->accepted_at->format('d M Y, H:i:s') }}</strong></div>
                                    </div>
                                    <span class="badge bg-success text-white font-monospace px-3 py-1 rounded-pill">OWNERSHIP TRANSFERRED</span>
                                </div>
                            @else
                                <form method="POST" action="{{ route('transport.accept-custody', $selectedTask->id) }}">
                                    @csrf
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-8">
                                            <div class="small text-muted">
                                                Pre-checks verified: Warehouse Status (Seal & Ready), Package Count Matching, Trip Planning Completed.
                                                Clicking accept officially confirms custody receipt in the Transport Department log.
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            @if($selectedTask->transport_trip_id && $selectedTask->vehicle_id && $selectedTask->driver_id)
                                                <button type="submit" class="btn btn-teal text-white w-100 rounded-3 py-2.5 fw-bold shadow-sm" style="background-color: #0d9488;">
                                                    🤝 Accept Shipment Custody &rarr;
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-secondary w-100 rounded-3 py-2.5 fw-bold" disabled>
                                                    🔒 Complete Vehicle & Driver Planning First
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </form>
                            @endif
                        </div>

                        <!-- Step 3: Mandatory 9-Point Verification Checklist & Manifest -->
                        @if($selectedTask->accepted_at)
                            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                    <div>
                                        <h5 class="fw-black text-body mb-0">Step 3: Mandatory Dispatch Verification Checklist</h5>
                                        <span class="small text-muted">All 9 mandatory verification items must be confirmed before dispatching or issuing manifest</span>
                                    </div>
                                    @if($selectedTask->dispatchChecklist && $selectedTask->dispatchChecklist->is_completed)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace">✔ 9/9 Verified</span>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('transport.update-checklist', $selectedTask->id) }}">
                                    @csrf
                                    @php
                                        $chk = $selectedTask->dispatchChecklist;
                                        $isLocked = $chk && $chk->is_completed;
                                    @endphp

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="vehicle_inspected" value="1" id="chk1" {{ ($chk && $chk->vehicle_inspected) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk1">1. Vehicle Inspection</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="packages_loaded" value="1" id="chk2" {{ ($chk && $chk->packages_loaded) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk2">2. Packages Loaded</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="package_count_verified" value="1" id="chk3" {{ ($chk && $chk->package_count_verified) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk3">3. Package Count Verified</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="labels_verified" value="1" id="chk4" {{ ($chk && $chk->labels_verified) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk4">4. Shipping Labels Verified</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="delivery_documents_verified" value="1" id="chk5" {{ ($chk && $chk->delivery_documents_verified) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk5">5. Delivery Docs Verified</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="vehicle_doors_sealed" value="1" id="chk6" {{ ($chk && $chk->vehicle_doors_sealed) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk6">6. Vehicle Doors Sealed</label>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="driver_documents_verified" value="1" id="chk7" {{ ($chk && $chk->driver_documents_verified) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk7">7. Driver License Verified</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="loading_completed" value="1" id="chk8" {{ ($chk && $chk->loading_completed) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk8">8. Cargo Loading Complete</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" name="supervisor_approved" value="1" id="chk9" {{ ($chk && $chk->supervisor_approved) ? 'checked' : '' }} {{ $isLocked ? 'disabled' : '' }}>
                                                <label class="form-check-label fw-semibold text-body small" for="chk9">9. Supervisor Approval</label>
                                            </div>
                                        </div>
                                    </div>

                                    @if(!$isLocked)
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold">
                                                Save & Verify Dispatch Checklist &rarr;
                                            </button>
                                        </div>
                                    @endif
                                </form>
                            </div>
                        @endif

                        <!-- Step 4: Dispatch Execution -->
                        @if($selectedTask->accepted_at)
                            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                    <div>
                                        <h5 class="fw-black text-body mb-0">Step 4: Dispatch Trip Execution (Departure)</h5>
                                        <span class="small text-muted">Atomic departure execution updating Trip, Order, Warehouse, Vehicle, Driver, and Driver Terminal</span>
                                    </div>
                                </div>

                                @if(in_array($selectedTask->status, ['in_transit', 'dispatched']))
                                    <div class="alert alert-success border-0 rounded-3 p-4 text-center mb-0">
                                        <div class="fs-2 mb-1">🚀</div>
                                        <h5 class="fw-bold text-body mb-1">Trip Dispatched & Shipment In Transit</h5>
                                        <p class="small text-muted mb-0">
                                            Trip <strong>{{ $selectedTask->transportTrip->trip_number }}</strong> departed under Manifest <strong>{{ $selectedTask->dispatchManifest->manifest_number ?? 'MAN-Locked' }}</strong>.
                                            Vehicle <strong>{{ $selectedTask->vehicle_number }}</strong> and Driver <strong>{{ $selectedTask->driver_name }}</strong> are in transit. Driver Terminal activated.
                                        </p>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('transport.dispatch-trip', $selectedTask->id) }}">
                                        @csrf
                                        <div class="row g-3 align-items-center">
                                            <div class="col-md-8">
                                                <div class="small text-muted">
                                                    Final departure execution requires: Transport Custody Accepted, Mandatory 9-Point Checklist Verified, Dispatch Manifest Issued.
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                @if($selectedTask->accepted_at && $selectedTask->dispatchChecklist && $selectedTask->dispatchChecklist->is_completed && $selectedTask->dispatch_manifest_id)
                                                    <button type="submit" class="btn btn-success w-100 rounded-3 py-2.5 fw-bold text-white shadow-sm">
                                                        🚀 Dispatch Trip (Departure Execution) &rarr;
                                                    </button>
                                                @else
                                                    <button type="button" class="btn btn-secondary w-100 rounded-3 py-2.5 fw-bold" disabled>
                                                        🔒 Complete Checklist & Manifest First
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @endif

                    @else
                        <div class="text-center text-muted py-5">
                            <div class="fs-1 mb-2">🚚</div>
                            <h5>Select a Transport Task</h5>
                            <p class="small text-muted mb-0">Select an active order from the left Dispatch Control Queue to open the Control Tower Workspace.</p>
                        </div>
                    @endif
                </x-slot:canvasContent>
            </x-master-detail-layout>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 6: ACTIVE TRIPS -->
    <!-- ========================================================================= -->
    @if($activeTab === 'active')
        <div class="col-12">
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h5 class="fw-black text-body mb-0">🚀 Active Dispatched Trips (In Transit)</h5>
                        <span class="small text-muted">Trips currently in transit under official Transport Department custody & Driver Terminal synchronization.</span>
                    </div>
                    <span class="badge bg-success font-monospace px-3 py-1.5 rounded-pill fs-7">{{ $activeTrips->count() }} Trips Active</span>
                </div>

                <div class="row g-3">
                    @foreach($activeTrips as $trip)
                        <div class="col-md-6 col-xl-4">
                            <div class="card p-3 rounded-3 border-translucent bg-body-tertiary h-100">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-danger text-white font-monospace fs-6 px-2.5 py-1 rounded-pill">{{ $trip->trip_number }}</span>
                                    <span class="badge bg-purple text-white font-monospace px-2.5 py-1 rounded-pill" style="background-color: #a855f7;">
                                        {{ $trip->dispatchManifest->manifest_number ?? 'Manifest' }}
                                    </span>
                                </div>

                                <div class="small mb-1">
                                    <strong>Vehicle:</strong> {{ $trip->vehicle->vehicle_number ?? 'N/A' }} | <strong>Driver:</strong> {{ $trip->driver->driver_name ?? 'N/A' }}
                                </div>
                                <div class="small text-muted mb-2">
                                    Destination: <strong>📍 {{ $trip->destination_city }}</strong> | Dispatched: <strong>{{ $trip->dispatched_at ? $trip->dispatched_at->format('H:i, d M') : 'Just now' }}</strong>
                                </div>

                                <div class="p-2 bg-body rounded-2 border border-translucent mb-2 text-center" style="font-size: 0.75rem;">
                                    <div class="row g-1">
                                        <div class="col-4 border-end border-translucent">
                                            <div class="text-muted">Assigned Orders</div>
                                            <div class="fw-bold text-primary">{{ $trip->transportRequests->count() }} Order</div>
                                        </div>
                                        <div class="col-4 border-end border-translucent">
                                            <div class="text-muted">Total Weight</div>
                                            <div class="fw-bold text-body">{{ number_format((float)$trip->total_weight_kg, 1) }} kg</div>
                                        </div>
                                        <div class="col-4">
                                            <div class="text-muted">Total Volume</div>
                                            <div class="fw-bold text-body">{{ number_format((float)$trip->total_volume_m3, 2) }} m³</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 text-end">
                                    <a href="{{ route('driver.index', ['driver_id' => $trip->driver_id, 'trip_id' => $trip->id]) }}" class="btn btn-sm btn-cyan text-white rounded-pill px-3" style="background-color: #06b6d4;">
                                        📲 Track in Driver Terminal &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 7: TRIP HISTORY ARCHIVE -->
    <!-- ========================================================================= -->
    @if($activeTab === 'history')
        <div class="col-12">
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h5 class="fw-black text-body mb-0">📜 Permanent Closed Trip History Archive</h5>
                        <span class="small text-muted">Immutable read-only log of closed trips, manifests, timestamps, vehicle & driver records.</span>
                    </div>
                    <span class="badge bg-secondary font-monospace px-3 py-1.5 rounded-pill fs-7">{{ $archivedTrips->count() }} Closed Trips</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 border-translucent">
                        <thead class="bg-body-tertiary text-muted small text-uppercase">
                            <tr>
                                <th>Trip ID</th>
                                <th>Manifest ID</th>
                                <th>Destination</th>
                                <th>Vehicle & Driver</th>
                                <th>Dispatch Time</th>
                                <th>Closure Time</th>
                                <th>Closed By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($archivedTrips as $at)
                                <tr>
                                    <td><span class="badge bg-danger text-white font-monospace">{{ $at->trip_number }}</span></td>
                                    <td><span class="badge bg-purple text-white font-monospace" style="background-color: #a855f7;">{{ $at->dispatchManifest->manifest_number ?? 'MAN-Closed' }}</span></td>
                                    <td><strong class="text-body">📍 {{ $at->destination_city }}</strong></td>
                                    <td class="small">
                                        <div>🚛 {{ $at->vehicle->vehicle_number ?? 'N/A' }}</div>
                                        <div class="text-muted">👤 {{ $at->driver->driver_name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="small font-monospace">{{ $at->dispatched_at ? $at->dispatched_at->format('d M Y, H:i') : 'N/A' }}</td>
                                    <td class="small font-monospace text-success">{{ $at->closed_at ? $at->closed_at->format('d M Y, H:i') : 'Closed' }}</td>
                                    <td class="small text-muted">{{ $at->closedByUser->name ?? 'Coordinator' }}</td>
                                    <td><span class="badge bg-secondary text-white font-monospace">CLOSED ARCHIVE</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- WORKSPACE 8: MAINTENANCE & COMPLIANCE -->
    <!-- ========================================================================= -->
    @if($activeTab === 'maintenance')
        <div class="col-12">
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <h5 class="fw-black text-body mb-0">🔧 Fleet Maintenance & Regulatory Compliance Center</h5>
                        <span class="small text-muted">Track vehicle service schedules, document expiries (Insurance, Fitness, PUC, RC), and driver license renewals.</span>
                    </div>
                    <span class="badge bg-danger font-monospace px-3 py-1.5 rounded-pill fs-7">⚠️ {{ $complianceAlerts['total_alerts'] }} Compliance Alerts</span>
                </div>

                <div class="row g-4">
                    <!-- Expiring Driver Licenses -->
                    <div class="col-md-6">
                        <div class="card p-3 rounded-4 border-translucent bg-body-tertiary h-100">
                            <h6 class="fw-bold text-body mb-2 border-bottom pb-2">👤 Expiring Driver Licenses (< 30 Days)</h6>
                            @if($complianceAlerts['expiring_licenses']->isEmpty())
                                <div class="text-success small">✔ All driver licenses are valid and up to date.</div>
                            @else
                                @foreach($complianceAlerts['expiring_licenses'] as $el)
                                    <div class="p-2 bg-body rounded-3 border border-translucent mb-2 small d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-body">{{ $el->driver_name }}</strong> ({{ $el->driver_code ?? $el->employee_id }})
                                            <div class="text-muted" style="font-size: 0.7rem;">License: {{ $el->driving_license_number }}</div>
                                        </div>
                                        <span class="badge bg-danger text-white">Expires: {{ $el->license_expiry_date ? $el->license_expiry_date->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Expiring Vehicle Insurance & Permits -->
                    <div class="col-md-6">
                        <div class="card p-3 rounded-4 border-translucent bg-body-tertiary h-100">
                            <h6 class="fw-bold text-body mb-2 border-bottom pb-2">🚛 Vehicle Insurance Expiries (< 30 Days)</h6>
                            @if($complianceAlerts['expiring_insurance']->isEmpty())
                                <div class="text-success small">✔ All vehicle insurance policies are valid.</div>
                            @else
                                @foreach($complianceAlerts['expiring_insurance'] as $ei)
                                    <div class="p-2 bg-body rounded-3 border border-translucent mb-2 small d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-body font-monospace">{{ $ei->vehicle_number }}</strong> ({{ $ei->vehicle_type }})
                                            <div class="text-muted" style="font-size: 0.7rem;">Policy: {{ $ei->insurance_policy_number }}</div>
                                        </div>
                                        <span class="badge bg-warning text-dark">Expires: {{ $ei->insurance_expiry_date ? $ei->insurance_expiry_date->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Fitness Expiries -->
                    <div class="col-md-6">
                        <div class="card p-3 rounded-4 border-translucent bg-body-tertiary h-100">
                            <h6 class="fw-bold text-body mb-2 border-bottom pb-2">📜 Fitness & PUC Expiries (< 30 Days)</h6>
                            @if($complianceAlerts['expiring_fitness']->isEmpty() && $complianceAlerts['expiring_puc']->isEmpty())
                                <div class="text-success small">✔ All vehicle fitness & PUC certificates are valid.</div>
                            @else
                                @foreach($complianceAlerts['expiring_fitness'] as $ef)
                                    <div class="p-2 bg-body rounded-3 border border-translucent mb-2 small d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-body font-monospace">{{ $ef->vehicle_number }}</strong> - Fitness Cert
                                        </div>
                                        <span class="badge bg-danger text-white">Expires: {{ $ef->fitness_expiry_date ? $ef->fitness_expiry_date->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                @endforeach
                                @foreach($complianceAlerts['expiring_puc'] as $ep)
                                    <div class="p-2 bg-body rounded-3 border border-translucent mb-2 small d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-body font-monospace">{{ $ep->vehicle_number }}</strong> - PUC Cert
                                        </div>
                                        <span class="badge bg-warning text-dark">Expires: {{ $ep->puc_expiry_date ? $ep->puc_expiry_date->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    <!-- Vehicle Service & Maintenance Schedule -->
                    <div class="col-md-6">
                        <div class="card p-3 rounded-4 border-translucent bg-body-tertiary h-100">
                            <h6 class="fw-bold text-body mb-2 border-bottom pb-2">🔧 Maintenance & Service Schedule</h6>
                            @if($complianceAlerts['maintenance_due']->isEmpty())
                                <div class="text-success small">✔ All vehicles are serviced and in good operational condition.</div>
                            @else
                                @foreach($complianceAlerts['maintenance_due'] as $md)
                                    <div class="p-2 bg-body rounded-3 border border-translucent mb-2 small d-flex align-items-center justify-content-between">
                                        <div>
                                            <strong class="text-body font-monospace">{{ $md->vehicle_number }}</strong> ({{ $md->maintenance_status }})
                                            <div class="text-muted" style="font-size: 0.7rem;">Odometer: {{ number_format((int)$md->current_odometer_km) }} km</div>
                                        </div>
                                        <span class="badge bg-danger text-white">Due: {{ $md->next_service_due_date ? $md->next_service_due_date->format('d M Y') : 'Immediate' }}</span>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    function syncLiveTransportQueue() {
        fetch('{{ route("transport.live-queue") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const elVeh = document.getElementById('cntAvailableVehicles');
                    const elDrv = document.getElementById('cntAvailableDrivers');
                    const elTrp = document.getElementById('cntActiveTrips');
                    const elPen = document.getElementById('cntPendingClosure');

                    if (elVeh) elVeh.innerText = `🚛 ${data.available_vehicles} Available`;
                    if (elDrv) elDrv.innerText = `👤 ${data.available_drivers} Standby`;
                    if (elTrp) elTrp.innerText = `📦 ${data.active_trips} Active`;
                    if (elPen) elPen.innerText = `⏳ ${data.pending_closure ?? 0} Review`;
                }
            })
            .catch(err => console.log('Live sync catch:', err));
    }
    setInterval(syncLiveTransportQueue, 10000);
});
</script>
@endpush
@endsection
