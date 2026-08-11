@extends('driver-terminal.layouts.app')

@php
    $orderCode = $delivery->order_reference ?? $delivery->request_number ?? ('TRN-REQ-' . $delivery->id);
    $customerName = $delivery->customer_name ?? $delivery->salesOrder?->customer?->company_name ?? 'Primary Customer';
    $address = $delivery->delivery_address ?? 'Primary Customer Address';
    $city = $delivery->delivery_city ?? $delivery->city ?? 'Local';
    $contact = $delivery->phone_number ?? $delivery->salesOrder?->customer?->phone ?? 'N/A';
    $vehicleReg = $delivery->vehicle?->vehicle_number ?? 'Assigned Vehicle';
    $vehicleType = $delivery->vehicle?->vehicle_type ?? 'Transport Vehicle';
    $expDate = $delivery->expected_delivery_date ? \Carbon\Carbon::parse($delivery->expected_delivery_date)->format('d M Y') : \Carbon\Carbon::now()->format('d M Y');
    $orderDate = $delivery->salesOrder?->order_date ? \Carbon\Carbon::parse($delivery->salesOrder->order_date)->format('d M Y') : \Carbon\Carbon::parse($delivery->created_at)->format('d M Y');
    $isCompleted = in_array(strtolower($delivery->status), ['delivered', 'completed']);
    $isDispatched = strtolower($delivery->status) === 'dispatched';
    $isAssigned = in_array(strtolower($delivery->status), ['driver_vehicle_assigned', 'assigned']);
@endphp

@section('title', 'Trip Details ' . $orderCode . ' — Driver Terminal')

@section('content')
<div class="vstack gap-3.5">
    <!-- Top Back Header Bar -->
    <div class="d-flex align-items-center justify-content-between">
        <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
           class="btn btn-sm bg-white border border-translucent rounded-pill px-3 py-1.5 fw-bold text-dark shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 0.8rem;">
            <span>&lsaquo;</span>
            <span>Back to Trips</span>
        </a>

        <div>
            @if($isCompleted)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold" style="font-size: 0.75rem;">
                    ✓ COMPLETED
                </span>
            @elseif($isDispatched)
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold" style="font-size: 0.75rem;">
                    🚀 IN TRANSIT
                </span>
            @else
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold" style="font-size: 0.75rem;">
                    ● ASSIGNED
                </span>
            @endif
        </div>
    </div>

    <!-- Main Delivery Information Card -->
    <div class="card bg-white border border-translucent rounded-4 shadow-sm">
        <div class="card-body p-3.5">
            <div class="d-flex align-items-center justify-content-between border-bottom border-translucent pb-3 mb-3">
                <div>
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Trip Reference</span>
                    <h5 class="font-monospace text-dark fw-black mb-0 fs-5">{{ $orderCode }}</h5>
                </div>
                <div class="text-end">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Scheduled Date</span>
                    <span class="text-dark fw-bold small font-monospace">{{ $expDate }}</span>
                </div>
            </div>

            <!-- Customer & Delivery Address -->
            <div class="mb-3 text-start">
                <span class="text-muted micro-text d-block text-uppercase fw-semibold mb-1" style="font-size: 0.7rem;">Consignee / Destination</span>
                <h6 class="text-dark fw-bold mb-1 fs-6">{{ $customerName }}</h6>
                <p class="text-body small mb-2" style="font-size: 0.82rem;">
                    📍 {{ $address }}, {{ $city }}
                </p>
                <div class="d-flex align-items-center gap-2 small text-muted">
                    <span>📞</span>
                    <span class="text-dark font-monospace fw-bold">{{ $contact }}</span>
                </div>
            </div>

            <!-- Package Details Grid -->
            <div class="row g-2 p-3 bg-light rounded-3 border border-translucent text-start mb-3">
                <div class="col-6">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Package & Weight</span>
                    <span class="text-dark fw-bold small d-block">{{ $delivery->package_count ?? 1 }} Cartons</span>
                    <span class="text-muted micro-text" style="font-size: 0.7rem;">{{ $delivery->weight_kg ?? 0 }} kg | {{ $delivery->volume_m3 ?? 0 }} m³</span>
                </div>
                <div class="col-6">
                    <span class="text-muted micro-text d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Assigned Vehicle</span>
                    <span class="font-monospace text-primary fw-bold small d-block">{{ $vehicleReg }}</span>
                    <span class="text-muted micro-text" style="font-size: 0.7rem;">{{ $vehicleType }}</span>
                </div>
            </div>

            <!-- Informational Status Banner -->
            <div class="p-3 rounded-3 text-center border" 
                 style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                <div class="fw-bold text-dark small mb-1">
                    ℹ️ Trip Status: {{ strtoupper($delivery->status_label ?? $delivery->status) }}
                </div>
                <p class="text-muted micro-text mb-0" style="font-size: 0.75rem;">
                    This trip is assigned to your driver account <strong>{{ $currentDriver->driver_code }}</strong> under Transport Management.
                </p>
            </div>

            @if($isAssigned)
                <div class="mt-3 pt-3 border-top border-translucent">
                    <button type="button" class="btn btn-warning text-dark fw-bold w-100 py-2.5 rounded-3" data-bs-toggle="modal" data-bs-target="#confirmAcceptModal">
                        ACCEPT DELIVERY
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

@if($isAssigned)
<!-- Accept Delivery Confirmation Modal -->
<div class="modal fade" id="confirmAcceptModal" tabindex="-1" aria-labelledby="confirmAcceptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-white border border-translucent text-dark">
            <div class="modal-header border-bottom">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="confirmAcceptModalLabel">
                    Confirm Delivery Acceptance
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <p class="small text-muted mb-3">
                    Are you sure you want to accept this delivery? This action will set the transport status to <strong class="text-primary">DISPATCHED</strong>.
                </p>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-light border text-secondary fw-bold px-3" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('driver-terminal.deliveries.accept', ['driver_code' => strtolower($currentDriver->driver_code), 'id' => $delivery->id]) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning text-dark fw-extrabold px-4">
                        ACCEPT DELIVERY
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
