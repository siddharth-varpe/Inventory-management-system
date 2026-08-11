@extends('driver-terminal.layouts.app')

@section('title', 'Vehicle Information — Driver Terminal')

@section('content')
<div class="vstack gap-4">

    <!-- 1. PAGE HEADER BAR -->
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-black text-dark mb-0 fs-5">Vehicle Information</h5>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">3D Model & Technical Specifications of Assigned Fleet Vehicle</p>
        </div>
        <a href="{{ route('driver-terminal.driver-profile', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
           class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-xs" style="font-size: 0.78rem;">
            <span>👤 Driver Profile</span>
            <span>&rsaquo;</span>
        </a>
    </div>

    @if($assignedVehicle)
        <!-- 2. HERO 3D VEHICLE SHOWCASE CARD -->
        <div class="card bg-white border border-translucent rounded-4 p-4 text-center shadow-sm overflow-hidden position-relative">
            <div class="position-absolute top-0 end-0 m-3">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold" style="font-size: 0.75rem;">
                    ● ASSIGNED & ACTIVE
                </span>
            </div>

            <!-- 3D TRUCK MODEL DISPLAY -->
            <div class="my-2 d-flex justify-content-center">
                <div class="p-3 rounded-circle d-flex align-items-center justify-content-center" 
                     style="background: radial-gradient(circle, #eff6ff 0%, #ffffff 70%); width: 160px; height: 160px;">
                    <img src="{{ asset('images/truck-3d.png') }}" alt="3D Vehicle Model" 
                         style="width: 140px; height: 140px; object-fit: contain; filter: drop-shadow(0 10px 15px rgba(37, 99, 235, 0.15));">
                </div>
            </div>

            <h4 class="font-monospace text-dark fw-black mb-1 fs-4">{{ $assignedVehicle->vehicle_number }}</h4>
            <div class="text-primary fw-bold small mb-2" style="color: #2563eb !important;">
                {{ $assignedVehicle->vehicle_type ?? 'Heavy Commercial Vehicle' }}
                @if($assignedVehicle->manufacturer || $assignedVehicle->model)
                    ({{ trim(($assignedVehicle->manufacturer ?? '') . ' ' . ($assignedVehicle->model ?? '')) }})
                @endif
            </div>

            <div class="d-flex align-items-center justify-content-center gap-2 text-muted micro-text font-monospace" style="font-size: 0.72rem;">
                <span>Code: <strong>{{ $assignedVehicle->vehicle_code ?? 'VEH-001' }}</strong></span>
                <span>&bull;</span>
                <span>Year: <strong>{{ $assignedVehicle->manufacturing_year ?? '2022' }}</strong></span>
                <span>&bull;</span>
                <span>Fuel: <strong>{{ $assignedVehicle->fuel_type ?? 'Diesel' }}</strong></span>
            </div>
        </div>

        <!-- 3. COMPLETE VEHICLE MASTER TECHNICAL SPECIFICATIONS -->
        <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-translucent pb-2">
                <span class="text-muted micro-text fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    📋 Fleet Vehicle Master Specifications
                </span>
                <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.68rem;">Verified Master</span>
            </div>

            <div class="row g-3 text-start" style="font-size: 0.8rem;">
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
                    <span class="text-dark fw-semibold">{{ $assignedVehicle->vehicle_type ?? 'Commercial Truck' }}</span>
                </div>
                <div class="col-6">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Manufacturer</span>
                    <span class="text-dark fw-semibold">{{ $assignedVehicle->manufacturer ?? 'Tata Motors' }}</span>
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

                <!-- Health & Insurance Status -->
                <div class="col-6">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Maintenance Status</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                        ✓ {{ $assignedVehicle->maintenance_status ?? 'Good & Active' }}
                    </span>
                </div>
                <div class="col-6">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Fitness Certificate Expiry</span>
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
    @else
        <!-- EMPTY STATE WHEN NO VEHICLE ASSIGNED -->
        <div class="card bg-white border border-translucent rounded-4 p-4 text-center shadow-sm">
            <div class="my-2">
                <img src="{{ asset('images/truck-3d.png') }}" alt="3D Truck" style="width: 100px; height: 100px; object-fit: contain; opacity: 0.5;">
            </div>
            <h6 class="fw-bold text-dark mb-1">NO VEHICLE ASSIGNED</h6>
            <p class="text-muted small mb-0 px-3" style="font-size: 0.82rem;">
                There is currently no vehicle assigned to your driver account. Please contact Transport Management.
            </p>
        </div>
    @endif

    <!-- 4. ACTION BAR TO GO TO DRIVER PROFILE -->
    <div class="card bg-white border border-translucent rounded-4 p-3 shadow-xs">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                    👤
                </div>
                <div>
                    <div class="fw-bold text-dark small mb-0">Driver Profile & Credentials</div>
                    <div class="text-muted micro-text" style="font-size: 0.72rem;">View Driver ID, License, Contact & Credentials</div>
                </div>
            </div>

            <a href="{{ route('driver-terminal.driver-profile', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
               class="btn btn-sm btn-primary rounded-pill px-3.5 fw-bold shadow-xs" style="font-size: 0.78rem;">
                View Profile
            </a>
        </div>
    </div>

</div>
@endsection
