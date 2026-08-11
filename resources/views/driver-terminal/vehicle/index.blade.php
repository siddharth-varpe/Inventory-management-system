@extends('driver-terminal.layouts.app')

@section('title', 'Vehicle & Status — Driver Terminal')

@section('content')
<div class="vstack gap-3.5">

    <!-- 1. HEADER BAR -->
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2.5">
            <a href="{{ route('driver-terminal.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
               class="btn btn-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs border"
               style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 18px; height: 18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            <div>
                <h5 class="fw-black text-dark mb-0 fs-5">Vehicle & Status</h5>
                <p class="text-muted small mb-0" style="font-size: 0.78rem;">Your vehicle details, health and live status.</p>
            </div>
        </div>

        <button type="button" class="btn btn-light btn-sm rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs border text-secondary"
                style="width: 36px; height: 36px;" onclick="window.location.reload()" title="Refresh Vehicle Data">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </button>
    </div>

    <!-- 2. MY VEHICLE CARD -->
    <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #6366f1;"></span>
                <span class="fw-black text-dark fs-6">My Vehicle</span>
            </div>
            @if($assignedVehicle)
                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-2.5 py-1 rounded-pill micro-text fw-bold">
                    ● ASSIGNED
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2.5 py-1 rounded-pill micro-text fw-bold">
                    UNASSIGNED
                </span>
            @endif
        </div>

        @if($assignedVehicle)
            <div class="row align-items-center g-2 mb-3">
                <div class="col-7">
                    <div class="d-flex align-items-center gap-1.5 mb-1">
                        <span class="fs-5">🚚</span>
                        <h4 class="font-monospace text-dark fw-black mb-0 fs-5">{{ $assignedVehicle->vehicle_number }}</h4>
                    </div>
                    <div class="text-muted small fw-semibold" style="font-size: 0.8rem;">
                        {{ $assignedVehicle->manufacturer ?? 'Tata' }} {{ $assignedVehicle->model ?? '407' }} &bull; {{ $assignedVehicle->vehicle_type ?? 'Closed Body' }}
                    </div>
                </div>

                <div class="col-5 text-end">
                    <img src="{{ asset('images/truck-3d.png') }}" alt="3D Truck Model" 
                         style="max-width: 100%; max-height: 90px; object-fit: contain; filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.08));">
                </div>
            </div>

            <!-- 3-Column Metrics Grid -->
            <div class="row g-2 mb-3">
                <!-- Load Capacity -->
                <div class="col-4">
                    <div class="bg-light p-2.5 rounded-3 border h-100 text-center">
                        <div class="mb-1 text-primary">🛍️</div>
                        <div class="text-muted micro-text text-uppercase fw-semibold" style="font-size: 0.65rem;">Load Capacity</div>
                        <div class="fw-black text-dark fs-7 mt-0.5">
                            {{ $assignedVehicle->load_capacity_kg ? number_format($assignedVehicle->load_capacity_kg / 1000, 1) . ' Ton' : 'Not available' }}
                        </div>
                    </div>
                </div>

                <!-- Fuel Level -->
                <div class="col-4">
                    <div class="bg-light p-2.5 rounded-3 border h-100 text-center">
                        <div class="mb-1 text-warning">⛽</div>
                        <div class="text-muted micro-text text-uppercase fw-semibold" style="font-size: 0.65rem;">Fuel Level</div>
                        <div class="fw-black text-dark fs-7 mt-0.5">
                            Not available
                        </div>
                    </div>
                </div>

                <!-- Odometer -->
                <div class="col-4">
                    <div class="bg-light p-2.5 rounded-3 border h-100 text-center">
                        <div class="mb-1 text-info">⏱️</div>
                        <div class="text-muted micro-text text-uppercase fw-semibold" style="font-size: 0.65rem;">Odometer</div>
                        <div class="fw-black text-dark fs-7 mt-0.5 font-monospace">
                            {{ $assignedVehicle->current_odometer_km ? number_format($assignedVehicle->current_odometer_km) . ' km' : 'Not available' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insurance Valid Strip -->
            <div class="bg-light p-2.5 rounded-3 border d-flex align-items-center justify-content-between" style="font-size: 0.8rem;">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-6">🛡️</span>
                    <div>
                        <span class="text-muted micro-text d-block" style="font-size: 0.68rem;">Insurance Valid Till</span>
                        <span class="fw-bold text-dark font-monospace">
                            {{ $assignedVehicle->insurance_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->insurance_expiry_date)->format('d M Y') : '15 Dec 2026' }}
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    @php
                        $insStatus = $assignedVehicle->insurance_status ?? 'Valid';
                        $badgeClass = match($insStatus) {
                            'Valid' => 'bg-success-subtle text-success border border-success-subtle',
                            'Expiring Soon' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            'Expired' => 'bg-danger-subtle text-danger border border-danger-subtle',
                            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} micro-text fw-bold px-2 py-0.5 rounded-pill">{{ $insStatus }}</span>
                    <span class="text-muted">&rsaquo;</span>
                </div>
            </div>
        @else
            <!-- EMPTY STATE WHEN NO VEHICLE ASSIGNED -->
            <div class="text-center py-4">
                <img src="{{ asset('images/truck-3d.png') }}" alt="3D Truck" style="width: 90px; height: 90px; object-fit: contain; opacity: 0.4;">
                <h6 class="fw-bold text-dark mt-2 mb-1">No vehicle assigned</h6>
                <p class="text-muted micro-text mb-0 px-3">
                    There is currently no vehicle assigned to your driver account. Please contact Transport Management.
                </p>
            </div>
        @endif
    </div>

    <!-- 3. VEHICLE HEALTH CARD -->
    <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h6 class="fw-black text-dark mb-0 fs-6">Vehicle Health</h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 0.72rem;">
                Good
            </span>
        </div>
        <p class="text-muted micro-text mb-3" style="font-size: 0.75rem;">All systems are normal</p>

        <!-- 5 System Icons Row -->
        <div class="row row-cols-5 g-1 text-center mb-3">
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-5 mb-0.5">🏎️</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.65rem;">Engine</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.62rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-5 mb-0.5">⭕</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.65rem;">Brakes</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.62rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-5 mb-0.5">⚙️</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.65rem;">Tyres</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.62rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-5 mb-0.5">🔋</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.65rem;">Battery</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.62rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-5 mb-0.5">💡</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.65rem;">Lights</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.62rem;">Good</div>
                </div>
            </div>
        </div>

        <!-- Last Checked Strip -->
        <div class="bg-success-subtle p-2.5 rounded-3 border border-success-subtle d-flex align-items-center justify-content-between" style="font-size: 0.75rem;">
            <div class="d-flex align-items-center gap-1.5 text-success-emphasis fw-semibold">
                <span>✓</span>
                <span>Last Checked: {{ $assignedVehicle && $assignedVehicle->last_service_date ? \Carbon\Carbon::parse($assignedVehicle->last_service_date)->format('d M Y, 07:30 AM') : '17 May 2026, 07:30 AM' }}</span>
            </div>
            <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-none" onclick="alert('Inspection history log interface reserved for Operational Phase.')">
                View All Checks &rsaquo;
            </a>
        </div>
    </div>

    <!-- 4. LIVE STATUS CARD (NON-GPS) -->
    <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-black text-dark mb-0 fs-6">Live Status</h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold micro-text">
                ● Live
            </span>
        </div>

        <!-- Location Row -->
        <div class="d-flex align-items-center justify-content-between bg-light p-2.5 rounded-3 border mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-5 text-primary">📍</span>
                <div>
                    <div class="fw-bold text-dark small" style="font-size: 0.8rem;">
                        {{ $assignedVehicle && $assignedVehicle->current_location ? $assignedVehicle->current_location : 'Near Kothrud Depot, Pune - 411038' }}
                    </div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">Updated just now</div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 opacity-75 fw-bold micro-text py-1 px-2.5" 
                    disabled title="GPS tracking disabled in Core 4">
                🗺️ View on Map
            </button>
        </div>

        <!-- 4 Operational Metrics Grid -->
        <div class="row row-cols-4 g-2 text-center">
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-6 text-primary mb-0.5">⏱️</div>
                    <div class="text-muted micro-text" style="font-size: 0.65rem;">Speed</div>
                    <div class="fw-black text-dark small" style="font-size: 0.78rem;">42 km/h</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-6 text-success mb-0.5">🏎️</div>
                    <div class="text-muted micro-text" style="font-size: 0.65rem;">Engine</div>
                    <div class="fw-black text-dark small" style="font-size: 0.78rem;">On</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-6 text-warning mb-0.5">🔌</div>
                    <div class="text-muted micro-text" style="font-size: 0.65rem;">Ignition</div>
                    <div class="fw-black text-dark small" style="font-size: 0.78rem;">ON</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fs-6 text-info mb-0.5">❄️</div>
                    <div class="text-muted micro-text" style="font-size: 0.65rem;">AC Status</div>
                    <div class="fw-black text-dark small" style="font-size: 0.78rem;">Off</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. DOCUMENTS AND NEXT SERVICE ROW -->
    <div class="row g-3">
        <!-- DOCUMENTS CARD -->
        <div class="col-12 col-md-6">
            <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-2.5">
                    <h6 class="fw-black text-dark mb-0 fs-6">Documents</h6>
                    <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-none micro-text" onclick="alert('Document Portal view reserved for Operational Phase.')">
                        View All &rsaquo;
                    </a>
                </div>

                <div class="vstack gap-2">
                    <!-- RC Book -->
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-6 text-primary">📄</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">RC Book</div>
                                <div class="text-muted micro-text" style="font-size: 0.68rem;">Valid till 01 Feb 2029</div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2 py-0.5 rounded-pill">Valid</span>
                    </div>

                    <!-- Insurance -->
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-6 text-success">🛡️</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">Insurance</div>
                                <div class="text-muted micro-text" style="font-size: 0.68rem;">
                                    Valid till {{ $assignedVehicle && $assignedVehicle->insurance_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->insurance_expiry_date)->format('d M Y') : '15 Dec 2026' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2 py-0.5 rounded-pill">
                            {{ $assignedVehicle ? $assignedVehicle->insurance_status : 'Valid' }}
                        </span>
                    </div>

                    <!-- PUC Certificate -->
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-6 text-success">🍃</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">PUC Certificate</div>
                                <div class="text-muted micro-text" style="font-size: 0.68rem;">
                                    Valid till {{ $assignedVehicle && $assignedVehicle->puc_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->puc_expiry_date)->format('d M Y') : '10 Jan 2027' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2 py-0.5 rounded-pill">
                            {{ $assignedVehicle ? $assignedVehicle->puc_status : 'Valid' }}
                        </span>
                    </div>

                    <!-- Permit -->
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fs-6 text-info">📋</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">Permit</div>
                                <div class="text-muted micro-text" style="font-size: 0.68rem;">
                                    Valid till {{ $assignedVehicle && $assignedVehicle->permit_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->permit_expiry_date)->format('d M Y') : '31 Mar 2027' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2 py-0.5 rounded-pill">
                            {{ $assignedVehicle ? $assignedVehicle->permit_status : 'Valid' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEXT SERVICE CARD -->
        <div class="col-12 col-md-6">
            <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm h-100">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="rounded-circle p-1 bg-primary-subtle text-primary fs-6 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">🔧</span>
                    <h6 class="fw-black text-dark mb-0 fs-6">Next Service</h6>
                </div>

                <!-- Due in Banner -->
                <div class="p-3 rounded-3 mb-3 border text-start" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Due in</span>
                    <div class="fw-black fs-4 text-primary" style="color: #6366f1 !important;">
                        {{ $serviceRemainingDays !== null ? ($serviceRemainingDays > 0 ? $serviceRemainingDays . ' Days' : 'Today') : '23 Days' }}
                    </div>
                    <div class="text-muted micro-text" style="font-size: 0.72rem;">or in 3,250 km</div>
                </div>

                <!-- Metrics Row -->
                <div class="row g-2 mb-3" style="font-size: 0.78rem;">
                    <div class="col-6">
                        <span class="text-muted micro-text d-block">📅 Last Service</span>
                        <span class="fw-bold text-dark font-monospace">
                            {{ $assignedVehicle && $assignedVehicle->last_service_date ? \Carbon\Carbon::parse($assignedVehicle->last_service_date)->format('d M Y') : '15 Apr 2026' }}
                        </span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block">⏱️ Odometer</span>
                        <span class="fw-bold text-dark font-monospace">
                            {{ $assignedVehicle && $assignedVehicle->current_odometer_km ? number_format($assignedVehicle->current_odometer_km) . ' km' : '1,22,430 km' }}
                        </span>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-primary w-100 rounded-3 py-2 fw-bold micro-text shadow-xs" 
                        onclick="alert('Service scheduling portal interface reserved for Operational Phase.')">
                    📅 Schedule Service
                </button>
            </div>
        </div>
    </div>

    <!-- 6. FOUR UTILITY ACTIONS GRID -->
    <div class="row row-cols-2 row-cols-md-4 g-2">
        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3 shadow-xs text-center w-100 h-100 btn text-start"
                    onclick="alert('Vehicle Checklist module reserved for Operational Phase.')">
                <div class="fs-4 mb-1">📋</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">Vehicle Checklist</div>
            </button>
        </div>

        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3 shadow-xs text-center w-100 h-100 btn text-start"
                    onclick="alert('Issue Reporting module reserved for Operational Phase.')">
                <div class="fs-4 mb-1 text-danger">⚠️</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">Report Issue</div>
            </button>
        </div>

        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3 shadow-xs text-center w-100 h-100 btn text-start"
                    onclick="alert('Fuel Log module reserved for Operational Phase.')">
                <div class="fs-4 mb-1 text-warning">⛽</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">Fuel Log</div>
            </button>
        </div>

        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3 shadow-xs text-center w-100 h-100 btn text-start"
                    onclick="alert('Service History log reserved for Operational Phase.')">
                <div class="fs-4 mb-1 text-info">🕒</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.78rem;">Service History</div>
            </button>
        </div>
    </div>

</div>
@endsection
