@extends('driver-terminal.layouts.app')

@section('title', 'Driver Profile — Driver Terminal')

@section('content')
<div class="vstack gap-4">

    <!-- 1. PAGE HEADER BAR -->
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-black text-dark mb-0 fs-5">Driver Profile</h5>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">Authenticated Driver Master Credentials & Account Details</p>
        </div>
        <a href="{{ route('driver-terminal.profile', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
           class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-flex align-items-center gap-1 shadow-xs" style="font-size: 0.78rem;">
            <span>🚗 Vehicle Page</span>
            <span>&rsaquo;</span>
        </a>
    </div>

    <!-- 2. DRIVER IDENTITY HEADER CARD -->
    <div class="card bg-white border border-translucent rounded-4 p-4 text-center shadow-sm">
        <div class="mb-2 d-flex justify-content-center">
            <div class="avatar-lg rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" 
                 style="width: 70px; height: 70px; font-size: 2rem;">
                👤
            </div>
        </div>

        <h4 class="fw-extrabold text-dark mb-0 fs-4">{{ $currentDriver->driver_name ?? 'Siddharth Varpe' }}</h4>
        
        <div class="font-monospace text-primary fw-extrabold fs-6 mt-1 mb-2" style="color: #2563eb !important;">
            {{ $currentDriver->driver_code ?? 'DRV-000001' }}
        </div>

        <div class="d-flex align-items-center justify-content-center gap-2">
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold" style="font-size: 0.75rem;">
                ● {{ $currentDriver->status_label ?? 'On Delivery' }}
            </span>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill fw-bold font-monospace" style="font-size: 0.75rem;">
                ⭐ {{ number_format($currentDriver->performance_rating ?? 5.0, 2) }} Rating
            </span>
        </div>
    </div>

    <!-- 3. DRIVER MASTER CREDENTIALS CARD -->
    <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm">
        <div class="d-flex align-items-center justify-content-between mb-2.5">
            <span class="text-muted micro-text fw-bold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                DRIVER MASTER CREDENTIALS
            </span>
            <span class="badge bg-light text-dark font-monospace border" style="font-size: 0.68rem;">Read-Only</span>
        </div>

        <div class="bg-dark p-3.5 rounded-3 border border-secondary text-white">
            <div class="row g-2.5" style="font-size: 0.8rem;">
                <div class="col-5 text-secondary">Employee ID:</div>
                <div class="col-7 text-white font-monospace fw-bold text-end">{{ $currentDriver->employee_id ?? 'EMP-DRV-0001' }}</div>

                <div class="col-5 text-secondary">License Class:</div>
                <div class="col-7 text-light text-end">{{ $currentDriver->license_class ?? 'Heavy Commercial (HMV)' }}</div>

                <div class="col-5 text-secondary">License Number:</div>
                <div class="col-7 text-light font-monospace text-end">{{ $currentDriver->driving_license_number ?? 'MH-12-7777777' }}</div>

                <div class="col-5 text-secondary">License Expiry:</div>
                <div class="col-7 text-light font-monospace text-end">
                    {{ $currentDriver->license_expiry_date ? \Carbon\Carbon::parse($currentDriver->license_expiry_date)->format('d M Y') : '08 Aug 2029' }}
                </div>

                <div class="col-5 text-secondary">Phone Number:</div>
                <div class="col-7 text-white font-monospace text-end">{{ $currentDriver->phone_number ?? '+91 90216 53893' }}</div>

                <div class="col-5 text-secondary">Registered Email:</div>
                <div class="col-7 text-light text-end text-truncate">{{ $currentDriver->email ?? 'varpes380@gmail.com' }}</div>

                <div class="col-5 text-secondary">Address:</div>
                <div class="col-7 text-light text-end text-truncate">{{ $currentDriver->address ?? 'abc, xyz' }}</div>

                <div class="col-5 text-secondary">Emergency Contact:</div>
                <div class="col-7 text-warning text-end font-semibold">
                    {{ $currentDriver->emergency_contact_name ?? 'Siddharth Varpe' }} ({{ $currentDriver->emergency_contact_number ?? $currentDriver->emergency_contact ?? '+91 99227 09726' }})
                </div>
            </div>
        </div>
    </div>

    <!-- 4. DELIVERY PERFORMANCE & STATS SUMMARY -->
    <div class="card bg-white border border-translucent rounded-4 p-3.5 shadow-sm text-center">
        <span class="text-muted micro-text fw-bold text-uppercase d-block mb-2 text-start" style="font-size: 0.72rem; letter-spacing: 0.5px;">
            📊 Driver Delivery Performance
        </span>

        <div class="row row-cols-3 g-2">
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fw-black text-dark fs-5">{{ $completedTripsCount }}</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">Completed</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fw-black text-warning-emphasis fs-5">{{ $activeTripsCount }}</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">In Progress</div>
                </div>
            </div>
            <div class="col">
                <div class="p-2 bg-light rounded-3 border">
                    <div class="fw-black text-primary fs-5">{{ $totalTripsCount }}</div>
                    <div class="text-muted micro-text" style="font-size: 0.68rem;">Total Assigned</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. ACCOUNT ACTIONS CARD -->
    <div class="vstack gap-2">
        <a href="{{ route('driver-terminal.profile', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
           class="btn btn-outline-primary btn-lg w-100 fw-bold rounded-4 shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.88rem;">
            <span>🚗</span>
            <span>View Assigned Vehicle & 3D Model</span>
        </a>

        <form action="{{ route('driver-terminal.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-lg w-100 fw-bold rounded-4 shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2" style="font-size: 0.88rem;">
                <span>🚪</span>
                <span>Sign Out from Terminal</span>
            </button>
        </form>
    </div>

</div>
@endsection
