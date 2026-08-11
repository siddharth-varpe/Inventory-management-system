@extends('driver-terminal.layouts.app')

@section('title', 'My Deliveries — Driver Terminal')

@section('content')
<div class="row g-3">
    <!-- Header Title & Search Bar -->
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-extrabold text-white mb-0">My Deliveries</h5>
                <span class="text-secondary small">Assigned tasks and active trips</span>
            </div>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold">
                {{ count($deliveries) }} Task{{ count($deliveries) !== 1 ? 's' : '' }}
            </span>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('driver-terminal.deliveries') }}" class="mb-3">
            <div class="input-group">
                <span class="input-group-text bg-slate-800 border-slate-700 text-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </span>
                <input type="text" name="search" class="form-control bg-slate-800 border-slate-700 text-white shadow-none placeholder-slate" placeholder="Search Order ID, Customer, Address..." value="{{ $search }}">
                @if(!empty($search))
                    <a href="{{ route('driver-terminal.deliveries') }}" class="btn btn-outline-secondary border-slate-700 text-secondary">Clear</a>
                @endif
                <button type="submit" class="btn btn-primary fw-bold px-3">Search</button>
            </div>
        </form>
    </div>

    <!-- Deliveries List -->
    <div class="col-12">
        @forelse($deliveries as $d)
            @php
                $isAssigned = in_array(strtolower($d->status), ['driver_vehicle_assigned', 'assigned']);
                $isDispatched = strtolower($d->status) === 'dispatched';
                $orderCode = $d->salesOrder?->order_number ?? $d->request_number ?? ('TRN-REQ-' . $d->id);
                $customerName = $d->salesOrder?->customer?->company_name ?? $d->customer_name ?? 'Primary Customer';
                $address = $d->delivery_address ?? 'Primary Customer Address';
                $vehicleReg = $d->vehicle?->vehicle_number ?? 'Assigned Vehicle';
                $expDate = $d->expected_delivery_date ? \Carbon\Carbon::parse($d->expected_delivery_date)->format('d M Y') : \Carbon\Carbon::now()->format('d M Y');
            @endphp

            <div class="card bg-slate-800 border-slate-700 rounded-3 shadow-sm mb-3 overflow-hidden">
                <div class="card-body p-3">
                    <!-- Top Info: Order ID & Status Badge -->
                    <div class="d-flex align-items-center justify-content-between border-bottom border-slate-700 pb-2.5 mb-2.5">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-sm rounded-2 bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold small">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                                    <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.847 3.5 8 6.099l6.153-2.599-5.967-2.387zM15 4.239l-6.5 2.746v6.918l6.5-2.746V4.239zM1 4.239v6.918l6.5 2.746V6.985L1 4.239z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="font-monospace text-info fw-extrabold fs-6 d-block">{{ $orderCode }}</span>
                                <span class="text-secondary micro-text">Transport Ref: {{ $d->request_number ?: 'TRN-' . $d->id }}</span>
                            </div>
                        </div>

                        <div>
                            @if($isAssigned)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill fw-extrabold micro-text">
                                    ● ASSIGNED
                                </span>
                            @elseif($isDispatched)
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 rounded-pill fw-extrabold micro-text">
                                    ✓ DISPATCHED
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill fw-extrabold micro-text">
                                    {{ strtoupper($d->status) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="row g-2 mb-3 text-start">
                        <div class="col-12">
                            <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Customer</span>
                            <span class="text-white fw-bold small">{{ $customerName }}</span>
                        </div>
                        <div class="col-12">
                            <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Destination</span>
                            <span class="text-slate-300 small">{{ $address }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Assigned Vehicle</span>
                            <span class="font-monospace text-cyan small fw-semibold">{{ $vehicleReg }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-secondary micro-text d-block text-uppercase fw-semibold">Expected Delivery</span>
                            <span class="text-slate-300 small">{{ $expDate }}</span>
                        </div>
                    </div>

                    <!-- Card Action Button -->
                    <a href="{{ route('driver-terminal.deliveries.show', $d->id) }}" class="btn btn-slate-700 hover-btn-primary text-white w-100 fw-bold py-2 d-flex align-items-center justify-content-center gap-2">
                        <span>VIEW DELIVERY</span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right-short" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M4 8a.5.5 0 0 1 .5-.5h5.793L8.146 5.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L10.293 8.5H4.5A.5.5 0 0 1 4 8z"/>
                        </svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="card bg-slate-800 border-slate-700 rounded-3 p-4 text-center">
                <div class="avatar-md mx-auto mb-3 rounded-circle bg-slate-700 text-secondary d-flex align-items-center justify-content-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-inbox" viewBox="0 0 16 16">
                        <path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4H4.98zm-1.17-.437A1.5 1.5 0 0 1 4.98 3h6.04a1.5 1.5 0 0 1 1.17.563l3.7 4.625a1 1 0 0 1 .23.64V13a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V8.826a1 1 0 0 1 .23-.64l3.7-4.625zM14 9H10.126a2.5 2.5 0 0 1-4.252 0H2v4a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V9z"/>
                    </svg>
                </div>
                <h6 class="fw-bold text-white mb-1">NO ASSIGNED DELIVERIES</h6>
                <p class="text-secondary small mb-0">You currently have no deliveries assigned to you.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
