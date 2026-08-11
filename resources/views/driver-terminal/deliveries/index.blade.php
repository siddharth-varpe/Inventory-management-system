@extends('driver-terminal.layouts.app')

@section('title', 'Deliveries — Driver Terminal')

@section('content')
<div class="vstack gap-4">

    <!-- 1. PAGE HEADER -->
    <div class="d-flex align-items-center justify-content-between pb-1">
        <div>
            <h5 class="fw-black text-dark mb-1 fs-4">Deliveries</h5>
            <p class="text-muted small mb-0" style="font-size: 0.82rem;">My Deliveries &bull; Manage all deliveries and track progress.</p>
        </div>
        <div class="d-flex align-items-center gap-2.5">
            <button type="button" class="btn bg-white border border-translucent rounded-circle shadow-xs p-0 d-flex align-items-center justify-content-center" 
                    style="width: 44px; height: 44px;"
                    onclick="document.getElementById('searchCollapse').classList.toggle('d-none')" title="Search">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                </svg>
            </button>
            <a href="{{ route('driver-terminal.notifications', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
               class="btn bg-white border border-translucent rounded-circle shadow-xs p-0 d-flex align-items-center justify-content-center position-relative" 
               style="width: 44px; height: 44px;" title="Notifications">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5.002 5.002 0 0 1 13 6c0 .88.32 4.2 1.22 6z"/>
                </svg>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light micro-text px-1.5">3</span>
            </a>
        </div>
    </div>

    <!-- 2. STATUS FILTER PILLS (5 PILLS - SPACIOUS & BREATHABLE) -->
    <div class="overflow-auto pb-1 my-1" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
        <div class="nav nav-pills flex-nowrap gap-2.5" style="width: max-content;">
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'all', 'search' => $search]) }}" 
               class="nav-link px-4 py-2.5 rounded-pill fw-bold text-nowrap shadow-xs {{ $activeTab === 'all' ? 'active text-white' : 'bg-white text-secondary border border-translucent' }}" 
               style="font-size: 0.85rem; {{ $activeTab === 'all' ? 'background-color: #9333ea !important; color: white !important; box-shadow: 0 4px 12px rgba(147, 51, 234, 0.25);' : '' }}">
                All Deliveries
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'in_progress', 'search' => $search]) }}" 
               class="nav-link px-4 py-2.5 rounded-pill fw-bold text-nowrap shadow-xs {{ $activeTab === 'in_progress' ? 'active bg-warning text-dark' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.85rem;">
                In Progress
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'completed', 'search' => $search]) }}" 
               class="nav-link px-4 py-2.5 rounded-pill fw-bold text-nowrap shadow-xs {{ $activeTab === 'completed' ? 'active bg-success text-white' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.85rem;">
                Completed
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'pending', 'search' => $search]) }}" 
               class="nav-link px-4 py-2.5 rounded-pill fw-bold text-nowrap shadow-xs {{ $activeTab === 'pending' ? 'active bg-primary text-white' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.85rem;">
                Pending
            </a>
            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => 'failed', 'search' => $search]) }}" 
               class="nav-link px-4 py-2.5 rounded-pill fw-bold text-nowrap shadow-xs {{ $activeTab === 'failed' ? 'active bg-danger text-white' : 'bg-white text-secondary border border-translucent' }}" style="font-size: 0.85rem;">
                Failed
            </a>
        </div>
    </div>

    <!-- 3. SUMMARY METRICS ROW (5 METRIC CARDS - BREATHABLE MIN-WIDTH) -->
    <div class="overflow-auto pb-2" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
        <div class="d-flex flex-nowrap gap-3 text-center" style="width: max-content;">
            <!-- Metric 1: Total Deliveries Today -->
            <div class="p-3.5 bg-white border border-translucent rounded-4 shadow-xs text-center d-flex flex-column align-items-center justify-content-center" style="min-width: 125px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 42px; height: 42px; background-color: #f3e8ff; color: #9333ea;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.847 3.5 8 6.099l6.153-2.599-5.967-2.387zM15 4.239l-6.5 2.746v6.918l6.5-2.746V4.239zM1 4.239v6.918l6.5 2.746V6.985L1 4.239z"/></svg>
                </div>
                <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $totalCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.72rem; line-height: 1.3;">Total Deliveries<br><span class="text-secondary">Today</span></div>
            </div>

            <!-- Metric 2: Completed -->
            <div class="p-3.5 bg-white border border-translucent rounded-4 shadow-xs text-center d-flex flex-column align-items-center justify-content-center" style="min-width: 125px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 42px; height: 42px; background-color: #ecfdf5; color: #10b981;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l4.992-5.99a.75.75 0 0 0-.018-1.042z"/></svg>
                </div>
                <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $completedCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.72rem; line-height: 1.3;">Completed<br><span class="text-success">({{ $completedPercent }}%)</span></div>
            </div>

            <!-- Metric 3: In Progress (FEATURING 3D TRUCK ASSET) -->
            <div class="p-3.5 bg-white border border-translucent rounded-4 shadow-xs text-center d-flex flex-column align-items-center justify-content-center" style="min-width: 125px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 42px; height: 42px; background-color: #fff7ed;">
                    <img src="{{ asset('images/truck-3d.png') }}" alt="3D Truck" style="width: 28px; height: 28px; object-fit: contain;">
                </div>
                <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $inProgressCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.72rem; line-height: 1.3;">In Progress<br><span class="text-warning-emphasis">({{ $inProgressPercent }}%)</span></div>
            </div>

            <!-- Metric 4: Pending -->
            <div class="p-3.5 bg-white border border-translucent rounded-4 shadow-xs text-center d-flex flex-column align-items-center justify-content-center" style="min-width: 125px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 42px; height: 42px; background-color: #eff6ff; color: #2563eb;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-clock-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/></svg>
                </div>
                <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $pendingCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.72rem; line-height: 1.3;">Pending<br><span class="text-primary">({{ $pendingPercent }}%)</span></div>
            </div>

            <!-- Metric 5: Failed -->
            <div class="p-3.5 bg-white border border-translucent rounded-4 shadow-xs text-center d-flex flex-column align-items-center justify-content-center" style="min-width: 125px;">
                <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" style="width: 42px; height: 42px; background-color: #fef2f2; color: #ef4444;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-x-circle-fill" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293 5.354 4.646z"/></svg>
                </div>
                <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $failedCount }}</div>
                <div class="text-muted micro-text fw-semibold" style="font-size: 0.72rem; line-height: 1.3;">Failed<br><span class="text-danger">({{ $failedPercent }}%)</span></div>
            </div>
        </div>
    </div>

    <!-- 4. SEARCH & SORT BAR (SPACIOUS MARGINS) -->
    <div class="d-flex align-items-center gap-2.5 my-1">
        <form method="GET" action="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" class="flex-grow-1">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div class="input-group">
                <span class="input-group-text bg-white border-translucent text-muted px-3" style="border-radius: 12px 0 0 12px;">🔍</span>
                <input type="text" name="search" class="form-control bg-white border-translucent py-2.5 px-3" 
                       placeholder="Search delivery, customer, order ID..." value="{{ $search }}" style="border-radius: 0 12px 12px 0; font-size: 0.85rem;">
                @if(!empty($search))
                    <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code), 'tab' => $activeTab]) }}" 
                       class="btn btn-outline-secondary px-3" style="border-radius: 12px; margin-left: 8px;">Clear</a>
                @endif
            </div>
        </form>

        <button type="button" class="btn bg-white border border-translucent text-dark fw-bold px-3.5 py-2.5 d-flex align-items-center gap-2 shadow-xs flex-shrink-0" style="font-size: 0.85rem; border-radius: 12px; min-height: 44px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-sliders" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.5 2a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM9.05 3a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0V3h9.05zM4.5 7a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zM2.05 8a2.5 2.5 0 0 1 4.9 0H16v1H6.95a2.5 2.5 0 0 1-4.9 0H0V8h2.05zm9.45 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3zm-2.45 1a2.5 2.5 0 0 1 4.9 0H16v1h-2.05a2.5 2.5 0 0 1-4.9 0H0v-1h9.05z"/></svg>
            <span>Sort &amp; Filter</span>
        </button>
    </div>

    <!-- 5. DELIVERY CARDS LIST (LUXURIOUS PADDING & BREATHABLE RHYTHM) -->
    <div class="vstack gap-4">
        @forelse($deliveries as $d)
            @php
                $statusLower = strtolower($d->status);
                $isCompleted = in_array($statusLower, ['delivered', 'completed']);
                $isInProgress = in_array($statusLower, ['dispatched', 'in_transit', 'arrived']);
                $isFailed = in_array($statusLower, ['failed', 'cancelled', 'rejected']);
                $isPending = !$isCompleted && !$isInProgress && !$isFailed;

                // Card left border strip & badge styling
                $stripColor = $isCompleted ? '#9333ea' : ($isInProgress ? '#f97316' : ($isFailed ? '#ef4444' : '#2563eb'));
                $iconBg = $isCompleted ? 'background-color: #f3e8ff; color: #9333ea;' : ($isInProgress ? 'background-color: #fff7ed; color: #ea580c;' : ($isFailed ? 'background-color: #fef2f2; color: #ef4444;' : 'background-color: #eff6ff; color: #2563eb;'));
                
                $badgeBg = $isCompleted ? 'bg-success-subtle text-success border-success-subtle' : ($isInProgress ? 'bg-warning-subtle text-warning-emphasis border-warning-subtle' : ($isFailed ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-primary-subtle text-primary border-primary-subtle'));
                $badgeText = $isCompleted ? '✓ Completed' : ($isInProgress ? '↻ In Progress (Ongoing)' : ($isFailed ? '✖ Failed' : '🕒 Pending (ASSIGNED)'));

                $orderRef = $d->order_reference ?? $d->request_number ?? ('DEL-' . $d->id);
                $customerName = $d->customer_name ?? $d->salesOrder?->customer?->company_name ?? 'Primary Customer';
                $address = $d->delivery_address ?? 'No address specified';
                $city = $d->delivery_city ?? $d->city ?? 'Pune';
                $pincode = $d->pincode ?? '411045';
                $window = $d->delivery_window ?? '09:30 AM - 10:30 AM';
                $expDate = $d->expected_delivery_date ? \Carbon\Carbon::parse($d->expected_delivery_date)->format('d M Y') : '17 May 2026';
                
                // Stops calculation
                $stopsCount = $d->package_count ? (int) ceil($d->package_count / 5) : 2;
                $completedStops = $isCompleted ? $stopsCount : ($isInProgress ? max(1, (int) floor($stopsCount / 2)) : 0);
                $stopsText = "{$completedStops}/{$stopsCount} Stops";
                $stopsClass = $isCompleted ? 'text-success' : ($isInProgress ? 'text-warning-emphasis' : ($isFailed ? 'text-danger' : 'text-primary'));
                $itemCount = ($d->package_count ?? 12) . ' Items';
            @endphp

            <div class="card bg-white border border-translucent rounded-4 shadow-sm overflow-hidden" 
                 style="border-left: 5px solid {{ $stripColor }} !important;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3.5">
                        <!-- Left Status Icon (FEATURING 3D TRUCK ON IN-PROGRESS) -->
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" 
                             style="width: 44px; height: 44px; {{ $iconBg }}">
                            @if($isInProgress)
                                <img src="{{ asset('images/truck-3d.png') }}" alt="3D Truck" style="width: 28px; height: 28px; object-fit: contain;">
                            @elseif($isCompleted)
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.847 3.5 8 6.099l6.153-2.599-5.967-2.387zM15 4.239l-6.5 2.746v6.918l6.5-2.746V4.239zM1 4.239v6.918l6.5 2.746V6.985L1 4.239z"/></svg>
                            @elseif($isFailed)
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-x-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/></svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16"><path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/><path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/></svg>
                            @endif
                        </div>

                        <!-- Card Content Column -->
                        <div class="flex-grow-1">
                            <!-- ID & Status Pill Row -->
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="fw-black font-monospace text-dark fs-6 lh-sm">
                                    {{ $orderRef }}
                                    @if($d->request_number && $d->request_number !== $orderRef)
                                        <span class="d-inline-block font-monospace text-muted micro-text ms-1">({{ $d->request_number }})</span>
                                    @endif
                                </div>
                                <span class="badge rounded-pill px-3 py-1.5 font-monospace border {{ $badgeBg }}" style="font-size: 0.75rem;">
                                    {{ $badgeText }}
                                </span>
                            </div>

                            <!-- Customer Name -->
                            <h6 class="fw-black text-primary mb-2 fs-6" style="color: #7c3aed !important; font-size: 0.98rem;">
                                {{ $customerName }}
                            </h6>

                            <!-- Destination Address -->
                            <div class="text-secondary small mb-3 d-flex align-items-start gap-2 lh-base" style="font-size: 0.82rem;">
                                <span class="fs-6">📍</span>
                                <span>{{ $address }}, {{ $city }} - {{ $pincode }}</span>
                            </div>

                            <!-- Schedule & Stop Count Row -->
                            <div class="d-flex align-items-center justify-content-between pt-3 border-top border-translucent">
                                <div class="text-muted small font-monospace" style="font-size: 0.78rem;">
                                    📅 {{ $expDate }} &nbsp;|&nbsp; 🕒 {{ $window }}
                                </div>

                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="text-end">
                                        <span class="fw-black micro-text d-block {{ $stopsClass }}" style="font-size: 0.8rem;">{{ $stopsText }}</span>
                                        <span class="text-muted micro-text d-block" style="font-size: 0.7rem;">{{ $itemCount }}</span>
                                    </div>
                                    <a href="{{ route('driver-terminal.deliveries.show', ['driver_code' => strtolower($currentDriver->driver_code), 'id' => $d->id]) }}" 
                                       class="btn btn-sm p-2 text-secondary hover-text-primary rounded-circle hover-bg-light" title="View Delivery Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- PROFESSIONAL EMPTY STATE (FEATURING 3D TRUCK) -->
            <div class="card bg-white border border-translucent rounded-4 p-5 text-center shadow-sm">
                <div class="mb-3">
                    <img src="{{ asset('images/truck-3d.png') }}" alt="3D Delivery Truck" style="width: 110px; height: 110px; object-fit: contain;">
                </div>
                <h6 class="fw-bold text-dark mb-1">NO ASSIGNED DELIVERIES</h6>
                <p class="text-muted small mb-0 px-3" style="font-size: 0.85rem;">
                    No deliveries found. You currently have no deliveries assigned to you.
                </p>
                @if(!empty($search) || $activeTab !== 'all')
                    <div class="mt-4">
                        <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
                           class="btn btn-sm btn-outline-secondary px-4 py-2 rounded-pill fw-bold">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- 6. TODAY'S DELIVERY SUMMARY CARD -->
    <div class="card bg-light-subtle border border-info-subtle rounded-4 p-4 shadow-xs" style="background-color: #f0f9ff !important; border-color: #bae6fd !important;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-bar-chart-fill" viewBox="0 0 16 16"><path d="M1 11a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3zm5-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm5-5a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V2z"/></svg>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 fs-6" style="font-size: 0.9rem;">Today's Delivery Summary</h6>
                    <div class="text-secondary micro-text font-monospace" style="font-size: 0.75rem; line-height: 1.5;">
                        Total Distance: 128 km &bull; Total Time: 7h 15m &bull; Fuel Cost: &#8377;1,850
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-bold flex-shrink-0" style="font-size: 0.78rem;">
                View Summary
            </button>
        </div>
    </div>

</div>
@endsection
