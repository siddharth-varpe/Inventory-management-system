@extends('driver-terminal.layouts.app')

@section('title', 'Driver Profile')

@section('content')
<div class="row g-3">
    <!-- Driver Profile Header Card -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-4 text-center shadow-sm">
            <div class="mb-2">
                <span class="fs-1">👤</span>
            </div>
            <h4 class="fw-extrabold text-white mb-0">{{ $currentDriver->driver_name ?? 'Driver Profile' }}</h4>
            <div class="font-monospace text-info small fw-bold mt-1 mb-2">
                {{ $currentDriver->driver_code ?? 'N/A' }}
            </div>
            <div>
                <span class="badge {{ $currentDriver->status_badge_class ?? 'bg-success-subtle text-success border border-success-subtle' }} px-3 py-1 rounded-pill fs-7 fw-bold">
                    ● {{ $currentDriver->status_label ?? 'Available' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Master Driver Details (Read-Only) -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-3.5 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Driver Master Credentials</span>
                <span class="badge bg-secondary-subtle text-secondary fs-8">Read-Only</span>
            </div>

            <div class="bg-dark p-3 rounded-3 border border-secondary">
                <div class="row g-2.5">
                    <div class="col-5 text-secondary small">Employee ID:</div>
                    <div class="col-7 text-white font-monospace fw-bold small text-end">{{ $currentDriver->employee_id ?? 'N/A' }}</div>

                    <div class="col-5 text-secondary small">License Class:</div>
                    <div class="col-7 text-light small text-end">{{ $currentDriver->license_class ?? 'Heavy Commercial' }}</div>

                    @if(!empty($currentDriver->driving_license_number))
                    <div class="col-5 text-secondary small">License Number:</div>
                    <div class="col-7 text-light font-monospace small text-end">{{ $currentDriver->driving_license_number }}</div>
                    @endif

                    <div class="col-5 text-secondary small">Phone Number:</div>
                    <div class="col-7 text-white small text-end">{{ $currentDriver->phone_number ?? 'N/A' }}</div>

                    <div class="col-5 text-secondary small">Registered Email:</div>
                    <div class="col-7 text-light small text-end text-truncate">{{ $currentDriver->email ?? 'N/A' }}</div>

                    @if(!empty($currentDriver->address))
                    <div class="col-5 text-secondary small">Address:</div>
                    <div class="col-7 text-light small text-end text-truncate">{{ $currentDriver->address }}</div>
                    @endif

                    @if(!empty($currentDriver->emergency_contact_number) || !empty($currentDriver->emergency_contact_name))
                    <div class="col-5 text-secondary small">Emergency Contact:</div>
                    <div class="col-7 text-warning small text-end">
                        {{ $currentDriver->emergency_contact_name ?? '' }} ({{ $currentDriver->emergency_contact_number ?? $currentDriver->emergency_contact ?? 'N/A' }})
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Vehicle Details (Read-Only) -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-3.5 shadow-sm">
            <div class="text-secondary small fw-bold text-uppercase mb-2.5" style="letter-spacing: 0.5px;">Assigned Vehicle</div>

            @if ($assignedVehicle)
                <div class="bg-dark p-3 rounded-3 border border-secondary d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-3">
                        <div class="fs-2">🚚</div>
                        <div>
                            <div class="fw-extrabold text-white font-monospace fs-6">{{ $assignedVehicle->vehicle_number }}</div>
                            <div class="text-secondary small">{{ $assignedVehicle->vehicle_type ?? 'Commercial Vehicle' }}</div>
                        </div>
                    </div>
                    <span class="badge bg-success-subtle text-success border border-success-subtle fs-8">ASSIGNED</span>
                </div>
            @else
                <div class="bg-dark p-3 rounded-3 border border-secondary text-center">
                    <div class="fw-bold text-secondary small">NO VEHICLE ASSIGNED</div>
                    <div class="text-muted micro-text mt-0.5">Contact Transport Management to update vehicle assignment.</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Logout Action Card -->
    <div class="col-12">
        <form action="{{ route('driver-terminal.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-danger btn-lg w-100 fw-bold rounded-4 shadow-sm">
                Sign Out from Terminal
            </button>
        </form>
    </div>
</div>
@endsection

@section('styles')
<style>
    .micro-text {
        font-size: 0.7rem;
    }
    .fs-7 {
        font-size: 0.75rem;
    }
    .fs-8 {
        font-size: 0.7rem;
    }
</style>
@endsection
