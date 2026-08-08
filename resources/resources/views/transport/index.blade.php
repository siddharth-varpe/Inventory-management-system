@extends('layouts.app')

@section('title', 'Transport Department - Digital Handover & Control Tower v3.0')

@section('header', 'Transport Department - Fleet Control Tower')
@section('subheader', 'Digital Handover, 9-Point Checklist, Permanent Manifests (MAN-YYYY-XXXXXX), and Trip Departure')

@section('content')
<div class="row g-4">

    <!-- Header Controls & Fleet Resource Bar -->
    <div class="col-12">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-danger text-white font-monospace px-3 py-1.5 rounded-pill fs-7">TRANSPORT HANDOVER ENGINE v3.0</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace fs-7 d-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px;" role="status"></span>
                            🟢 Live Sync Active
                        </span>
                    </div>
                    <h3 class="fw-black text-body mb-0">Transport Control Tower</h3>
                    <p class="text-muted small mb-0 mt-1">Official Digital Handover from Warehouse, Mandatory Dispatch Checklist, Immutable Manifests (MAN-YYYY-XXXXXX), and Departure Execution.</p>
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
                </div>
            </div>

            <!-- Status Filter Pills -->
            <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top border-translucent">
                <a href="{{ route('transport.index', ['status' => 'all', 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}" class="btn btn-sm rounded-pill px-3 fw-semibold {{ !request('status') || request('status') === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All Orders
                </a>
                <a href="{{ route('transport.index', ['status' => 'waiting_planning', 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}" class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'waiting_planning' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">
                    Waiting Planning
                </a>
                <a href="{{ route('transport.index', ['status' => 'planning_completed', 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}" class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'planning_completed' ? 'btn-purple text-white' : 'btn-outline-secondary' }}" style="background-color: #a855f7;">
                    Trip Planned
                </a>
                <a href="{{ route('transport.index', ['status' => 'accepted_by_transport', 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}" class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'accepted_by_transport' ? 'btn-teal text-white' : 'btn-outline-secondary' }}" style="background-color: #0d9488;">
                    Accepted By Transport
                </a>
                <a href="{{ route('transport.index', ['status' => 'ready', 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}" class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'ready' ? 'btn-info text-white' : 'btn-outline-secondary' }}">
                    Ready For Dispatch
                </a>
                <a href="{{ route('transport.index', ['status' => 'in_transit', 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}" class="btn btn-sm rounded-pill px-3 fw-semibold {{ request('status') === 'in_transit' ? 'btn-success text-white' : 'btn-outline-secondary' }}">
                    In Transit
                </a>
            </div>

            <!-- Enhanced Search & Filter Form -->
            <form method="GET" action="{{ route('transport.index') }}" class="mt-3 pt-3 border-top border-translucent">
                <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                <div class="row g-3 align-items-center">
                    <!-- Search Input -->
                    <div class="col-md-5">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary border-translucent text-muted px-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                            </span>
                            <input type="text" name="search" class="form-control bg-body-tertiary border-translucent text-body" placeholder="Search Manifest ID (MAN-...), Trip ID (TRIP-...), Order ID, Task ID, Vehicle, Driver, City..." value="{{ request('search') }}">
                        </div>
                    </div>

                    <!-- Priority Dropdown Filter -->
                    <div class="col-md-3">
                        <select name="priority" class="form-select form-select-sm bg-body-tertiary border-translucent text-body rounded-3" onchange="this.form.submit()">
                            <option value="all" {{ !request('priority') || request('priority') === 'all' ? 'selected' : '' }}>All Priorities</option>
                            <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>🔴 Urgent Priority</option>
                            <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>🟡 High Priority</option>
                            <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>🔵 Normal Priority</option>
                            <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>⚪ Low Priority</option>
                        </select>
                    </div>

                    <!-- Destination City Dropdown Filter -->
                    <div class="col-md-3">
                        <select name="city" class="form-select form-select-sm bg-body-tertiary border-translucent text-body rounded-3" onchange="this.form.submit()">
                            <option value="all" {{ !request('city') || request('city') === 'all' ? 'selected' : '' }}>All Destination Cities</option>
                            @foreach($availableCities as $c)
                                <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>📍 {{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-primary w-100 rounded-3">Filter</button>
                    </div>
                </div>
            </form>
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

    <!-- STEP 6: ACTIVE DISPATCHED TRIPS PANEL -->
    @if($activeTrips->isNotEmpty())
        <div class="col-12">
            <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-translucent pb-2">
                    <div>
                        <h5 class="fw-bold text-body mb-0">🚀 Active Dispatched Trips (In Transit)</h5>
                        <span class="small text-muted">Orders currently in transit under official Transport Department custody & Driver Terminal synchronization</span>
                    </div>
                    <span class="badge bg-success font-monospace px-3 py-1 rounded-pill small">{{ $activeTrips->count() }} Trips Active</span>
                </div>

                <div class="row g-3">
                    @foreach($activeTrips as $trip)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="card p-3 rounded-3 border-translucent bg-body-tertiary h-100 position-relative">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-danger text-white font-monospace fs-6 px-2.5 py-1 rounded-pill">{{ $trip->trip_number }}</span>
                                    <span class="badge bg-purple text-white font-monospace px-2.5 py-1 rounded-pill" style="background-color: #a855f7;">
                                        {{ $trip->dispatchManifest->manifest_number ?? 'Manifest Issued' }}
                                    </span>
                                </div>

                                <div class="small mb-1">
                                    <strong>Vehicle:</strong> {{ $trip->vehicle->vehicle_number ?? 'N/A' }} ({{ $trip->vehicle->vehicle_type ?? 'N/A' }})
                                </div>
                                <div class="small mb-2">
                                    <strong>Driver:</strong> {{ $trip->driver->driver_name ?? 'N/A' }} ({{ $trip->driver->phone_number ?? 'N/A' }})
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

                                <div class="d-flex justify-content-between text-muted small" style="font-size: 0.75rem;">
                                    <span>Destination: <strong class="text-body">{{ $trip->destination_city }}</strong></span>
                                    <span>Dispatched: <strong class="text-body font-monospace">{{ $trip->dispatched_at ? $trip->dispatched_at->format('H:i, d M') : 'Just now' }}</strong></span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Master-Detail Workspace: Dispatch Queue & Execution Canvas -->
    <div class="col-12">
        <x-master-detail-layout queueTitle="DISPATCH CONTROL QUEUE" queueSubtitle="Digital Handover & Departure Execution Workspace">
            <x-slot:queueContent>
                @if($requests->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="fs-2 mb-1">🚚</div>
                        <div class="fw-bold">No transport tasks found.</div>
                        <div class="small text-muted">Tasks automatically populate when the warehouse completes Pick & Pack and seals an order.</div>
                    </div>
                @else
                    @foreach($requests as $r)
                        <div class="card p-3 rounded-4 mb-2 shadow-sm border-translucent {{ ($selectedTask->id ?? 0) === $r->id ? 'border-primary bg-primary-subtle' : 'bg-body' }}" style="cursor: pointer;" onclick="window.location.href='{{ route('transport.index', ['task_id' => $r->id, 'status' => request('status'), 'priority' => request('priority'), 'city' => request('city'), 'search' => request('search')]) }}'">
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

                    <!-- Extended Transport Task Profile Card -->
                    <div class="card p-3 rounded-4 shadow-sm border-translucent mb-4 bg-body-tertiary">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <div class="text-muted small fw-semibold">Delivery Address & Destination</div>
                                <div class="fw-bold text-body fs-6 mb-1">{{ $selectedTask->delivery_address ?? 'Primary Customer Address' }}</div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 small">📍 {{ $selectedTask->city }}</span>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted small fw-semibold">Package Specifications</div>
                                <div class="fw-bold text-body">
                                    {{ $selectedTask->package_count }} {{ Str::plural('Carton', $selectedTask->package_count) }} | {{ number_format((float)$selectedTask->weight_kg, 1) }} kg | {{ number_format((float)($selectedTask->volume_m3 ?? 0.5), 2) }} m³
                                </div>
                                <div class="small text-muted">Packaging: {{ $selectedTask->package_type ?? 'Sealed Carton' }} ({{ $selectedTask->dimensions ?? '40x30x20 cm' }})</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small fw-semibold">Timestamps & Milestones</div>
                                <div class="small text-muted mb-0.5">Warehouse Sealed: <strong>{{ $selectedTask->warehouse_completed_at ? $selectedTask->warehouse_completed_at->format('d M, H:i') : 'Completed' }}</strong></div>
                                <div class="small text-muted">Transport Accepted: <strong>{{ $selectedTask->accepted_at ? $selectedTask->accepted_at->format('d M, H:i') : 'Pending' }}</strong></div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- PHASE 3 EXECUTION WORKSPACE: DIGITAL HANDOVER, CHECKLIST, MANIFEST, DISPATCH-->
                    <!-- ========================================================================= -->

                    <!-- STEP 1: DIGITAL HANDOVER (TRANSPORT ACCEPTS CUSTODY) -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <div>
                                <h5 class="fw-black text-body mb-0">Step 1: Digital Handover (Transport Accepts Custody)</h5>
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

                    <!-- STEP 2: MANDATORY 9-POINT DISPATCH CHECKLIST -->
                    @if($selectedTask->accepted_at)
                        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-black text-body mb-0">Step 2: Mandatory Dispatch Verification Checklist</h5>
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

                    <!-- STEP 3: DISPATCH MANIFEST (MAN-YYYY-XXXXXX) -->
                    @if($selectedTask->dispatchManifest)
                        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-black text-body mb-0">Step 3: Permanent Dispatch Manifest</h5>
                                    <span class="small text-muted">Immutable official document (MAN-YYYY-XXXXXX) linking Trip, Vehicle, Driver, Orders, and Verification Checklist</span>
                                </div>
                                <span class="badge bg-purple text-white font-monospace px-3 py-1.5 fs-6 rounded-pill" style="background-color: #a855f7;">
                                    {{ $selectedTask->dispatchManifest->manifest_number }}
                                </span>
                            </div>

                            <div class="p-4 bg-body-tertiary rounded-4 border border-translucent">
                                <div class="row g-3">
                                    <div class="col-md-3 border-end border-translucent">
                                        <div class="text-muted small fw-semibold">Manifest ID</div>
                                        <div class="fw-bold text-primary font-monospace fs-5">{{ $selectedTask->dispatchManifest->manifest_number }}</div>
                                    </div>
                                    <div class="col-md-3 border-end border-translucent">
                                        <div class="text-muted small fw-semibold">Associated Trip ID</div>
                                        <div class="fw-bold text-danger font-monospace fs-5">{{ $selectedTask->transportTrip->trip_number ?? 'N/A' }}</div>
                                    </div>
                                    <div class="col-md-3 border-end border-translucent">
                                        <div class="text-muted small fw-semibold">Assigned Fleet Vehicle</div>
                                        <div class="fw-bold text-body fs-6">{{ $selectedTask->vehicle_number }}</div>
                                        <div class="small text-muted">{{ $selectedTask->carrier }}</div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-muted small fw-semibold">Assigned Driver</div>
                                        <div class="fw-bold text-body fs-6">{{ $selectedTask->driver_name }}</div>
                                        <div class="small text-muted">{{ $selectedTask->driver->employee_id ?? 'DRV-8041' }}</div>
                                    </div>

                                    <div class="col-12 pt-3 border-top border-translucent">
                                        <div class="row g-3 text-center">
                                            <div class="col-md-3 border-end border-translucent">
                                                <div class="text-muted small">Total Package Count</div>
                                                <div class="fw-bold text-body fs-6">{{ $selectedTask->dispatchManifest->package_count }} Cartons</div>
                                            </div>
                                            <div class="col-md-3 border-end border-translucent">
                                                <div class="text-muted small">Total Cargo Weight</div>
                                                <div class="fw-bold text-body fs-6">{{ number_format((float)$selectedTask->dispatchManifest->total_weight_kg, 1) }} kg</div>
                                            </div>
                                            <div class="col-md-3 border-end border-translucent">
                                                <div class="text-muted small">Total Cargo Volume</div>
                                                <div class="fw-bold text-body fs-6">{{ number_format((float)$selectedTask->dispatchManifest->total_volume_m3, 2) }} m³</div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="text-muted small">Destination Summary</div>
                                                <div class="fw-bold text-danger fs-6">📍 {{ $selectedTask->dispatchManifest->destination_summary }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 pt-3 border-top border-translucent d-flex align-items-center justify-content-between text-muted small">
                                        <span>Warehouse Supervisor: <strong>{{ $selectedTask->dispatchManifest->warehouse_supervisor_name }}</strong></span>
                                        <span>Transport Coordinator: <strong>{{ $selectedTask->dispatchManifest->creator->name ?? 'Logistics Manager' }}</strong></span>
                                        <span>Document Status: <strong class="text-purple">READ-ONLY PERMANENT MANIFEST</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- STEP 4: DISPATCH TRIP (DEPARTURE EXECUTION) -->
                    @if($selectedTask->accepted_at)
                        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-black text-body mb-0">Step 4: Dispatch Trip Execution (Shipment Departure)</h5>
                                    <span class="small text-muted">Atomic departure execution updating Trip, Order, Warehouse, Vehicle, Driver, and Driver Terminal simultaneously</span>
                                </div>
                            </div>

                            @if($selectedTask->status === 'in_transit' || $selectedTask->transportTrip->status === 'dispatched')
                                <div class="alert alert-success border-0 rounded-3 p-4 text-center mb-0">
                                    <div class="fs-2 mb-1">🚀</div>
                                    <h5 class="fw-bold text-body mb-1">Trip Dispatched & Shipment In Transit</h5>
                                    <p class="small text-muted mb-0">
                                        Trip <strong>{{ $selectedTask->transportTrip->trip_number }}</strong> departed under Manifest <strong>{{ $selectedTask->dispatchManifest->manifest_number ?? 'MAN-2026-000184' }}</strong>.
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
                        <p class="small text-muted mb-0">Select an active order from the left Dispatch Control Queue to open the Digital Handover & Departure Execution Workspace.</p>
                    </div>
                @endif
            </x-slot:canvasContent>
        </x-master-detail-layout>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live Synchronization Polling for Fleet & Queue Status (Every 10 seconds)
    function syncLiveTransportQueue() {
        fetch('{{ route("transport.live-queue") }}')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const elVeh = document.getElementById('cntAvailableVehicles');
                    const elDrv = document.getElementById('cntAvailableDrivers');
                    const elTrp = document.getElementById('cntActiveTrips');

                    if (elVeh) elVeh.innerText = `🚛 ${data.available_vehicles} Available`;
                    if (elDrv) elDrv.innerText = `👤 ${data.available_drivers} Standby`;
                    if (elTrp) elTrp.innerText = `📦 ${data.active_trips} Active`;
                }
            })
            .catch(err => console.log('Live sync catch:', err));
    }
    setInterval(syncLiveTransportQueue, 10000);
});
</script>
@endpush
@endsection
