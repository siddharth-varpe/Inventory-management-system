@extends('driver-terminal.layouts.app')

@section('title', 'Vehicle & Status — Driver Terminal')

@section('content')
<div class="vstack gap-4">

    <!-- 1. VEHICLE HEADER BAR -->
    <div class="d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('driver-terminal.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
               class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs border"
               style="width: 44px; height: 44px;" title="Back to Dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 20px; height: 20px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </a>
            <div>
                <h5 class="fw-black text-dark mb-0 fs-5">Vehicle &amp; Status</h5>
                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Dedicated Vehicle Master Profile, 3D WebGL Model &amp; Status</p>
            </div>
        </div>

        <button type="button" class="btn btn-light rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs border text-secondary"
                style="width: 44px; height: 44px;" onclick="window.location.reload()" title="Refresh Vehicle Data">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 20px; height: 20px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
        </button>
    </div>

    <!-- 2. CURRENT VEHICLE / ASSIGNMENT (SHOWCASE CARD WITH THREE.JS 3D MODEL) -->
    <div class="card bg-white border border-translucent rounded-4 p-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background-color: #6366f1;"></span>
                <span class="fw-black text-dark fs-6">My Vehicle Showcase</span>
            </div>
            @if($assignedVehicle)
                <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1 rounded-pill micro-text fw-bold">
                    ● ASSIGNED &amp; ACTIVE
                </span>
            @else
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-3 py-1 rounded-pill micro-text fw-bold">
                    UNASSIGNED
                </span>
            @endif
        </div>

        @if($assignedVehicle)
            <!-- PRODUCTION-GRADE 3D WEBGL VEHICLE VIEWER CONTAINER -->
            <div id="truck3d-container" class="mb-4 position-relative rounded-4 overflow-hidden border border-translucent shadow-sm" 
                 style="width: 100%; height: 235px; background: radial-gradient(circle at center, #f8fafc 0%, #edf2f7 100%);">
                
                <!-- 3D Canvas -->
                <canvas id="truck3d-canvas" style="width: 100%; height: 100%; display: block; cursor: grab;"></canvas>

                <!-- Loading State Skeleton Overlay -->
                <div class="vehicle-3d-loader position-absolute top-0 start-0 w-100 h-100 bg-white d-flex align-items-center justify-content-center" 
                     style="transition: opacity 0.3s ease; z-index: 5;">
                    <div class="text-center">
                        <div class="spinner-border text-primary spinner-border-sm mb-2" role="status"></div>
                        <div class="fw-bold micro-text text-dark">Loading 3D Fleet Vehicle...</div>
                        <div class="text-muted micro-text" style="font-size: 0.68rem;">{{ $assignedVehicle->manufacturer ?? 'Tata' }} {{ $assignedVehicle->model ?? 'Prima' }}</div>
                    </div>
                </div>

                <!-- Onboarding Temporary Interaction Guidance -->
                <div class="vehicle-3d-hint position-absolute bottom-0 start-50 translate-middle-x mb-2 badge bg-dark-subtle text-dark-emphasis font-monospace micro-text px-3 py-1 rounded-pill opacity-75 shadow-xs" 
                     style="font-size: 0.68rem; pointer-events: none; z-index: 2;">
                    🔄 Touch &amp; Drag to Rotate &bull; Double-Tap to Reset
                </div>

                <!-- Perspective Preset Camera Toolbar -->
                <div class="position-absolute top-0 end-0 m-2.5 d-flex gap-1.5" style="z-index: 3;">
                    <button type="button" class="btn btn-sm btn-white bg-white border shadow-xs rounded-pill px-2.5 py-1 micro-text fw-bold text-secondary dt-touch-active" 
                            data-3d-action="reset" title="Reset View">
                        📷 Reset
                    </button>
                    <button type="button" class="btn btn-sm btn-white bg-white border shadow-xs rounded-pill px-2.5 py-1 micro-text fw-bold text-secondary dt-touch-active" 
                            data-3d-action="front" title="Front Perspective">
                        📐 Front
                    </button>
                    <button type="button" class="btn btn-sm btn-white bg-white border shadow-xs rounded-pill px-2.5 py-1 micro-text fw-bold text-secondary dt-touch-active" 
                            data-3d-action="side" title="Side Perspective">
                        📐 Side
                    </button>
                    <button type="button" class="btn btn-sm btn-white bg-white border shadow-xs rounded-pill px-2.5 py-1 micro-text fw-bold text-secondary dt-touch-active" 
                            data-3d-action="rear" title="Rear Perspective">
                        📐 Rear
                    </button>
                </div>
            </div>

            <div class="row align-items-center g-3 mb-3.5">
                <div class="col-12">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold mb-0.5" style="font-size: 0.68rem; letter-spacing: 0.5px;">Vehicle Registration</span>
                    <h3 class="font-monospace text-dark fw-black mb-1 fs-4">{{ $assignedVehicle->vehicle_number }}</h3>
                    <div class="text-primary small font-monospace fw-bold mb-0" style="color: #2563eb !important; font-size: 0.85rem;">
                        {{ $assignedVehicle->manufacturer ?? 'Tata' }} {{ $assignedVehicle->model ?? '407' }} &bull; {{ $assignedVehicle->vehicle_type ?? 'Closed Body' }}
                    </div>
                </div>
            </div>

            <!-- 3-Column Metrics Grid -->
            <div class="row g-2.5 mb-3.5">
                <!-- Load Capacity -->
                <div class="col-4">
                    <div class="bg-light p-3 rounded-3 border h-100 text-center">
                        <div class="mb-1 text-primary fs-5">🛍️</div>
                        <div class="text-muted micro-text text-uppercase fw-semibold" style="font-size: 0.68rem;">Load Capacity</div>
                        <div class="fw-black text-dark fs-7 mt-1">
                            {{ $assignedVehicle->load_capacity_kg ? number_format($assignedVehicle->load_capacity_kg / 1000, 1) . ' Ton' : '7.5 Ton' }}
                        </div>
                    </div>
                </div>

                <!-- Fuel Level -->
                <div class="col-4">
                    <div class="bg-light p-3 rounded-3 border h-100 text-center">
                        <div class="mb-1 text-warning fs-5">⛽</div>
                        <div class="text-muted micro-text text-uppercase fw-semibold" style="font-size: 0.68rem;">Fuel Level</div>
                        <div class="fw-black text-dark fs-7 mt-1">
                            78% Full
                        </div>
                    </div>
                </div>

                <!-- Odometer -->
                <div class="col-4">
                    <div class="bg-light p-3 rounded-3 border h-100 text-center">
                        <div class="mb-1 text-info fs-5">⏱️</div>
                        <div class="text-muted micro-text text-uppercase fw-semibold" style="font-size: 0.68rem;">Odometer</div>
                        <div class="fw-black text-dark fs-7 mt-1 font-monospace">
                            {{ $assignedVehicle->current_odometer_km ? number_format($assignedVehicle->current_odometer_km) . ' km' : '12,000 km' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insurance Valid Strip -->
            <div class="bg-light p-3 rounded-3 border d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="fs-5">🛡️</span>
                    <div>
                        <span class="text-muted micro-text d-block" style="font-size: 0.72rem;">Insurance Valid Till</span>
                        <span class="fw-bold text-dark font-monospace" style="font-size: 0.85rem;">
                            {{ $assignedVehicle->insurance_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->insurance_expiry_date)->format('d M Y') : '15 Dec 2026' }}
                        </span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @php
                        $insStatus = $assignedVehicle->insurance_status ?? 'Valid';
                        $badgeClass = match($insStatus) {
                            'Valid' => 'bg-success-subtle text-success border border-success-subtle',
                            'Expiring Soon' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                            'Expired' => 'bg-danger-subtle text-danger border border-danger-subtle',
                            default => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }} micro-text fw-bold px-2.5 py-1 rounded-pill">{{ $insStatus }}</span>
                </div>
            </div>
        @else
            <!-- EMPTY STATE WHEN NO VEHICLE ASSIGNED -->
            <div class="text-center py-5">
                <div class="fs-1 mb-2">🚚</div>
                <h6 class="fw-bold text-dark mt-3 mb-1">No vehicle assigned</h6>
                <p class="text-muted small mb-0 px-3" style="font-size: 0.82rem;">
                    There is currently no vehicle assigned to your driver account. Please contact Transport Management.
                </p>
            </div>
        @endif
    </div>

    <!-- 3. VEHICLE HEALTH CARD -->
    <div class="card bg-white border border-translucent rounded-4 p-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <h6 class="fw-black text-dark mb-0 fs-6">Vehicle Health</h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                Good
            </span>
        </div>
        <p class="text-muted small mb-3.5" style="font-size: 0.78rem;">All systems are normal</p>

        <!-- 5 System Icons Row -->
        <div class="row row-cols-5 g-2 text-center mb-3.5">
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-5 mb-1">🏎️</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.68rem;">Engine</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.65rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-5 mb-1">⭕</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.68rem;">Brakes</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.65rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-5 mb-1">⚙️</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.68rem;">Tyres</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.65rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-5 mb-1">🔋</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.68rem;">Battery</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.65rem;">Good</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-5 mb-1">💡</div>
                    <div class="fw-bold text-dark micro-text" style="font-size: 0.68rem;">Lights</div>
                    <div class="text-success micro-text fw-bold" style="font-size: 0.65rem;">Good</div>
                </div>
            </div>
        </div>

        <!-- Last Checked Strip -->
        <div class="bg-success-subtle p-3 rounded-3 border border-success-subtle d-flex align-items-center justify-content-between" style="font-size: 0.78rem;">
            <div class="d-flex align-items-center gap-2 text-success-emphasis fw-semibold">
                <span>✓</span>
                <span>Last Checked: {{ $assignedVehicle && $assignedVehicle->last_service_date ? \Carbon\Carbon::parse($assignedVehicle->last_service_date)->format('d M Y, 07:30 AM') : '17 May 2026, 07:30 AM' }}</span>
            </div>
            <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-none" onclick="alert('Inspection history log interface reserved for Operational Phase.')">
                View All Checks &rsaquo;
            </a>
        </div>
    </div>

    <!-- 4. LIVE STATUS CARD (NON-GPS) -->
    <div class="card bg-white border border-translucent rounded-4 p-4 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-black text-dark mb-0 fs-6">Live Status</h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold micro-text">
                ● Live
            </span>
        </div>

        <!-- Location Row -->
        <div class="d-flex align-items-center justify-content-between bg-light p-3 rounded-3 border mb-3.5">
            <div class="d-flex align-items-center gap-2.5">
                <span class="fs-5 text-primary">📍</span>
                <div>
                    <div class="fw-bold text-dark small" style="font-size: 0.82rem;">
                        {{ $assignedVehicle && $assignedVehicle->current_location ? $assignedVehicle->current_location : 'Near Kothrud Depot, Pune - 411038' }}
                    </div>
                    <div class="text-muted micro-text" style="font-size: 0.7rem;">Updated just now</div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary rounded-3 opacity-75 fw-bold micro-text py-1.5 px-3" 
                    disabled title="GPS tracking disabled in Core 4" style="min-height: 38px;">
                🗺️ View on Map
            </button>
        </div>

        <!-- 4 Operational Metrics Grid -->
        <div class="row row-cols-4 g-2.5 text-center">
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-6 text-primary mb-1">⏱️</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">Speed</div>
                    <div class="fw-black text-dark small" style="font-size: 0.82rem;">42 km/h</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-6 text-success mb-1">🏎️</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">Engine</div>
                    <div class="fw-black text-dark small" style="font-size: 0.82rem;">On</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-6 text-warning mb-1">🔌</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">Ignition</div>
                    <div class="fw-black text-dark small" style="font-size: 0.82rem;">ON</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2.5 bg-light rounded-3 border">
                    <div class="fs-6 text-info mb-1">❄️</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">AC Status</div>
                    <div class="fw-black text-dark small" style="font-size: 0.82rem;">Off</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. DOCUMENTS AND NEXT SERVICE ROW -->
    <div class="row g-3">
        <!-- DOCUMENTS CARD -->
        <div class="col-12 col-md-6">
            <div class="card bg-white border border-translucent rounded-4 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-black text-dark mb-0 fs-6">Documents</h6>
                    <a href="javascript:void(0)" class="text-primary fw-bold text-decoration-none micro-text" onclick="alert('Document Portal view reserved for Operational Phase.')">
                        View All &rsaquo;
                    </a>
                </div>

                <div class="vstack gap-2.5">
                    <!-- RC Book -->
                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2.5">
                            <span class="fs-6 text-primary">📄</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">RC Book</div>
                                <div class="text-muted micro-text" style="font-size: 0.7rem;">Valid till 01 Feb 2029</div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2.5 py-1 rounded-pill">Valid</span>
                    </div>

                    <!-- Insurance -->
                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2.5">
                            <span class="fs-6 text-success">🛡️</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">Insurance</div>
                                <div class="text-muted micro-text" style="font-size: 0.7rem;">
                                    Valid till {{ $assignedVehicle && $assignedVehicle->insurance_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->insurance_expiry_date)->format('d M Y') : '15 Dec 2026' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2.5 py-1 rounded-pill">
                            {{ $assignedVehicle ? $assignedVehicle->insurance_status : 'Valid' }}
                        </span>
                    </div>

                    <!-- PUC Certificate -->
                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2.5">
                            <span class="fs-6 text-success">🍃</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">PUC Certificate</div>
                                <div class="text-muted micro-text" style="font-size: 0.7rem;">
                                    Valid till {{ $assignedVehicle && $assignedVehicle->puc_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->puc_expiry_date)->format('d M Y') : '10 Jan 2027' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2.5 py-1 rounded-pill">
                            {{ $assignedVehicle ? $assignedVehicle->puc_status : 'Valid' }}
                        </span>
                    </div>

                    <!-- Permit -->
                    <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 bg-light border">
                        <div class="d-flex align-items-center gap-2.5">
                            <span class="fs-6 text-info">📋</span>
                            <div>
                                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">Permit</div>
                                <div class="text-muted micro-text" style="font-size: 0.7rem;">
                                    Valid till {{ $assignedVehicle && $assignedVehicle->permit_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->permit_expiry_date)->format('d M Y') : '31 Mar 2027' }}
                                </div>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle micro-text fw-bold px-2.5 py-1 rounded-pill">
                            {{ $assignedVehicle ? $assignedVehicle->permit_status : 'Valid' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEXT SERVICE CARD -->
        <div class="col-12 col-md-6">
            <div class="card bg-white border border-translucent rounded-4 p-4 shadow-sm h-100">
                <div class="d-flex align-items-center gap-2 mb-2.5">
                    <span class="rounded-circle p-1 bg-primary-subtle text-primary fs-6 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">🔧</span>
                    <h6 class="fw-black text-dark mb-0 fs-6">Next Service</h6>
                </div>

                <!-- Due in Banner -->
                <div class="p-3.5 rounded-3 mb-3.5 border text-start" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Due in</span>
                    <div class="fw-black fs-4 text-primary" style="color: #6366f1 !important;">
                        {{ $serviceRemainingDays !== null ? ($serviceRemainingDays > 0 ? $serviceRemainingDays . ' Days' : 'Today') : '23 Days' }}
                    </div>
                    <div class="text-muted micro-text" style="font-size: 0.75rem;">or in 3,250 km</div>
                </div>

                <!-- Metrics Row -->
                <div class="row g-2 mb-3.5" style="font-size: 0.8rem;">
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

                <button type="button" class="btn btn-outline-primary w-100 rounded-3 py-2.5 fw-bold micro-text shadow-xs" 
                        onclick="alert('Service scheduling portal interface reserved for Operational Phase.')" style="min-height: 44px;">
                    📅 Schedule Service
                </button>
            </div>
        </div>
    </div>

    <!-- 6. FOUR UTILITY ACTIONS GRID -->
    <div class="row row-cols-2 row-cols-md-4 g-3">
        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3.5 shadow-xs text-center w-100 h-100 btn text-start d-flex flex-column align-items-center justify-content-center"
                    onclick="alert('Vehicle Checklist module reserved for Operational Phase.')" style="min-height: 90px;">
                <div class="fs-4 mb-1">📋</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">Vehicle Checklist</div>
            </button>
        </div>

        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3.5 shadow-xs text-center w-100 h-100 btn text-start d-flex flex-column align-items-center justify-content-center"
                    onclick="alert('Issue Reporting module reserved for Operational Phase.')" style="min-height: 90px;">
                <div class="fs-4 mb-1 text-danger">⚠️</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">Report Issue</div>
            </button>
        </div>

        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3.5 shadow-xs text-center w-100 h-100 btn text-start d-flex flex-column align-items-center justify-content-center"
                    onclick="alert('Fuel Log module reserved for Operational Phase.')" style="min-height: 90px;">
                <div class="fs-4 mb-1 text-warning">⛽</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">Fuel Log</div>
            </button>
        </div>

        <div class="col">
            <button type="button" class="card bg-white border border-translucent rounded-4 p-3.5 shadow-xs text-center w-100 h-100 btn text-start d-flex flex-column align-items-center justify-content-center"
                    onclick="alert('Service History log reserved for Operational Phase.')" style="min-height: 90px;">
                <div class="fs-4 mb-1 text-info">🕒</div>
                <div class="fw-bold text-dark micro-text" style="font-size: 0.8rem;">Service History</div>
            </button>
        </div>
    </div>

    @if($assignedVehicle)
    <!-- 7. FULL VEHICLE MASTER SPECIFICATIONS (FINAL EXPANDABLE SECTION AT VERY BOTTOM) -->
    <div class="card bg-white border border-translucent rounded-4 shadow-sm overflow-hidden mt-2">
        <button type="button" class="card-header bg-white border-0 p-4 d-flex align-items-center justify-content-between text-start w-100 text-decoration-none shadow-none" 
                data-bs-toggle="collapse" data-bs-target="#fullMasterSpecsCollapse" aria-expanded="true" aria-controls="fullMasterSpecsCollapse"
                style="cursor: pointer;">
            <div class="d-flex align-items-center gap-2.5">
                <span class="fs-5">📋</span>
                <div>
                    <h6 class="fw-black text-dark mb-0 fs-6">Full Vehicle Master Specifications</h6>
                    <span class="text-muted micro-text d-block" style="font-size: 0.72rem;">Complete technical details &amp; master records</span>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.7rem;">Verified Master</span>
                <span class="text-secondary fw-bold fs-6 chevron-icon">▾</span>
            </div>
        </button>

        <div class="collapse show" id="fullMasterSpecsCollapse">
            <div class="card-body p-4 pt-0 border-top border-translucent">
                <div class="row g-3 pt-3" style="font-size: 0.82rem;">
                    <!-- Reg & Code -->
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Registration Number</span>
                        <span class="font-monospace text-dark fw-bold">{{ $assignedVehicle->vehicle_number }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Vehicle Code</span>
                        <span class="font-monospace text-primary fw-bold">{{ $assignedVehicle->vehicle_code ?? 'VEH-000001' }}</span>
                    </div>

                    <!-- Type & Make -->
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Vehicle Class</span>
                        <span class="text-dark fw-semibold">{{ $assignedVehicle->vehicle_type ?? 'Heavy Commercial Vehicle' }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Manufacturer</span>
                        <span class="text-dark fw-semibold">{{ $assignedVehicle->manufacturer ?? 'Tata Motors' }}</span>
                    </div>

                    <!-- Model & Year -->
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Model</span>
                        <span class="text-dark fw-semibold">{{ $assignedVehicle->model ?? 'Prima 2830.K' }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Manufacturing Year</span>
                        <span class="font-monospace text-dark fw-bold">{{ $assignedVehicle->manufacturing_year ?? '2022' }}</span>
                    </div>

                    <!-- Load & Volume Capacity -->
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Load Capacity</span>
                        <span class="text-dark fw-bold">{{ number_format($assignedVehicle->load_capacity_kg ?? 7500, 2) }} kg</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Volume Capacity</span>
                        <span class="text-dark fw-bold">{{ number_format($assignedVehicle->volume_capacity_m3 ?? 22.5, 2) }} m³</span>
                    </div>

                    <!-- Odometer & Fuel -->
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Current Odometer</span>
                        <span class="font-monospace text-dark fw-bold">{{ number_format($assignedVehicle->current_odometer_km ?? 12000) }} km</span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Fuel Type</span>
                        <span class="badge bg-light text-dark border font-monospace">{{ $assignedVehicle->fuel_type ?? 'Diesel' }}</span>
                    </div>

                    <!-- Health & Maintenance Status -->
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Maintenance Status</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            ✓ {{ $assignedVehicle->maintenance_status ?? 'Good & Active' }}
                        </span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Fitness Expiry Date</span>
                        <span class="text-dark font-monospace">
                            {{ $assignedVehicle->fitness_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->fitness_expiry_date)->format('d M Y') : '08 Aug 2027' }}
                        </span>
                    </div>

                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">PUC Expiry Date</span>
                        <span class="text-dark font-monospace">
                            {{ $assignedVehicle->puc_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->puc_expiry_date)->format('d M Y') : '08 Feb 2027' }}
                        </span>
                    </div>
                    <div class="col-6">
                        <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Insurance Expiry Date</span>
                        <span class="text-dark font-monospace">
                            {{ $assignedVehicle->insurance_expiry_date ? \Carbon\Carbon::parse($assignedVehicle->insurance_expiry_date)->format('d M Y') : '08 Aug 2027' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@section('scripts')
<!-- THREE.JS 3D WEBGL ENGINE, ORBITCONTROLS & GLTFLOADER -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/controls/OrbitControls.js"></script>
<script src="https://cdn.jsdelivr.net/npm/three@0.128.0/examples/js/loaders/GLTFLoader.js"></script>
<script src="{{ asset('js/driver-terminal/vehicle-3d-viewer.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Vehicle3DViewer === 'undefined') return;

    const viewer = new Vehicle3DViewer({
        container: '#truck3d-container',
        canvas: '#truck3d-canvas',
        vehicleData: {
            registration: "{{ $assignedVehicle ? $assignedVehicle->vehicle_number : '' }}",
            type: "{{ $assignedVehicle ? $assignedVehicle->vehicle_type : '' }}",
            manufacturer: "{{ $assignedVehicle ? $assignedVehicle->manufacturer : '' }}",
            model: "{{ $assignedVehicle ? $assignedVehicle->model : '' }}",
            code: "{{ $assignedVehicle ? $assignedVehicle->vehicle_code : '' }}",
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function () {
        if (viewer && typeof viewer.dispose === 'function') {
            viewer.dispose();
        }
    });
});
</script>
@endsection
