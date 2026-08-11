@extends('driver-terminal.layouts.app')

@php
    $orderCode = $delivery->salesOrder?->order_number ?? $delivery->request_number ?? ('TRN-REQ-' . $delivery->id);
    $customerName = $delivery->salesOrder?->customer?->company_name ?? $delivery->customer_name ?? 'Primary Customer';
    $address = $delivery->delivery_address ?? 'Primary Customer Address';
    $contact = $delivery->phone_number ?? $delivery->salesOrder?->customer?->phone ?? 'N/A';
    $vehicleReg = $delivery->vehicle?->vehicle_number ?? 'Assigned Vehicle';
    $vehicleType = $delivery->vehicle?->vehicle_type ?? 'Transport Vehicle';
    $expDate = $delivery->expected_delivery_date ? \Carbon\Carbon::parse($delivery->expected_delivery_date)->format('d M Y') : \Carbon\Carbon::now()->format('d M Y');
    $orderDate = $delivery->salesOrder?->order_date ? \Carbon\Carbon::parse($delivery->salesOrder->order_date)->format('d M Y') : \Carbon\Carbon::parse($delivery->created_at)->format('d M Y');
    $isAssigned = in_array(strtolower($delivery->status), ['driver_vehicle_assigned', 'assigned']);
    $isDispatched = strtolower($delivery->status) === 'dispatched';
    $acceptedAtFormatted = $delivery->dispatched_at ? \Carbon\Carbon::parse($delivery->dispatched_at)->format('d M Y, h:i A') : ($delivery->accepted_at ? \Carbon\Carbon::parse($delivery->accepted_at)->format('d M Y, h:i A') : null);
@endphp

@section('title', 'Delivery ' . $orderCode . ' — Driver Terminal')

