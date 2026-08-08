@extends('layouts.app')

@section('title', 'Driver Terminal - Last-Mile Delivery Execution v4.0')

@section('header', 'Driver Terminal - Delivery Execution Workspace')
@section('subheader', 'Execute assigned transport trips, update delivery checkpoints, and confirm completed deliveries')

@section('content')
<div class="row g-4">

    <!-- Header Controls & Driver Context Switcher -->
    <div class="col-12">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-cyan text-white font-monospace px-3 py-1.5 rounded-pill fs-7" style="background-color: #06b6d4;">DRIVER TERMINAL v4.0</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 font-monospace fs-7 d-flex align-items-center gap-1">
                            <span class="spinner-grow spinner-grow-sm text-success" style="width: 6px; height: 6px;" role="status"></span>
                            🟢 Live Dispatch Sync Active
                        </span>
                    </div>
                    <h3 class="fw-black text-body mb-0">Driver Execution Terminal</h3>
                    <p class="text-muted small mb-0 mt-1">Focused last-mile delivery execution workspace for assigned fleet drivers.</p>
                </div>

                <!-- Driver Context Switcher Dropdown -->
                <form method="GET" action="{{ route('driver.index') }}" class="d-flex align-items-center gap-2">
                    <label class="form-label mb-0 small text-muted text-nowrap fw-semibold">Active Driver Context:</label>
                    <select name="driver_id" class="form-select form-select-sm bg-body-tertiary border-translucent text-body rounded-3 fw-bold" onchange="this.form.submit()">
                        @foreach($allDrivers as $d)
                            <option value="{{ $d->id }}" {{ ($currentDriver->id ?? 0) === $d->id ? 'selected' : '' }}>
                                👤 {{ $d->driver_name }} ({{ $d->employee_id }}) - {{ strtoupper($d->status) }}
                            </option>
                        @endforeach
                    </select>
                </form>
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

    <!-- Active Assigned Trips & Details Master-Detail Workspace -->
    <div class="col-12">
        <x-master-detail-layout queueTitle="MY ASSIGNED TRIPS" queueSubtitle="Sorted by Highest Priority & Oldest Dispatch Time">
            <x-slot:queueContent>
                @if($activeTrips->isEmpty())
                    <div class="text-center text-muted py-5">
                        <div class="fs-2 mb-1">🚚</div>
                        <div class="fw-bold">No active assigned trips.</div>
                        <div class="small text-muted">Trips automatically populate here when the Transport Department dispatches a trip to {{ $currentDriver->driver_name ?? 'your profile' }}.</div>
                    </div>
                @else
                    @foreach($activeTrips as $t)
                        @php
                            $mainReq = $t->transportRequests->first();
                            $isSelected = ($selectedTrip->id ?? 0) === $t->id;
                        @endphp
                        <div class="card p-3 rounded-4 mb-2 shadow-sm border-translucent {{ $isSelected ? 'border-cyan bg-info-subtle' : 'bg-body' }}" style="cursor: pointer;" onclick="window.location.href='{{ route('driver.index', ['driver_id' => $currentDriver->id, 'trip_id' => $t->id]) }}'">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="badge bg-danger text-white font-monospace small px-2 py-1 rounded-pill">{{ $t->trip_number }}</span>
                                @if($mainReq)
                                    <span class="badge {{ $mainReq->priority_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ strtoupper($mainReq->priority) }}
                                    </span>
                                @endif
                            </div>

                            <div class="fw-bold text-body small mb-1">
                                {{ $mainReq->order_reference ?? 'Order Reference' }} ({{ $mainReq->customer_name ?? 'Customer' }})
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.75rem;">
                                <span>📍 {{ $t->destination_city }}</span>
                                <span>🚛 {{ $t->vehicle->vehicle_number ?? 'Vehicle' }}</span>
                            </div>

                            <div class="mt-2 pt-2 border-top border-translucent d-flex align-items-center justify-content-between">
                                <span class="badge bg-purple text-white font-monospace" style="font-size: 0.65rem; background-color: #a855f7;">
                                    {{ $t->dispatchManifest->manifest_number ?? 'Manifest' }}
                                </span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 0.65rem;">
                                    {{ $t->status_label }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif

                <!-- Completed Trips History Section -->
                @if($completedTrips->isNotEmpty())
                    <div class="mt-4 pt-3 border-top border-translucent">
                        <div class="fw-bold text-body small mb-2 text-uppercase text-muted">Completed Trips History</div>
                        @foreach($completedTrips as $ct)
                            <div class="p-2 px-3 rounded-3 bg-body-tertiary border border-translucent mb-1 small d-flex align-items-center justify-content-between">
                                <div>
                                    <span class="font-monospace fw-bold text-muted" style="font-size: 0.75rem;">{{ $ct->trip_number }}</span>
                                    <div class="text-muted" style="font-size: 0.7rem;">{{ $ct->destination_city }}</div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 0.65rem;">
                                    {{ strtoupper($ct->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-slot:queueContent>

            <x-slot:canvasContent>
                @if($selectedTrip && $selectedTask)
                    <!-- Trip Workspace Header -->
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3 border-bottom pb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-danger font-monospace fs-6 px-2.5 py-1 rounded-pill">{{ $selectedTrip->trip_number }}</span>
                                <span class="badge bg-purple text-white font-monospace px-2.5 py-1 rounded-pill" style="background-color: #a855f7;">
                                    {{ $selectedTrip->dispatchManifest->manifest_number ?? 'Manifest Issued' }}
                                </span>
                                <span class="badge {{ $selectedTask->driver_status_badge_class ?? 'bg-primary-subtle text-primary' }} rounded-pill px-3 py-1 fs-7">
                                    {{ $selectedTask->driver_status_label }}
                                </span>
                            </div>
                            <h3 class="fw-bold text-body mb-0">Delivery Task: Order {{ $selectedTask->order_reference }}</h3>
                            <div class="text-muted small mt-1">
                                Customer: <strong>{{ $selectedTask->customer_name }}</strong> | Destination: <strong>{{ $selectedTask->city }}</strong>
                            </div>
                        </div>

                        <div class="text-end">
                            <div class="text-muted small fw-semibold">Assigned Vehicle</div>
                            <span class="badge bg-info text-white font-monospace fs-6 px-3 py-1.5 rounded-pill">{{ $selectedTrip->vehicle->vehicle_number ?? 'Vehicle' }}</span>
                        </div>
                    </div>

                    <!-- Read-Only Trip & Order Details Card -->
                    <div class="card p-3 rounded-4 shadow-sm border-translucent mb-4 bg-body-tertiary">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="text-muted small fw-semibold">Delivery Address</div>
                                <div class="fw-bold text-body fs-6 mb-1">{{ $selectedTask->delivery_address ?? 'Primary Customer Address' }}</div>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 small">📍 {{ $selectedTask->city }}</span>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small fw-semibold">Package Specs</div>
                                <div class="fw-bold text-body">
                                    {{ $selectedTask->package_count }} {{ Str::plural('Carton', $selectedTask->package_count) }} ({{ number_format((float)$selectedTask->weight_kg, 1) }} kg)
                                </div>
                                <div class="small text-muted">Volume: {{ number_format((float)($selectedTask->volume_m3 ?? 0.5), 2) }} m³</div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-muted small fw-semibold">Dispatch Timestamp</div>
                                <div class="fw-bold text-body font-monospace">
                                    {{ $selectedTrip->dispatched_at ? $selectedTrip->dispatched_at->format('H:i, d M Y') : 'Dispatched' }}
                                </div>
                                <div class="small text-muted">Carrier: {{ $selectedTask->carrier ?? 'Logistics' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- DRIVER EXECUTION CONTROLS & CHECKPOINTS -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                            <div>
                                <h5 class="fw-black text-body mb-0">📲 Driver Delivery Checkpoints & Status Update</h5>
                                <span class="small text-muted">Update operational delivery checkpoints. All changes update ERP & CRM in real time.</span>
                            </div>
                        </div>

                        <!-- Step 1: Accept Trip if Not Accepted -->
                        @if($selectedTask->driver_status === 'dispatched')
                            <div class="alert alert-warning border-0 rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-bold fs-6">Trip Assigned to Your Terminal</div>
                                    <div class="small text-muted">Please confirm acceptance of custody for Trip #{{ $selectedTrip->trip_number }} before proceeding with delivery checkpoints.</div>
                                </div>
                                <form method="POST" action="{{ route('driver.accept-trip', $selectedTrip->id) }}">
                                    @csrf
                                    <input type="hidden" name="driver_id" value="{{ $currentDriver->id }}">
                                    <button type="submit" class="btn btn-warning text-dark rounded-3 px-4 py-2 fw-bold shadow-sm">
                                        ✋ Accept Assigned Trip &rarr;
                                    </button>
                                </form>
                            </div>
                        @endif

                        <!-- Step 2: Delivery Checkpoints & Action Buttons -->
                        <div class="row g-3 mb-4">
                            <!-- Reached Destination Button -->
                            <div class="col-md-4">
                                <form method="POST" action="{{ route('driver.update-status', $selectedTask->id) }}">
                                    @csrf
                                    <input type="hidden" name="driver_id" value="{{ $currentDriver->id }}">
                                    <input type="hidden" name="status" value="reached_destination">
                                    <button type="submit" class="btn btn-outline-info w-100 p-3 rounded-3 fw-bold text-start d-flex align-items-center justify-content-between {{ $selectedTask->driver_status === 'reached_destination' ? 'active bg-info text-white' : '' }}" {{ $selectedTask->driver_status === 'dispatched' ? 'disabled' : '' }}>
                                        <div>
                                            <div class="fs-6">📍 Reached Destination</div>
                                            <div class="small opacity-75">Vehicle arrived at customer site</div>
                                        </div>
                                        <span>&rarr;</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Delivery Attempt Button -->
                            <div class="col-md-4">
                                <form method="POST" action="{{ route('driver.update-status', $selectedTask->id) }}">
                                    @csrf
                                    <input type="hidden" name="driver_id" value="{{ $currentDriver->id }}">
                                    <input type="hidden" name="status" value="delivery_attempt">
                                    <button type="submit" class="btn btn-outline-primary w-100 p-3 rounded-3 fw-bold text-start d-flex align-items-center justify-content-between {{ $selectedTask->driver_status === 'delivery_attempt' ? 'active bg-primary text-white' : '' }}" {{ $selectedTask->driver_status === 'dispatched' ? 'disabled' : '' }}>
                                        <div>
                                            <div class="fs-6">📦 Delivery Attempt</div>
                                            <div class="small opacity-75">Unloading & presenting cargo</div>
                                        </div>
                                        <span>&rarr;</span>
                                    </button>
                                </form>
                            </div>

                            <!-- Delivered & Confirmed Button -->
                            <div class="col-md-4">
                                <form method="POST" action="{{ route('driver.update-status', $selectedTask->id) }}">
                                    @csrf
                                    <input type="hidden" name="driver_id" value="{{ $currentDriver->id }}">
                                    <input type="hidden" name="status" value="delivered">
                                    <button type="submit" class="btn btn-success w-100 p-3 rounded-3 fw-bold text-start text-white shadow-sm d-flex align-items-center justify-content-between" {{ $selectedTask->driver_status === 'dispatched' ? 'disabled' : '' }}>
                                        <div>
                                            <div class="fs-6">✔ Confirm Delivery</div>
                                            <div class="small opacity-75">Cargo delivered & finalized</div>
                                        </div>
                                        <span>✔</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Exception Reporting Form (Mandatory Remarks) -->
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                            <h6 class="fw-bold text-body mb-2">Report Delivery Exception (Mandatory Remarks)</h6>
                            <form method="POST" action="{{ route('driver.update-status', $selectedTask->id) }}">
                                @csrf
                                <input type="hidden" name="driver_id" value="{{ $currentDriver->id }}">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-body small">Select Exception Type *</label>
                                        <select name="status" class="form-select bg-body text-body rounded-3" required>
                                            <option value="" disabled selected>-- Select Exception Reason --</option>
                                            <option value="customer_unavailable">🚫 Customer Unavailable</option>
                                            <option value="delivery_refused">❌ Delivery Refused</option>
                                            <option value="address_not_found">🗺 Address Not Found</option>
                                            <option value="vehicle_breakdown">⚠️ Vehicle Breakdown</option>
                                            <option value="returned_to_warehouse">📦 Returned To Warehouse</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-body small">Mandatory Remarks / Exception Details *</label>
                                        <input type="text" name="notes" class="form-control bg-body text-body rounded-3" placeholder="Enter specific exception details..." required>
                                    </div>
                                    <div class="col-12 text-end mt-3">
                                        <button type="submit" class="btn btn-danger btn-sm rounded-3 px-4 py-2 fw-bold">
                                            Report Operational Exception &rarr;
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- IMMUTABLE OPERATIONAL DELIVERY TIMELINE -->
                    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
                        <h5 class="fw-black text-body mb-3 border-bottom pb-2">📋 Operational Delivery Timeline</h5>
                        
                        @if($selectedTask->deliveryTimelines->isEmpty())
                            <div class="text-muted small">No delivery events logged yet. Checkpoints will record automatically.</div>
                        @else
                            <div class="timeline ps-3">
                                @foreach($selectedTask->deliveryTimelines as $tl)
                                    <div class="mb-3 position-relative ps-4 border-start border-primary border-2">
                                        <div class="fw-bold text-body small mb-0.5">{{ $tl->event_type }}</div>
                                        <div class="small text-muted mb-1">{{ $tl->notes ?? 'Status milestone recorded' }}</div>
                                        <div class="small text-muted font-monospace" style="font-size: 0.7rem;">
                                            Driver: {{ $tl->driver_name ?? 'Driver' }} | Time: {{ $tl->recorded_at ? $tl->recorded_at->format('d M Y, H:i:s') : 'Recorded' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                @else
                    <div class="text-center text-muted py-5">
                        <div class="fs-1 mb-2">🚚</div>
                        <h5>Select an Assigned Trip</h5>
                        <p class="small text-muted mb-0">Select an active trip from the left panel to open the Driver Execution Terminal.</p>
                    </div>
                @endif
            </x-slot:canvasContent>
        </x-master-detail-layout>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live Synchronization Polling for Driver Terminal (Every 10 seconds)
    function syncLiveDriverTerminal() {
        const drvId = "{{ $currentDriver->id ?? 1 }}";
        fetch(`{{ route("driver.live-sync") }}?driver_id=${drvId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Driver Terminal Sync:', data.driver_name, data.active_trips_count, 'active trips.');
                }
            })
            .catch(err => console.log('Driver live sync catch:', err));
    }
    setInterval(syncLiveDriverTerminal, 10000);
});
</script>
@endpush
@endsection
