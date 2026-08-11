@extends('driver-terminal.layouts.app')

@section('title', 'Driver Workspace — Home')

@section('content')
<div class="vstack gap-4">

    <!-- 1. DRIVER STATUS CARD -->
    <div class="card border-0 rounded-4 shadow-sm" style="background-color: #ecfdf5; border: 1px solid #a7f3d0 !important;">
        <div class="card-body p-3.5 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" 
                     style="width: 48px; height: 48px; background-color: #d1fae5; color: #059669;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-steering-wheel" viewBox="0 0 16 16">
                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                        <path d="M8 13A5 5 0 1 1 8 3a5 5 0 0 1 0 10zm0 1A6 6 0 1 0 8 2a6 6 0 0 0 0 12z"/>
                        <path d="M8 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z"/>
                        <path d="M8 3.25a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3a.75.75 0 0 1 .75-.75zM3.25 8a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 0 1.5h-3A.75.75 0 0 1 3.25 8zm8.75-.75a.75.75 0 0 1 0 1.5h-3a.75.75 0 0 1 0-1.5h3z"/>
                    </svg>
                </div>
                <div>
                    <h6 class="fw-extrabold mb-0 fs-6" style="color: #047857;">
                        {{ $currentDriver->status_label ?? 'On Duty' }}
                    </h6>
                    <span class="small" style="color: #065f46; font-size: 0.78rem;">
                        Shift Status: Active &bull; {{ $assignedVehicle ? $assignedVehicle->vehicle_number : 'Fleet Operational' }}
                    </span>
                </div>
            </div>

            <button type="button" class="btn btn-sm bg-white rounded-pill px-3 py-1.5 border border-success-subtle shadow-xs d-flex align-items-center gap-1.5"
                    style="color: #047857; font-weight: 700; font-size: 0.78rem;"
                    onclick="alert('Status Indicator: You are active on duty under Driver Master profile {{ $currentDriver->driver_code ?? '' }}.')">
                <span>Change Status</span>
                <span class="rounded-circle" style="width: 8px; height: 8px; background-color: #10b981;"></span>
            </button>
        </div>
    </div>

    <!-- 2. TODAY'S OVERVIEW CARD -->
    <div class="card bg-white border border-translucent rounded-4 shadow-sm">
        <div class="card-body p-3.5">
            <h6 class="fw-extrabold text-dark mb-3 fs-6">Today's Overview</h6>

            <div class="row row-cols-2 row-cols-sm-4 g-3 text-center">
                <!-- Metric 1: Deliveries Today -->
                <div class="col border-end-sm">
                    <div class="d-flex flex-column align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" 
                             style="width: 44px; height: 44px; background-color: #eff6ff; color: #2563eb;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-briefcase" viewBox="0 0 16 16">
                                <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.5 6a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                        </div>
                        <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $deliveriesTodayCount }}</div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.72rem; line-height: 1.1;">Deliveries<br>Today</div>
                    </div>
                </div>

                <!-- Metric 2: Completed Today -->
                <div class="col border-end-sm">
                    <div class="d-flex flex-column align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" 
                             style="width: 44px; height: 44px; background-color: #ecfdf5; color: #10b981;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                                <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                            </svg>
                        </div>
                        <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $completedTodayCount }}</div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.72rem; line-height: 1.1;">Completed<br>Today</div>
                    </div>
                </div>

                <!-- Metric 3: Pending Deliveries -->
                <div class="col border-end-sm">
                    <div class="d-flex flex-column align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" 
                             style="width: 44px; height: 44px; background-color: #fffbeb; color: #f59e0b;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-clock" viewBox="0 0 16 16">
                                <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                            </svg>
                        </div>
                        <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $pendingDeliveriesCount }}</div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.72rem; line-height: 1.1;">Pending<br>Deliveries</div>
                    </div>
                </div>

                <!-- Metric 4: Total Distance Today -->
                <div class="col">
                    <div class="d-flex flex-column align-items-center">
                        <div class="rounded-circle d-flex align-items-center justify-content-center mb-2" 
                             style="width: 44px; height: 44px; background-color: #f3e8ff; color: #9333ea;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-signpost-split" viewBox="0 0 16 16">
                                <path d="M7 7V1.414a1 1 0 0 1 .293-.707l.707-.707a1 1 0 0 1 1.414 0l.707.707A1 1 0 0 1 10 1.414V7h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H10v3.586a1 1 0 0 1-.293.707l-.707.707a1 1 0 0 1-1.414 0l-.707-.707A1 1 0 0 1 7 13.586V11H5a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1h2z"/>
                            </svg>
                        </div>
                        <div class="fw-black text-dark fs-4 lh-1 mb-1">{{ $totalDistanceKm }} <span class="fs-6 text-muted fw-normal">km</span></div>
                        <div class="text-muted small fw-semibold" style="font-size: 0.72rem; line-height: 1.1;">Total Distance<br>Today</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. TODAY'S SCHEDULE CARD -->
    <div class="card bg-white border border-translucent rounded-4 shadow-sm">
        <div class="card-body p-3.5">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 class="fw-extrabold text-dark mb-0 fs-6">Today's Schedule</h6>
                <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" 
                   class="text-decoration-none fw-bold small" style="color: #2563eb; font-size: 0.8rem;">
                    View Full Plan
                </a>
            </div>

            @if(isset($todayRequests) && $todayRequests->count() > 0)
                <div class="vstack gap-2.5">
                    @foreach($todayRequests as $index => $req)
                        @php
                            $isCompleted = in_array(strtolower($req->status), ['delivered', 'completed']);
                            $isDispatched = in_array(strtolower($req->status), ['dispatched', 'in_transit', 'arrived']);
                            $badgeClass = $isCompleted ? 'bg-success-subtle text-success' : ($isDispatched ? 'bg-warning-subtle text-warning-emphasis' : 'bg-primary-subtle text-primary');
                            $badgeText = $isCompleted ? 'Completed' : ($isDispatched ? 'In Transit' : 'Assigned');
                            $circleBg = $isCompleted ? 'bg-success text-white' : ($isDispatched ? 'bg-warning text-white' : 'bg-secondary-subtle text-secondary');
                        @endphp
                        <a href="{{ route('driver-terminal.deliveries.show', ['driver_code' => strtolower($currentDriver->driver_code), 'id' => $req->id]) }}" 
                           class="p-2.5 bg-body-tertiary rounded-3 border border-translucent text-decoration-none text-body d-flex align-items-center justify-content-between gap-2.5 hover-shadow">
                            <div class="d-flex align-items-start gap-2.5">
                                <div class="rounded-circle fw-bold small d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5 {{ $circleBg }}" 
                                     style="width: 26px; height: 26px; font-size: 0.78rem;">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <div class="fw-bold text-dark fs-6 lh-sm">
                                        {{ $req->customer_name ?? $req->order_reference }}
                                    </div>
                                    <div class="text-muted small mt-0.5 text-truncate" style="font-size: 0.75rem; max-width: 210px;">
                                        📍 {{ $req->delivery_address ?? $req->city }}
                                    </div>
                                    <div class="text-muted font-monospace micro-text mt-0.5" style="font-size: 0.7rem;">
                                        🕒 {{ $req->expected_delivery_date ? $req->expected_delivery_date->format('d M Y') : 'Scheduled Today' }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-1.5">
                                <span class="badge rounded-pill px-2.5 py-1 font-monospace {{ $badgeClass }}" style="font-size: 0.7rem;">
                                    {{ $badgeText }}
                                </span>
                                <span class="text-muted fs-5">&rsaquo;</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <!-- PROFESSIONAL EMPTY STATE (NO FAKE MOCK DATA) -->
                <div class="text-center py-4 my-1">
                    <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <span class="fs-3">🚚</span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1">No deliveries scheduled for today</h6>
                    <p class="text-muted small mb-0 px-3" style="font-size: 0.8rem;">
                        You are all caught up! New assigned shipments from Transport Management will appear here automatically.
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- 4. TRIP PROGRESS CARD -->
    <div class="card bg-white border border-translucent rounded-4 shadow-sm">
        <div class="card-body p-3.5">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="fs-5">🚛</span>
                    <h6 class="fw-extrabold text-dark mb-0 fs-6">Trip Progress</h6>
                </div>
                <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" class="text-muted text-decoration-none fs-5">&rsaquo;</a>
            </div>

            @php
                $totalStops = $deliveriesTodayCount;
                $completedStops = $completedTodayCount;
                $progressPercent = $totalStops > 0 ? (int) round(($completedStops / $totalStops) * 100) : 0;
            @endphp

            @if($totalStops > 0)
                <div class="text-muted small mb-2" style="font-size: 0.8rem;">
                    {{ $completedStops }} of {{ $totalStops }} stops completed
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="progress flex-grow-1" style="height: 8px; background-color: #e2e8f0; border-radius: 999px;">
                        <div class="progress-bar bg-success rounded-pill" role="progressbar" 
                             style="width: {{ $progressPercent }}%;" 
                             aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="fw-bold text-success font-monospace small" style="font-size: 0.82rem;">{{ $progressPercent }}%</span>
                </div>
            @else
                <div class="text-muted small mb-2" style="font-size: 0.8rem;">
                    No active trip in progress
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="progress flex-grow-1" style="height: 8px; background-color: #e2e8f0; border-radius: 999px;">
                        <div class="progress-bar bg-secondary rounded-pill" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <span class="fw-bold text-muted font-monospace small" style="font-size: 0.82rem;">0%</span>
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