@section('content')
<div class="row g-3">
    <!-- Top Navigation & Order Title -->
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" class="btn btn-slate-800 border-slate-700 text-slate-300 btn-sm fw-bold px-3 py-1.5 d-inline-flex align-items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chevron-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/>
                </svg>
                <span>Back to Deliveries</span>
            </a>

            <div>
                @if($isAssigned)
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-extrabold micro-text">
                        ● ASSIGNED
                    </span>
                @elseif($isDispatched)
                    <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill fw-extrabold micro-text">
                        ✓ DISPATCHED
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1.5 rounded-pill fw-extrabold micro-text">
                        {{ strtoupper($delivery->status) }}
                    </span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-3 p-3 rounded-3 small fw-bold">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info bg-info-subtle text-info border-info-subtle mb-3 p-3 rounded-3 small fw-bold">
                ℹ️ {{ session('info') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-3 p-3 rounded-3 small fw-bold">
                ⚠️ {{ session('error') }}
            </div>
        @endif
    </div>

    <!-- Main Delivery Information Card -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-3 shadow-sm mb-3">
            <div class="card-body p-3">
                <div class="d-flex align-items-center justify-content-between border-bottom border-slate-700 pb-3 mb-3">
                    <div>
                        <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Order Reference</span>
                        <h4 class="font-monospace text-info fw-extrabold mb-0">{{ $orderCode }}</h4>
                    </div>
                    <div class="text-end">
                        <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Order Date</span>
                        <span class="text-white small font-monospace">{{ $orderDate }}</span>
                    </div>
                </div>

                <!-- Customer & Delivery Address -->
                <div class="mb-3 text-start">
                    <span class="text-secondary micro-text d-block text-uppercase fw-semibold mb-1">Customer / Consignee</span>
                    <h6 class="text-white fw-bold mb-1">{{ $customerName }}</h6>
                    <p class="text-slate-300 small mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-geo-alt-fill text-danger me-1" viewBox="0 0 16 16">
                            <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10zm0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6z"/>
                        </svg>
                        {{ $address }}
                    </p>
                    <div class="d-flex align-items-center gap-2 small text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-telephone-fill text-info" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z"/>
                        </svg>
                        <span class="text-white font-monospace">{{ $contact }}</span>
                    </div>
                </div>

                <!-- Vehicle & Schedule Grid -->
                <div class="row g-2 p-2.5 bg-slate-900 rounded-3 border border-slate-700 text-start">
                    <div class="col-6">
                        <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Assigned Vehicle</span>
                        <span class="font-monospace text-cyan fw-bold small d-block">{{ $vehicleReg }}</span>
                        <span class="text-secondary micro-text">{{ $vehicleType }}</span>
                    </div>
                    <div class="col-6">
                        <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Expected Delivery</span>
                        <span class="text-white fw-bold small d-block">{{ $expDate }}</span>
                        <span class="text-secondary micro-text">Scheduled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operational Execution & Accept Action Card -->
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-3 shadow-sm p-3 text-center">
            @if($isAssigned)
                <div class="mb-3">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1.5 rounded-pill fw-bold small">
                        ● READY FOR DRIVER ACCEPTANCE
                    </span>
                    <p class="text-secondary micro-text mt-2 mb-0">
                        Review delivery details above and tap Accept Delivery to confirm trip dispatch.
                    </p>
                </div>

                <!-- Accept Delivery Action Button -->
                <button type="button" class="btn btn-warning hover-btn-warning text-dark fw-extrabold w-100 py-3 rounded-3 fs-6 d-flex align-items-center justify-content-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#confirmAcceptModal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l4.992-5.99a.75.75 0 0 0-.018-1.042z"/>
                    </svg>
                    <span>ACCEPT DELIVERY</span>
                </button>
            @elseif($isDispatched)
                <div class="py-2">
                    <div class="avatar-md mx-auto mb-2 rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-patch-check-fill" viewBox="0 0 16 16">
                            <path d="M10.067.87a2.89 2.89 0 0 0-4.134 0l-.622.638-.89-.011a2.89 2.89 0 0 0-2.924 2.924l.01.89-.636.622a2.89 2.89 0 0 0 0 4.134l.637.622-.011.89a2.89 2.89 0 0 0 2.924 2.924l.89-.01.622.636a2.89 2.89 0 0 0 4.134 0l.622-.637.89.011a2.89 2.89 0 0 0 2.924-2.924l-.01-.89.636-.622a2.89 2.89 0 0 0 0-4.134l-.637-.622.011-.89a2.89 2.89 0 0 0-2.924-2.924l-.89.01-.622-.636zm.287 5.984-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7 8.793l2.646-2.647a.5.5 0 0 1 .708.708z"/>
                        </svg>
                    </div>
                    <h6 class="fw-extrabold text-success mb-1">✓ DISPATCHED</h6>
                    <p class="text-secondary small mb-0">
                        Accepted: <strong class="text-white">{{ $acceptedAtFormatted ?: 'Just now' }}</strong>
                    </p>
                    <div class="mt-3 p-2 bg-slate-900 rounded-3 text-slate-400 micro-text">
                        ℹ️ Transport status updated to DISPATCHED on Transport Portal. Active delivery execution state confirmed.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Accept Delivery Confirmation Modal -->
@if($isAssigned)
<div class="modal fade" id="confirmAcceptModal" tabindex="-1" aria-labelledby="confirmAcceptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-slate-800 border-slate-700 text-white">
            <div class="modal-header border-slate-700">
                <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="confirmAcceptModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-question-circle text-warning" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M5.255 5.786a.237.237 0 0 0 .241.247h.825c.138 0 .248-.113.266-.25.09-.656.54-1.134 1.342-1.134.686 0 1.276.38 1.276 1.047 0 .47-.286.74-.834 1.082l-.46.29a2.14 2.14 0 0 0-.613.682l-.022.046c-.056.11-.087.23-.087.353v.154c0 .147.119.266.266.266h.81c.148 0 .267-.12.267-.267v-.087c0-.184.07-.333.242-.445l.434-.276c.74-.467 1.341-1.037 1.341-2.12 0-1.407-1.16-2.22-2.584-2.22-1.42 0-2.52.883-2.673 2.183zM9.283 12.164a.8.8 0 1 0-1.6 0 .8.8 0 0 0 1.6 0z"/>
                    </svg>
                    Confirm Delivery Acceptance
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <p class="small text-slate-300 mb-3">
                    Are you sure you want to accept this delivery? This action will set the transport status to <strong class="text-info">DISPATCHED</strong> and notify Transport Management.
                </p>

                <div class="bg-slate-900 rounded-3 p-3 mb-3 border border-slate-700">
                    <div class="row g-2 small">
                        <div class="col-4 text-secondary">Order ID:</div>
                        <div class="col-8 font-monospace text-info fw-bold">{{ $orderCode }}</div>

                        <div class="col-4 text-secondary">Customer:</div>
                        <div class="col-8 text-white font-semibold">{{ $customerName }}</div>

                        <div class="col-4 text-secondary">Destination:</div>
                        <div class="col-8 text-slate-300">{{ $address }}</div>

                        <div class="col-4 text-secondary">Vehicle:</div>
                        <div class="col-8 font-monospace text-cyan fw-bold">{{ $vehicleReg }}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-slate-700">
                <button type="button" class="btn btn-slate-700 text-slate-300 fw-bold px-3" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('driver-terminal.deliveries.accept', ['driver_code' => strtolower($currentDriver->driver_code), 'id' => $delivery->id]) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning hover-btn-warning text-dark fw-extrabold px-4">
                        ACCEPT DELIVERY
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
