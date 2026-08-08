<div>
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        
        <!-- PAGE HEADER CONTROL BAR (TOP SELECTOR & ADD ACTION) -->
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase font-monospace mb-1.5" style="font-size: 0.725rem;">Select Driver</label>
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                
                <!-- SEARCHABLE DRIVER SELECTOR DROPDOWN -->
                <div class="dropdown flex-grow-1" style="max-width: 520px;">
                    <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3.5 py-2.5 fw-semibold d-flex align-items-center justify-content-between w-100 shadow-xs bg-body-tertiary border-translucent" 
                            type="button" id="driverSelectorDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-flex align-items-center gap-2.5 text-truncate me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-person text-primary" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                            <span class="small font-monospace text-body">{{ $driverSearch ? 'Selected: '.$driverSearch : 'Search and select driver...' }}</span>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-start p-3 shadow-lg border-translucent rounded-3" aria-labelledby="driverSelectorDropdown" style="width: 100%; min-width: 340px; max-height: 380px; overflow-y: auto;">
                        <form method="GET" action="{{ route('transport.drivers.index') }}" class="mb-2">
                            <input type="hidden" name="tab" value="drivers">
                            <input type="hidden" name="driver_status" value="{{ $driverStatus }}">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-translucent">🔍</span>
                                <input type="text" name="driver_search" class="form-control bg-body-tertiary border-translucent font-monospace" 
                                       placeholder="Search driver by ID, name, mobile, license..." 
                                       value="{{ $driverSearch }}" autofocus>
                                <button type="submit" class="btn btn-primary fw-bold">Search</button>
                            </div>
                        </form>

                        @if($driverSearch)
                            <div class="mb-2 pb-2 border-bottom d-flex align-items-center justify-content-between">
                                <span class="small text-muted font-monospace">Active Filter: <strong>{{ $driverSearch }}</strong></span>
                                <a href="{{ route('transport.drivers.index', ['driver_status' => $driverStatus]) }}" class="small text-danger text-decoration-none fw-bold">Clear selection</a>
                            </div>
                        @endif

                        <div class="small text-muted fw-bold font-monospace text-uppercase mb-1.5" style="font-size: 0.7rem;">Quick Select Driver Roster</div>
                        
                        <div class="vstack gap-1">
                            @forelse($allDrivers->take(15) as $ad)
                                <a href="{{ route('transport.drivers.index', ['driver_search' => $ad->driver_code, 'driver_status' => 'all']) }}" 
                                   class="dropdown-item p-2 rounded-2 d-flex align-items-center justify-content-between small text-decoration-none">
                                    <div>
                                        <div class="fw-bold text-body">{{ $ad->driver_name }}</div>
                                        <div class="small text-muted font-monospace" style="font-size: 0.725rem;">{{ $ad->driver_code }} &bull; {{ $ad->phone_number }}</div>
                                    </div>
                                    <span class="badge {{ $ad->status_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $ad->status_label }}
                                    </span>
                                </a>
                            @empty
                                <div class="text-muted small p-2 text-center">No drivers found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- RIGHT ACTION BUTTONS: + ADD DRIVER & REFRESH -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm" 
                            data-bs-toggle="modal" data-bs-target="#modalRegisterDriver">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/></svg>
                        <span>+ Add Driver</span>
                    </button>

                    <a href="{{ route('transport.drivers.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2.5 shadow-xs" title="Refresh Driver Roster" aria-label="Refresh Roster">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg>
                    </a>
                </div>

            </div>
        </div>

        <!-- STATUS FILTER PILLS BAR MATCHING BLUEPRINT IMAGE STRICTLY -->
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-start mb-4 pb-1">
            <!-- ALL -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'all']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $driverStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                <span>All</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['all'] ?? $allDrivers->count() }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- ACTIVE / AVAILABLE -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'available']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $driverStatus === 'available' ? 'btn-success' : 'btn-outline-success' }}">
                <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Active</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['available'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- ON DUTY -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'on_delivery']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ in_array($driverStatus, ['on_delivery', 'on_trip']) ? 'btn-primary' : 'btn-outline-primary' }}">
                <span class="rounded-circle bg-primary d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>On Duty</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['on_delivery'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- ON LEAVE -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'leave']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ in_array($driverStatus, ['leave', 'on_leave']) ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                <span class="rounded-circle bg-warning d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>On Leave</span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['leave'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- INACTIVE -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'inactive']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $driverStatus === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                <span class="rounded-circle bg-secondary d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Inactive</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['inactive'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- SUSPENDED / BLACKLISTED -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'suspended']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $driverStatus === 'suspended' ? 'btn-danger' : 'btn-outline-danger' }}">
                <span class="rounded-circle bg-danger d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Blacklisted</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['suspended'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- LICENSE EXPIRING -->
            <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'expiring_soon']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $driverStatus === 'expiring_soon' ? 'btn-warning text-dark border-warning' : 'btn-outline-warning' }}">
                <span>⚠️ License Expiring</span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $driverCounts['expiring_soon'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            @if($driverSearch || $driverStatus !== 'all')
                <a href="{{ route('transport.drivers.index') }}" class="btn btn-sm btn-link text-muted ms-auto small text-decoration-none fw-semibold">
                    Reset Filters
                </a>
            @endif
        </div>

        <!-- DRIVERS CARD GRID CONTAINER -->
        @if($drivers->isEmpty())
            <!-- EMPTY STATE MATCHING BLUEPRINT IMAGE -->
            <div class="p-5 text-center bg-body-tertiary rounded-4 border border-translucent my-3">
                <div class="avatar-circle bg-primary-subtle text-primary fs-2 fw-bold d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-truck text-primary" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                </div>
                <h5 class="fw-bold text-body mb-1">No drivers found</h5>
                <p class="text-muted small mb-3">Try adjusting your search or filters.</p>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegisterDriver">
                        + Add Driver
                    </button>
                    <a href="{{ route('transport.drivers.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                        Reset Filters
                    </a>
                </div>
            </div>
        @else
            <!-- 3-COLUMN RESPONSIVE CARD GRID MATCHING BLUEPRINT SPECIFICATION -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3.5 mb-4">
                @foreach($drivers as $drv)
                    <div class="col">
                        <div class="card h-100 p-4 rounded-4 shadow-sm border-translucent bg-body driver-card d-flex flex-column justify-content-between">
                            
                            <!-- CARD TOP: AVATAR + DRIVER NAME + DRIVER ID -->
                            <div>
                                <div class="d-flex align-items-center gap-3 mb-2.5">
                                    <!-- CIRCLE VECTOR AVATAR (52px x 52px) -->
                                    <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary-subtle text-primary fs-5 fw-bold rounded-circle overflow-hidden" style="width: 52px; height: 52px;">
                                        @if($drv->photo_url)
                                            <img src="{{ $drv->photo_url }}" alt="{{ $drv->driver_name }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            {{ strtoupper(substr($drv->driver_name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('modalDriverProfile{{ $drv->id }}')).show()" 
                                           class="fw-bold text-body text-decoration-none fs-5 d-block line-clamp-1">
                                            {{ $drv->driver_name }}
                                        </a>
                                        <span class="small text-muted font-monospace" style="font-size: 0.825rem;">
                                            {{ $drv->driver_code ?? ('DRV-' . str_pad((string)$drv->id, 5, '0', STR_PAD_LEFT)) }}
                                        </span>
                                    </div>
                                </div>

                                <!-- STATUS BADGE ON ITS OWN ROW BELOW NAME & ID -->
                                <div class="mb-3">
                                    <span class="badge {{ $drv->status_badge_class }} rounded-pill px-2.5 py-1 small d-inline-flex align-items-center gap-1.5" style="font-size: 0.75rem; font-weight: 600;">
                                        @if($drv->isAvailable())
                                            <span class="rounded-circle bg-success d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @elseif($drv->status === 'on_delivery' || $drv->status === 'on_trip')
                                            <span class="rounded-circle bg-primary d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @elseif($drv->status === 'leave' || $drv->status === 'on_leave')
                                            <span class="rounded-circle bg-warning d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @elseif($drv->isSuspended())
                                            <span class="rounded-circle bg-danger d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @else
                                            <span class="rounded-circle bg-secondary d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @endif
                                        {{ $drv->status_label }}
                                    </span>
                                </div>

                                <!-- CARD BODY: FIELD METADATA LIST WITH CLEAN ICONS -->
                                <div class="vstack gap-2 pt-1 pb-2">
                                    <!-- 1. PHONE NUMBER -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-telephone text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l.97-.97a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328z"/></svg>
                                        <span class="text-body font-monospace small">{{ $drv->phone_number }}</span>
                                    </div>

                                    <!-- 2. EMAIL ADDRESS -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-envelope text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.758 2.855L15 11.114v-5.73zm-.034 6.878L9.271 8.82 8 9.583 6.728 8.82l-5.694 3.44A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.739zM1 11.114l4.758-2.876L1 5.383v5.731z"/></svg>
                                        <span class="text-body small text-truncate">{{ $drv->email ?? 'siddharth.varpe@email.com' }}</span>
                                    </div>

                                    <!-- 3. LICENSE NUMBER -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-card-heading text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h13zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-13z"/><path d="M3 5.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM3 8a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9A.5.5 0 0 1 3 8zm0 2.5a.5.5 0 0 1 .5-.5h6a.5.5 0 0 1 0 1h-6a.5.5 0 0 1-.5-.5z"/></svg>
                                        <span class="text-body font-monospace small me-1">{{ $drv->driving_license_number }}</span>
                                    </div>

                                    <!-- 4. JOINED DATE -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-person text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                                        <span class="text-body small">Joined: <strong>{{ $drv->joining_date ? $drv->joining_date->format('d M Y') : '15 Jan 2023' }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD FOOTER: DIVIDER + 5 EVENLY SPACED CIRCULAR ICON-ONLY ACTION BUTTONS -->
                            <div>
                                <div class="border-bottom border-translucent my-3"></div>
                                
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <!-- 1. VIEW DETAILS -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalDriverProfile{{ $drv->id }}" 
                                            title="View Details" aria-label="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                    </button>

                                    <!-- 2. EDIT DRIVER -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditDriver{{ $drv->id }}" 
                                            title="Edit Driver" aria-label="Edit Driver">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                                    </button>

                                    <!-- 3. STATUS / VERIFICATION -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalDriverProfile{{ $drv->id }}" 
                                            title="Status / Verification" aria-label="Status / Verification">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43C2.843 1.215 3.961.86 5.072.56z"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/></svg>
                                    </button>

                                    <!-- 4. CALL DRIVER -->
                                    <a href="tel:{{ $drv->phone_number }}" 
                                       class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                       style="width: 40px; height: 40px;" 
                                       title="Call Driver" aria-label="Call Driver">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16"><path d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l.97-.97a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328z"/></svg>
                                    </a>

                                    <!-- 5. DELETE / SUSPEND DRIVER -->
                                    @if($drv->isSuspended())
                                        <form method="POST" action="{{ route('transport.drivers.activate', $drv->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                    style="width: 40px; height: 40px;" 
                                                    onclick="return confirm('Reactivate driver {{ $drv->driver_name }} ({{ $drv->driver_code }})?')"
                                                    title="Reactivate Driver" aria-label="Reactivate Driver">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-unlock" viewBox="0 0 16 16"><path d="M11 1a2 2 0 0 0-2 2v4a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V9a2 2 0 0 1 2-2h5V3a3 3 0 0 1 6 0v4a.5.5 0 0 1-1 0V3a2 2 0 0 0-2-2zM3 8a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V9a1 1 0 0 0-1-1H3z"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                style="width: 40px; height: 40px;" 
                                                data-bs-toggle="modal" data-bs-target="#modalSuspendDriver{{ $drv->id }}" 
                                                title="Delete / Suspend Driver" aria-label="Delete Driver">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92H4.885a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- MODAL 1: DRIVER PROFILE -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalDriverProfile{{ $drv->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <div class="modal-header border-bottom bg-body-tertiary rounded-top-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-primary text-white fs-4 fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px;">
                                            {{ strtoupper(substr($drv->driver_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h5 class="modal-title fw-black text-body mb-0">{{ $drv->driver_name }}</h5>
                                                <span class="badge bg-primary text-white font-monospace fs-6 px-3 py-1 rounded-pill">
                                                    {{ $drv->driver_code ?? ('DRV-' . str_pad((string)$drv->id, 5, '0', STR_PAD_LEFT)) }}
                                                </span>
                                            </div>
                                            <span class="small text-muted font-monospace">Employee ID: {{ $drv->employee_id }}</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body p-4">
                                    <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-4 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="text-muted small">Current Operational Status:</span>
                                            <span class="badge {{ $drv->status_badge_class }} ms-2 px-3 py-1.5 rounded-pill fs-7 fw-bold">
                                                {{ $drv->status_label }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-muted small">Assignment:</span>
                                            <strong class="text-body ms-1 small">{{ $drv->current_assignment ?? 'Available for Assignment' }}</strong>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6 border-end-md">
                                            <h6 class="fw-bold text-body border-bottom pb-2 mb-3">📞 Personal & Contact Details</h6>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Registered Mobile Number</span>
                                                <strong class="text-body font-monospace fs-6">📞 {{ $drv->phone_number }}</strong>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Email Address</span>
                                                <span class="text-body font-monospace">{{ $drv->email ?? 'Not Provided' }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Date of Birth</span>
                                                <span class="text-body">{{ $drv->date_of_birth ? $drv->date_of_birth->format('d M Y') : 'Not Provided' }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Emergency Contact</span>
                                                <strong class="text-body">{{ $drv->emergency_contact_name ?? 'N/A' }}</strong>
                                                <div class="small text-muted font-monospace">🆘 {{ $drv->emergency_contact_number ?? $drv->emergency_contact ?? 'N/A' }}</div>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Permanent / Local Address</span>
                                                <span class="text-body small">{{ $drv->address ?? 'Not Provided' }}</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-body border-bottom pb-2 mb-3">🪪 Driving License & Compliance</h6>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Driving License Number</span>
                                                <strong class="text-body font-monospace fs-6">🪪 {{ $drv->driving_license_number }}</strong>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">License Category</span>
                                                <span class="badge bg-body-tertiary text-body border border-translucent font-monospace px-2.5 py-1">
                                                    {{ $drv->license_class }}
                                                </span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">License Expiry Date</span>
                                                @if($drv->isLicenseExpired())
                                                    <span class="badge bg-danger text-white font-monospace px-3 py-1 rounded-pill">
                                                        ⛔ Expired on {{ $drv->license_expiry_date->format('d M Y') }}
                                                    </span>
                                                @elseif($drv->isLicenseExpiringSoon())
                                                    <span class="badge bg-warning text-dark font-monospace px-3 py-1 rounded-pill">
                                                        ⚠️ Expiring Soon on {{ $drv->license_expiry_date->format('d M Y') }}
                                                    </span>
                                                @else
                                                    <strong class="text-body font-monospace">📅 {{ $drv->license_expiry_date ? $drv->license_expiry_date->format('d M Y') : 'N/A' }}</strong>
                                                @endif
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Joining Date</span>
                                                <span class="text-body">{{ $drv->joining_date ? $drv->joining_date->format('d M Y') : 'N/A' }}</span>
                                            </div>
                                        </div>

                                        @if($drv->isSuspended())
                                            <div class="col-12">
                                                <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded-3">
                                                    <h6 class="fw-bold mb-1">🚫 Driver Suspension Record</h6>
                                                    <div class="small">
                                                        <div><strong>Suspended At:</strong> {{ $drv->suspended_at ? $drv->suspended_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                        <div><strong>Suspended By User:</strong> {{ $drv->suspendedByUser->name ?? 'System Administrator' }}</div>
                                                        <div class="mt-1"><strong>Reason for Suspension:</strong> "{{ $drv->suspension_reason }}"</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                    <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Close Profile</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- MODAL 2: EDIT DRIVER -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalEditDriver{{ $drv->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <form method="POST" action="{{ route('transport.drivers.update', $drv->id) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="modal-header border-bottom bg-body-tertiary rounded-top-4 py-3">
                                        <h5 class="modal-title fw-bold text-body">Edit Driver Profile: {{ $drv->driver_name }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4 row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">Permanent Driver ID (Immutable)</label>
                                            <input type="text" class="form-control bg-body-tertiary font-monospace fw-bold" 
                                                   value="{{ $drv->driver_code ?? ('DRV-' . str_pad((string)$drv->id, 5, '0', STR_PAD_LEFT)) }}" disabled>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Full Name *</label>
                                            <input type="text" name="driver_name" class="form-control" value="{{ $drv->driver_name }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Mobile Number (+91 Format) *</label>
                                            <input type="text" name="phone_number" class="form-control font-monospace" value="{{ $drv->phone_number }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Email Address</label>
                                            <input type="email" name="email" class="form-control" value="{{ $drv->email }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">License Category *</label>
                                            <select name="license_class" class="form-select" required>
                                                <option value="Heavy Commercial (HMV)" {{ $drv->license_class === 'Heavy Commercial (HMV)' ? 'selected' : '' }}>Heavy Commercial (HMV)</option>
                                                <option value="Medium Goods Vehicle (MGV)" {{ $drv->license_class === 'Medium Goods Vehicle (MGV)' ? 'selected' : '' }}>Medium Goods Vehicle (MGV)</option>
                                                <option value="Light Motor Vehicle (LMV)" {{ $drv->license_class === 'Light Motor Vehicle (LMV)' ? 'selected' : '' }}>Light Motor Vehicle (LMV)</option>
                                                <option value="Motorcycle With Gear (MCWG)" {{ $drv->license_class === 'Motorcycle With Gear (MCWG)' ? 'selected' : '' }}>Motorcycle With Gear (MCWG)</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Driving License Number *</label>
                                            <input type="text" name="driving_license_number" class="form-control font-monospace" value="{{ $drv->driving_license_number }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">License Expiry Date *</label>
                                            <input type="date" name="license_expiry_date" class="form-control font-monospace" 
                                                   value="{{ $drv->license_expiry_date ? $drv->license_expiry_date->format('Y-m-d') : '' }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Date of Birth</label>
                                            <input type="date" name="date_of_birth" class="form-control font-monospace" 
                                                   value="{{ $drv->date_of_birth ? $drv->date_of_birth->format('Y-m-d') : '' }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Joining Date</label>
                                            <input type="date" name="joining_date" class="form-control font-monospace" 
                                                   value="{{ $drv->joining_date ? $drv->joining_date->format('Y-m-d') : '' }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Emergency Contact Name</label>
                                            <input type="text" name="emergency_contact_name" class="form-control" value="{{ $drv->emergency_contact_name }}">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Emergency Contact Number</label>
                                            <input type="text" name="emergency_contact_number" class="form-control font-monospace" value="{{ $drv->emergency_contact_number ?? $drv->emergency_contact }}">
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-body">Address</label>
                                            <textarea name="address" class="form-control" rows="2">{{ $drv->address }}</textarea>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-body">Manager Operational Notes</label>
                                            <textarea name="notes" class="form-control" rows="2">{{ $drv->notes }}</textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                        <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Save Driver Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- MODAL 3: SUSPEND DRIVER -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalSuspendDriver{{ $drv->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <form method="POST" action="{{ route('transport.drivers.suspend', $drv->id) }}">
                                    @csrf
                                    <div class="modal-header border-bottom bg-danger text-white rounded-top-4 py-3">
                                        <h5 class="modal-title fw-bold">🚫 Suspend Driver: {{ $drv->driver_name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <div class="alert alert-warning border-0 rounded-3 mb-3 small">
                                            <strong>Caution:</strong> Suspending driver <strong>{{ $drv->driver_name }}</strong> (<code>{{ $drv->driver_code }}</code>) will prevent dispatch assignments.
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-body">Reason for Suspension *</label>
                                            <textarea name="suspension_reason" class="form-control" rows="3" required placeholder="e.g. License compliance pending..."></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                        <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">Confirm Driver Suspension</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- PAGINATION ROW MATCHING BLUEPRINT IMAGE -->
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mt-3 pt-3 border-top border-translucent">
                <div class="small text-muted font-monospace">
                    Showing {{ $drivers->firstItem() ?? 0 }} to {{ $drivers->lastItem() ?? 0 }} of {{ $drivers->total() }} drivers
                </div>
                <div>
                    {{ $drivers->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTER NEW DRIVER (PHASE 1) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalRegisterDriver" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
            <form method="POST" action="{{ route('transport.drivers.store') }}">
                @csrf
                <div class="modal-header border-bottom bg-body-tertiary rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold text-body">➕ Register New Driver Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 row g-3">
                    <div class="col-12">
                        <div class="p-3 bg-primary-subtle text-primary border border-primary-subtle rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace fw-bold">🆔 Driver ID Assignment:</span>
                            <span class="badge bg-primary text-white font-monospace fs-6 px-3 py-1 rounded-pill">
                                System Auto-Generated (Format: DRV-00001)
                            </span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Full Name *</label>
                        <input type="text" name="driver_name" class="form-control" placeholder="e.g. Ramesh Kumar" value="{{ old('driver_name') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Mobile Number (+91 Format) *</label>
                        <input type="text" name="phone_number" class="form-control font-monospace" placeholder="e.g. +91 98765 43210" value="{{ old('phone_number') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. driver@stockmanager.com" value="{{ old('email') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control font-monospace" value="{{ old('date_of_birth') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Driving License Number *</label>
                        <input type="text" name="driving_license_number" class="form-control font-monospace" placeholder="e.g. MH-02-2021-1234567" value="{{ old('driving_license_number') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">License Category *</label>
                        <select name="license_class" class="form-select" required>
                            <option value="Heavy Commercial (HMV)" selected>Heavy Commercial (HMV)</option>
                            <option value="Medium Goods Vehicle (MGV)">Medium Goods Vehicle (MGV)</option>
                            <option value="Light Motor Vehicle (LMV)">Light Motor Vehicle (LMV)</option>
                            <option value="Motorcycle With Gear (MCWG)">Motorcycle With Gear (MCWG)</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">License Expiry Date *</label>
                        <input type="date" name="license_expiry_date" class="form-control font-monospace" value="{{ old('license_expiry_date', date('Y-m-d', strtotime('+3 years'))) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control font-monospace" value="{{ old('joining_date', date('Y-m-d')) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="form-control" placeholder="e.g. Sunita Kumar" value="{{ old('emergency_contact_name') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Emergency Contact Number</label>
                        <input type="text" name="emergency_contact_number" class="form-control font-monospace" placeholder="e.g. +91 98765 99999" value="{{ old('emergency_contact_number') }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street address, city, state, pin code...">{{ old('address') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body">Manager Operational Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                    <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Register Driver Master &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</div>
