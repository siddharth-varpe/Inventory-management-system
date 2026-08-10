@extends('driver-terminal.layouts.app')

@section('title', 'Driver Home')

@section('content')
<div class="row g-3">
    <!-- Header Greeting Card -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-3.5 shadow-sm">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-secondary small fw-medium">Welcome back,</span>
                    <h5 class="fw-extrabold text-white mb-0 mt-0.5">{{ $currentDriver->driver_name ?? 'Driver' }}</h5>
                    <div class="font-monospace text-info small fw-bold mt-1">
                        {{ $currentDriver->driver_code ?? 'DRV-000001' }}
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge {{ $currentDriver->status_badge_class ?? 'bg-success-subtle text-success border border-success-subtle' }} px-3 py-1.5 rounded-pill fs-7 fw-bold">
                        ● {{ $currentDriver->status_label ?? 'Available' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Today Summary Metrics -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-3 shadow-sm">
            <div class="text-secondary small fw-bold text-uppercase mb-2.5" style="letter-spacing: 0.5px;">Today Summary</div>
            <div class="row g-2 text-center">
                <div class="col-4">
                    <div class="bg-dark p-2.5 rounded-3 border border-secondary">
                        <div class="fs-4 fw-extrabold text-warning">{{ $assignedCount ?? 0 }}</div>
                        <div class="text-secondary micro-text fw-semibold text-uppercase">Assigned</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-dark p-2.5 rounded-3 border border-secondary">
                        <div class="fs-4 fw-extrabold text-info">{{ $dispatchedCount ?? 0 }}</div>
                        <div class="text-secondary micro-text fw-semibold text-uppercase">Dispatched</div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="bg-dark p-2.5 rounded-3 border border-secondary">
                        <div class="fs-4 fw-extrabold text-success">{{ $completedCount ?? 0 }}</div>
                        <div class="text-secondary micro-text fw-semibold text-uppercase">Completed</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current Delivery Section (Display Only) -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-3.5 shadow-sm">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-secondary small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Current Delivery</span>
                @if ($activeDelivery)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 font-monospace fs-7">
                        {{ strtoupper(str_replace('_', ' ', $activeDelivery->status)) }}
                    </span>
                @endif
            </div>

            @if ($activeDelivery)
                <div class="bg-dark p-3 rounded-3 border border-secondary">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-extrabold text-white fs-6 font-monospace">
                            {{ $activeDelivery->order_reference ?? $activeDelivery->request_number }}
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary fs-8">Display Only</span>
                    </div>

                    <div class="mb-2">
                        <div class="text-secondary micro-text">Customer</div>
                        <div class="text-white fw-bold small">
                            {{ $activeDelivery->customer_name ?? $activeDelivery->salesOrder->customer->company_name ?? 'N/A' }}
                        </div>
                    </div>

                    <div class="mb-2">
                        <div class="text-secondary micro-text">Delivery Address</div>
                        <div class="text-light small text-truncate">
                            📍 {{ $activeDelivery->delivery_address }}, {{ $activeDelivery->delivery_city }}
                        </div>
                    </div>

                    <div class="row g-2 mt-1">
                        <div class="col-6">
                            <div class="text-secondary micro-text">Vehicle</div>
                            <div class="text-info font-monospace fw-bold small">
                                🚚 {{ $assignedVehicle->vehicle_number ?? $activeDelivery->vehicle->vehicle_number ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-secondary micro-text">Weight / Vol</div>
                            <div class="text-light small">
                                {{ $activeDelivery->weight_kg ? number_format((float)$activeDelivery->weight_kg, 1).' kg' : 'Standard' }}
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-dark p-4 rounded-3 border border-secondary text-center">
                    <div class="fs-2 text-secondary mb-2">📦</div>
                    <div class="fw-bold text-white small">NO ACTIVE DELIVERY</div>
                    <div class="text-secondary micro-text mt-1">Your assigned deliveries will appear here.</div>
                </div>
            @endif
        </div>
    </div>

    <!-- Assigned Vehicle Card -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-3.5 shadow-sm">
            <div class="text-secondary small fw-bold text-uppercase mb-2.5" style="letter-spacing: 0.5px;">Current Assigned Vehicle</div>

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
                    <div class="text-muted micro-text mt-0.5">Vehicle allocation managed by Transport Management.</div>
                </div>
            @endif
        </div>
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
