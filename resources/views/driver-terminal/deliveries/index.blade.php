@extends('driver-terminal.layouts.app')

@section('title', 'My Deliveries — Driver Terminal')

@section('content')
<div class="vstack gap-3.5">

    <!-- 1. PAGE HEADER -->
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-black text-dark mb-0 fs-5">My Deliveries</h5>
            <p class="text-muted small mb-0" style="font-size: 0.78rem;">All your assigned trips and their status.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm bg-white border border-translucent rounded-circle shadow-xs p-2" 
                    onclick="document.getElementById('searchCollapse').classList.toggle('d-none')" title="Toggle Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- 2. STATUS FILTER TABS -->
    <div class="overflow-auto pb-1" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
        <div class="nav nav-pills flex-nowrap gap-2" style="width: max-content;">
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'all', 'search' => $search]) }}" 
               class="nav-link px-3 py-1.5 rounded-pill fw-bold small text-nowrap {{ $activeTab === 'all' ? 'active bg-primary text-white shadow-xs' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.8rem;">
                All Trips <span class="badge rounded-pill bg-white text-dark ms-1" style="font-size: 0.68rem;">{{ $totalCount }}</span>
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'ongoing', 'search' => $search]) }}" 
               class="nav-link px-3 py-1.5 rounded-pill fw-bold small text-nowrap {{ $activeTab === 'ongoing' ? 'active bg-success text-white shadow-xs' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.8rem;">
                Ongoing <span class="badge rounded-pill bg-white text-dark ms-1" style="font-size: 0.68rem;">{{ $ongoingCount }}</span>
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'completed', 'search' => $search]) }}" 
               class="nav-link px-3 py-1.5 rounded-pill fw-bold small text-nowrap {{ $activeTab === 'completed' ? 'active bg-primary text-white shadow-xs' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.8rem;">
                Completed <span class="badge rounded-pill bg-white text-dark ms-1" style="font-size: 0.68rem;">{{ $completedCount }}</span>
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'upcoming', 'search' => $search]) }}" 
               class="nav-link px-3 py-1.5 rounded-pill fw-bold small text-nowrap {{ $activeTab === 'upcoming' ? 'active bg-warning text-dark shadow-xs' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.8rem;">
                Upcoming <span class="badge rounded-pill bg-white text-dark ms-1" style="font-size: 0.68rem;">{{ $upcomingCount }}</span>
            </a>
        </div>
    </div>

    <!-- 3. SUMMARY METRICS ROW (4 CARDS) -->
    <div class="row row-cols-2 row-cols-sm-4 g-2 text-center">
        <!-- Metric 1: Total Trips Assigned -->
        <div class="col">
            <div class="p-2.5 bg-white border border-translucent rounded-3 shadow-xs h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; background-color: #eff6ff; color: #2563eb;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-briefcase" viewBox="0 0 16 16"><path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.5 6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 .5-.5z"/></svg>
                </div>
                <div class="fw-black text-dark fs-5 lh-1 mb-0.5">{{ $totalCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.68rem; line-height: 1.1;">Total Trips<br>Assigned</div>
            </div>
        </div>

        <!-- Metric 2: Ongoing Trips In Progress -->
        <div class="col">
            <div class="p-2.5 bg-white border border-translucent rounded-3 shadow-xs h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; background-color: #ecfdf5; color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-play-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M6.271 5.055a.5.5 0 0 1 .52.038l3.5 2.5a.5.5 0 0 1 0 .814l-3.5 2.5A.5.5 0 0 1 6 10.5v-5a.5.5 0 0 1 .271-.445z"/></svg>
                </div>
                <div class="fw-black text-dark fs-5 lh-1 mb-0.5">{{ $ongoingCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.68rem; line-height: 1.1;">Ongoing Trips<br>In Progress</div>
            </div>
        </div>

        <!-- Metric 3: Completed Trips -->
        <div class="col">
            <div class="p-2.5 bg-white border border-translucent rounded-3 shadow-xs h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; background-color: #fffbeb; color: #f59e0b;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02-.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>
                </div>
                <div class="fw-black text-dark fs-5 lh-1 mb-0.5">{{ $completedCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.68rem; line-height: 1.1;">Completed<br>Trips</div>
            </div>
        </div>

        <!-- Metric 4: Upcoming Trips -->
        <div class="col">
            <div class="p-2.5 bg-white border border-translucent rounded-3 shadow-xs h-100 d-flex flex-column align-items-center justify-content-center">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-1" style="width: 36px; height: 36px; background-color: #f3e8ff; color: #9333ea;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16"><path d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.758.21 1.119.37l-.353.905zM8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3.5A.5.5 0 0 1 8 8V4z"/><path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z"/></svg>
                </div>
                <div class="fw-black text-dark fs-5 lh-1 mb-0.5">{{ $upcomingCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.68rem; line-height: 1.1;">Upcoming<br>Trips</div>
            </div>
        </div>
    </div>

    <!-- 4. SEARCH FORM (COLLAPSIBLE / INLINE) -->
    <div id="searchCollapse" class="{{ empty($search) ? 'd-none' : '' }}">
        <form method="GET" action="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" class="row g-2">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="col-12">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-translucent text-muted">🔍</span>
                    <input type="text" name="search" class="form-control bg-white border-translucent" 
                           placeholder="Search by Order ID, Customer, Address..." value="{{ $search }}">
                    @if(!empty($search))
                        <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => $activeTab]) }}" 
                           class="btn btn-outline-secondary">Clear</a>
                    @endif
                    <button type="submit" class="btn btn-primary fw-bold px-3">Search</button>
                </div>
            </div>
        </form>
    </div>

    <!-- 5. DELIVERY CARDS LIST -->
    <div class="vstack gap-3">
        @forelse($deliveries as $d)
            @php
                $isCompleted = in_array(strtolower($d->status), ['delivered', 'completed']);
                $isOngoing = in_array(strtolower($d->status), ['dispatched', 'in_transit', 'arrived']);
                $isUpcoming = in_array(strtolower($d->status), ['driver_vehicle_assigned', 'assigned']);

                $stripColor = $isCompleted ? '#2563eb' : ($isOngoing ? '#10b981' : '#f59e0b');
                $badgeBg = $isCompleted ? 'bg-primary-subtle text-primary border-primary-subtle' : ($isOngoing ? 'bg-success-subtle text-success border-success-subtle' : 'bg-warning-subtle text-warning-emphasis border-warning-subtle');
                $badgeText = $isCompleted ? 'Completed' : ($isOngoing ? 'Ongoing' : 'Assigned');
                $dotColor = $isCompleted ? '#2563eb' : ($isOngoing ? '#10b981' : '#f59e0b');

                $orderRef = $d->order_reference ?? $d->request_number ?? ('TRN-' . $d->id);
                $customerName = $d->customer_name ?? $d->salesOrder?->customer?->company_name ?? 'Primary Customer';
                $address = $d->delivery_address ?? 'No address specified';
                $city = $d->delivery_city ?? $d->city ?? 'Local';
                $packages = ($d->package_count ?? 1) . ' Cartons · ' . ($d->weight_kg ?? 0) . ' kg';
                $expDate = $d->expected_delivery_date ? \Carbon\Carbon::parse($d->expected_delivery_date)->format('d M Y') : 'Scheduled Today';
            @endphp

            <div class="card bg-white border border-translucent rounded-4 shadow-sm overflow-hidden" 
                 style="border-left: 5px solid {{ $stripColor }} !important;">
                <div class="card-body p-3.5">
                    <!-- Card Top Row: ID & Status Pill -->
                    <div class="d-flex align-items-center justify-content-between mb-2 border-bottom pb-2">
                        <div class="fw-black font-monospace text-dark fs-6">
                            {{ $orderRef }}
                            @if($d->request_number && $d->request_number !== $orderRef)
                                <span class="d-block font-monospace text-muted micro-text fw-semibold" style="font-size:0.7rem;">Ref: {{ $d->request_number }}</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="badge rounded-pill px-2.5 py-1 font-monospace border {{ $badgeBg }}" style="font-size: 0.72rem;">
                                ● {{ strtoupper($badgeText) }}
                            </span>
                        </div>
                    </div>

                    <!-- Destination Row -->
                    <div class="d-flex align-items-start gap-2 mb-2">
                        <span class="fs-6 mt-0.5">📍</span>
                        <div class="flex-grow-1">
                            <div class="fw-bold text-dark fs-6 lh-sm">{{ $customerName }}</div>
                            <div class="text-muted small text-truncate mt-0.5" style="font-size: 0.78rem; max-width: 280px;">
                                {{ $address }}, {{ $city }}
                            </div>
                            <div class="text-secondary micro-text mt-1 font-monospace" style="font-size: 0.7rem;">
                                {{ $packages }}
                            </div>
                        </div>
                    </div>

                    <!-- Date & Action Footer Row -->
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top border-translucent mt-2">
                        <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                            📅 {{ $expDate }}
                        </div>

                        <a href="{{ route('driver-terminal.deliveries.show', ['driver_code' => strtolower($currentDriver->driver_code), 'id' => $d->id]) }}" 
                           class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold d-flex align-items-center gap-1" style="font-size: 0.78rem;">
                            <span>View Details</span>
                            <span>&rsaquo;</span>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <!-- PROFESSIONAL EMPTY STATE -->
            <div class="card bg-white border border-translucent rounded-4 p-4 text-center shadow-sm">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <span class="fs-2">📦</span>
                </div>
                <h6 class="fw-bold text-dark mb-1">NO ASSIGNED DELIVERIES</h6>
                <p class="text-muted small mb-0 px-3" style="font-size: 0.82rem;">
                    No deliveries found. You currently have no deliveries assigned to you.
                </p>
                @if(!empty($search) || $activeTab !== 'all')
                    <div class="mt-3">
                        <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
                           class="btn btn-sm btn-outline-secondary px-3 rounded-pill fw-bold">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

</div>
@endsection
