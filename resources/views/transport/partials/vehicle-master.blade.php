<div>
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        
        <!-- PAGE HEADER CONTROL BAR (TOP SELECTOR & ADD ACTION) -->
        <div class="mb-4">
            <label class="form-label small fw-bold text-muted text-uppercase font-monospace mb-1.5" style="font-size: 0.725rem;">Select Vehicle</label>
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                
                <!-- SEARCHABLE VEHICLE SELECTOR DROPDOWN -->
                <div class="dropdown flex-grow-1" style="max-width: 520px;">
                    <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3.5 py-2.5 fw-semibold d-flex align-items-center justify-content-between w-100 shadow-xs bg-body-tertiary border-translucent" 
                            type="button" id="vehicleSelectorDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="d-flex align-items-center gap-2.5 text-truncate me-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-truck text-primary" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                            <span class="small font-monospace text-body">{{ $vehicleSearch ? 'Selected: '.$vehicleSearch : 'Search and select vehicle...' }}</span>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-start p-3 shadow-lg border-translucent rounded-3" aria-labelledby="vehicleSelectorDropdown" style="width: 100%; min-width: 340px; max-height: 380px; overflow-y: auto;">
                        <form method="GET" action="{{ route('transport.vehicles.index') }}" class="mb-2">
                            <input type="hidden" name="tab" value="vehicles">
                            <input type="hidden" name="vehicle_status" value="{{ $vehicleStatus }}">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary border-translucent">🔍</span>
                                <input type="text" name="vehicle_search" class="form-control bg-body-tertiary border-translucent font-monospace" 
                                       placeholder="Search reg no, ID, model, type..." 
                                       value="{{ $vehicleSearch }}" autofocus>
                                <button type="submit" class="btn btn-primary fw-bold">Search</button>
                            </div>
                        </form>

                        @if($vehicleSearch)
                            <div class="mb-2 pb-2 border-bottom d-flex align-items-center justify-content-between">
                                <span class="small text-muted font-monospace">Active Filter: <strong>{{ $vehicleSearch }}</strong></span>
                                <a href="{{ route('transport.vehicles.index', ['vehicle_status' => $vehicleStatus]) }}" class="small text-danger text-decoration-none fw-bold">Clear selection</a>
                            </div>
                        @endif

                        <div class="small text-muted fw-bold font-monospace text-uppercase mb-1.5" style="font-size: 0.7rem;">Quick Select Fleet Roster</div>
                        
                        <div class="vstack gap-1">
                            @forelse($allVehicles->take(15) as $av)
                                <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $av->vehicle_number, 'vehicle_status' => 'all']) }}" 
                                   class="dropdown-item p-2 rounded-2 d-flex align-items-center justify-content-between small text-decoration-none">
                                    <div>
                                        <div class="fw-bold text-body font-monospace">{{ $av->vehicle_number }}</div>
                                        <div class="small text-muted font-monospace" style="font-size: 0.725rem;">{{ $av->vehicle_code }} &bull; {{ $av->vehicle_type }}</div>
                                    </div>
                                    <span class="badge {{ $av->status_badge_class }} rounded-pill px-2 py-0.5" style="font-size: 0.65rem;">
                                        {{ $av->status_label }}
                                    </span>
                                </a>
                            @empty
                                <div class="text-muted small p-2 text-center">No vehicles found.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- RIGHT ACTION BUTTONS: + ADD VEHICLE & REFRESH -->
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm" 
                            data-bs-toggle="modal" data-bs-target="#modalRegisterVehicle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/></svg>
                        <span>+ Add Vehicle</span>
                    </button>

                    <a href="{{ route('transport.vehicles.index') }}" class="btn btn-outline-secondary rounded-3 px-3 py-2.5 shadow-xs" title="Refresh Fleet Roster" aria-label="Refresh Fleet">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-clockwise" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/></svg>
                    </a>
                </div>

            </div>
        </div>

        <!-- STATUS FILTER PILLS BAR MATCHING BLUEPRINT IMAGE STRICTLY -->
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-start mb-4 pb-1">
            <!-- ALL -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'all']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                <span>All</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['all'] ?? $allVehicles->count() }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- ACTIVE / AVAILABLE -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'available']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'available' ? 'btn-success' : 'btn-outline-success' }}">
                <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Active</span>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['available'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- ON TRIP -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'on_trip']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'on_trip' ? 'btn-primary' : 'btn-outline-primary' }}">
                <span class="rounded-circle bg-primary d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>On Trip</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['on_trip'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- MAINTENANCE -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'maintenance']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'maintenance' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                <span class="rounded-circle bg-warning d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Maintenance</span>
                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['maintenance'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- BREAKDOWN -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'breakdown']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'breakdown' ? 'btn-danger' : 'btn-outline-danger' }}">
                <span class="rounded-circle bg-danger d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Breakdown</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['breakdown'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- INACTIVE -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'inactive']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                <span class="rounded-circle bg-secondary d-inline-block" style="width: 8px; height: 8px;"></span>
                <span>Inactive</span>
                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['inactive'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            <!-- DOCUMENTS EXPIRING -->
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'expiring_documents']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 {{ $vehicleStatus === 'expiring_documents' ? 'btn-danger text-white border-danger' : 'btn-outline-danger' }}">
                <span>⚠️ Documents Expiring</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill ms-1" style="font-size: 0.725rem;">{{ $vehicleCounts['expiring_documents'] ?? 0 }}</span>
                <span class="ms-1" style="font-size: 0.7rem;">&rsaquo;</span>
            </a>

            @if($vehicleSearch || $vehicleStatus !== 'all')
                <a href="{{ route('transport.vehicles.index') }}" class="btn btn-sm btn-link text-muted ms-auto small text-decoration-none fw-semibold">
                    Reset Filters
                </a>
            @endif
        </div>

        <!-- VEHICLES CARD GRID CONTAINER -->
        @if($vehicles->isEmpty())
            <!-- EMPTY STATE MATCHING BLUEPRINT IMAGE -->
            <div class="p-5 text-center bg-body-tertiary rounded-4 border border-translucent my-3">
                <div class="avatar-circle bg-primary-subtle text-primary fs-2 fw-bold d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-truck text-primary" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                </div>
                <h5 class="fw-bold text-body mb-1">No vehicles found</h5>
                <p class="text-muted small mb-3">Try adjusting your search or filters.</p>
                <div class="d-flex align-items-center justify-content-center gap-2">
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegisterVehicle">
                        + Add Vehicle
                    </button>
                    <a href="{{ route('transport.vehicles.index') }}" class="btn btn-outline-secondary rounded-pill px-4 fw-bold">
                        Reset Filters
                    </a>
                </div>
            </div>
        @else
            <!-- 3-COLUMN RESPONSIVE CARD GRID MATCHING BLUEPRINT SPECIFICATION -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3.5 mb-4">
                @foreach($vehicles as $veh)
                    <div class="col">
                        <div class="card h-100 p-4 rounded-4 shadow-sm border-translucent bg-body vehicle-card d-flex flex-column justify-content-between">
                            
                            <!-- CARD TOP: TRUCK AVATAR + REGISTRATION NO + VEHICLE ID + STATUS BADGE -->
                            <div>
                                <div class="d-flex align-items-start justify-content-between gap-2.5 mb-2.5">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- CIRCLE TRUCK AVATAR (52px x 52px) -->
                                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-primary-subtle text-primary rounded-circle" style="width: 52px; height: 52px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVehicleProfile{{ $veh->id }}')).show()" 
                                               class="fw-bold text-body text-decoration-none font-monospace fs-5 d-block line-clamp-1">
                                                {{ $veh->vehicle_number }}
                                            </a>
                                            <span class="small text-muted font-monospace" style="font-size: 0.825rem;">
                                                {{ $veh->vehicle_code ?? ('VL-' . str_pad((string)$veh->id, 5, '0', STR_PAD_LEFT)) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- COMPACT STATUS BADGE -->
                                    <span class="badge {{ $veh->status_badge_class }} rounded-pill px-2.5 py-1 small flex-shrink-0 d-inline-flex align-items-center gap-1" style="font-size: 0.725rem; font-weight: 600;">
                                        @if($veh->isAvailable())
                                            <span class="rounded-circle bg-success d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @elseif($veh->status === 'on_trip')
                                            <span class="rounded-circle bg-primary d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @elseif($veh->isUnderMaintenance())
                                            <span class="rounded-circle bg-warning d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @elseif($veh->isBreakdown())
                                            <span class="rounded-circle bg-danger d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @else
                                            <span class="rounded-circle bg-secondary d-inline-block" style="width: 6px; height: 6px;"></span>
                                        @endif
                                        {{ $veh->status_label }}
                                    </span>
                                </div>

                                <!-- CARD BODY: FIELD METADATA LIST WITH CLEAN ICONS MATCHING BLUEPRINT -->
                                <div class="vstack gap-2 pt-1 pb-2">
                                    <!-- 1. VEHICLE TYPE -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-truck text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                                        <span class="text-body fw-semibold small">Truck &bull; {{ $veh->vehicle_type }}</span>
                                    </div>

                                    <!-- 2. CAPACITY -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-box-seam text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-11z"/></svg>
                                        <span class="text-body small">Capacity: <strong>{{ number_format((float)$veh->load_capacity_kg / 1000, 0) }} Ton</strong> <span class="text-muted font-monospace">({{ number_format((float)$veh->load_capacity_kg, 0) }} kg)</span></span>
                                    </div>

                                    <!-- 3. MODEL & MAKE -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-tag text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z"/><path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1H2zm4 3.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>
                                        <span class="text-body small">{{ $veh->manufacturer }} {{ $veh->model }}</span>
                                    </div>

                                    <!-- 4. INSURANCE EXPIRY -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-calendar-event text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/><path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H1a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/></svg>
                                        <span class="text-body small">Insurance: <strong>{{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('d M Y') : '12 Aug 2026' }}</strong></span>
                                    </div>

                                    <!-- 5. FITNESS EXPIRY -->
                                    <div class="d-flex align-items-center gap-2.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-lock text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2zM5 8h6a1 1 0 0 1 1 1v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/></svg>
                                        <span class="text-body small">Fitness: <strong>{{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('d M Y') : '25 Aug 2026' }}</strong></span>
                                    </div>
                                </div>
                            </div>

                            <!-- CARD FOOTER: DIVIDER + 5 EVENLY SPACED CIRCULAR ICON-ONLY ACTION BUTTONS -->
                            <div>
                                <div class="border-bottom border-translucent my-3"></div>
                                
                                <div class="d-flex align-items-center justify-content-between gap-1">
                                    <!-- 1. VIEW PROFILE / DETAILS -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalVehicleProfile{{ $veh->id }}" 
                                            title="View Details" aria-label="View Vehicle Profile">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                    </button>

                                    <!-- 2. EDIT VEHICLE -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditVehicle{{ $veh->id }}" 
                                            title="Edit Vehicle" aria-label="Edit Vehicle Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                                    </button>

                                    <!-- 3. LEGAL COMPLIANCE AUDIT -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalVehicleProfile{{ $veh->id }}" 
                                            title="Legal Compliance Audit" aria-label="Compliance Audit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43C2.843 1.215 3.961.86 5.072.56z"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/></svg>
                                    </button>

                                    <!-- 4. MAINTENANCE LOG ACTION -->
                                    @if($veh->isUnderMaintenance())
                                        <form method="POST" action="{{ route('transport.vehicles.return-maintenance', $veh->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                    style="width: 40px; height: 40px;" 
                                                    onclick="return confirm('Return vehicle {{ $veh->vehicle_number }} from maintenance?')"
                                                    title="Complete Maintenance" aria-label="Complete Maintenance">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wrench" viewBox="0 0 16 16"><path d="M.102 2.223A3.004 3.004 0 0 0 3.78 5.897l6.341 6.252A3.003 3.003 0 0 0 13 16a3 3 0 1 0-.851-5.878L5.897 3.78A3.004 3.004 0 0 0 2.223.102L3.84 1.72 1.72 3.84.102 2.223zm4.922 4.922-6.252-6.34A2.001 2.001 0 0 1 2.5 0c1.104 0 2 .896 2 2 0 .324-.077.63-.213.901l1.737 1.737-.999.999-.003.508z"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                style="width: 40px; height: 40px;" 
                                                data-bs-toggle="modal" data-bs-target="#modalVehicleMaintenance{{ $veh->id }}" 
                                                title="Maintenance Log" aria-label="Maintenance Log">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wrench" viewBox="0 0 16 16"><path d="M.102 2.223A3.004 3.004 0 0 0 3.78 5.897l6.341 6.252A3.003 3.003 0 0 0 13 16a3 3 0 1 0-.851-5.878L5.897 3.78A3.004 3.004 0 0 0 2.223.102L3.84 1.72 1.72 3.84.102 2.223zm4.922 4.922-6.252-6.34A2.001 2.001 0 0 1 2.5 0c1.104 0 2 .896 2 2 0 .324-.077.63-.213.901l1.737 1.737-.999.999-.003.508z"/></svg>
                                        </button>
                                    @endif

                                    <!-- 5. BREAKDOWN / DEACTIVATE ACTION -->
                                    @if($veh->isBreakdown())
                                        <form method="POST" action="{{ route('transport.vehicles.recover-breakdown', $veh->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                    style="width: 40px; height: 40px;" 
                                                    onclick="return confirm('Recover vehicle {{ $veh->vehicle_number }} from breakdown?')"
                                                    title="Recover Breakdown Vehicle" aria-label="Recover Breakdown">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16"><path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.356 3.356a1 1 0 0 0 1.414 0l1.586-1.586a1 1 0 0 0 0-1.414l-3.356-3.356a1 1 0 0 0-1.023-.242l-.914.305-.968-.968 2.617-2.654A3.003 3.003 0 0 0 16 3a3 3 0 1 0-5.878.851L7.468 6.504 4.793 3.829A1 1 0 0 1 4.5 3.121v-.07a1 1 0 0 0-.419-.815L1 0zm2.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm10.5-10a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center p-0 shadow-xs" 
                                                style="width: 40px; height: 40px;" 
                                                data-bs-toggle="modal" data-bs-target="#modalVehicleBreakdown{{ $veh->id }}" 
                                                title="Report Vehicle Breakdown" aria-label="Report Vehicle Breakdown">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3" viewBox="0 0 16 16"><path d="M6.5 1h3a.5.5 0 0 1 .5.5v1H6v-1a.5.5 0 0 1 .5-.5ZM11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3A1.5 1.5 0 0 0 5 1.5v1H2.506a.58.58 0 0 0-.01 0H1.5a.5.5 0 0 0 0 1h.538l.853 10.66A2 2 0 0 0 4.885 16h6.23a2 2 0 0 0 1.994-1.84l.853-10.66h.538a.5.5 0 0 0 0-1h-.995a.59.59 0 0 0-.01 0H11Zm1.958 1-.846 10.58a1 1 0 0 1-.997.92H4.885a1 1 0 0 1-.997-.92L3.042 3.5h9.916Zm-7.487 1a.5.5 0 0 1 .528.47l.5 8.5a.5.5 0 0 1-.998.06L5 5.03a.5.5 0 0 1 .47-.53Zm5.058 0a.5.5 0 0 1 .47.53l-.5 8.5a.5.5 0 1 1-.998-.06l.5-8.5a.5.5 0 0 1 .528-.47ZM8 4.5a.5.5 0 0 1 .5.5v8.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5Z"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- MODAL 1: VEHICLE PROFILE -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalVehicleProfile{{ $veh->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <div class="modal-header border-bottom bg-body-tertiary rounded-top-4 py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="avatar-circle bg-primary text-white fs-4 fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px;">
                                            🚛
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h5 class="modal-title fw-black text-body mb-0">{{ $veh->vehicle_number }}</h5>
                                                <span class="badge bg-primary text-white font-monospace fs-6 px-3 py-1 rounded-pill">
                                                    {{ $veh->vehicle_code ?? ('VL-' . str_pad((string)$veh->id, 5, '0', STR_PAD_LEFT)) }}
                                                </span>
                                            </div>
                                            <span class="small text-muted font-monospace">{{ $veh->manufacturer }} {{ $veh->model }} ({{ $veh->vehicle_type }})</span>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body p-4">
                                    <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-4 d-flex align-items-center justify-content-between">
                                        <div>
                                            <span class="text-muted small">Current Fleet Status:</span>
                                            <span class="badge {{ $veh->status_badge_class }} ms-2 px-3 py-1.5 rounded-pill fs-7 fw-bold">
                                                {{ $veh->status_label }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-muted small">Location:</span>
                                            <strong class="text-body ms-1 small">📍 {{ $veh->current_location ?? 'Central Freight Yard' }}</strong>
                                        </div>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-6 border-end-md">
                                            <h6 class="fw-bold text-body border-bottom pb-2 mb-3">⚙️ Technical Specifications & Capacity</h6>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Legal Registration Number</span>
                                                <strong class="text-body font-monospace fs-6">🚘 {{ $veh->vehicle_number }}</strong>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Vehicle Type & Classification</span>
                                                <span class="text-body fw-semibold">{{ $veh->vehicle_type }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Manufacturer & Model</span>
                                                <span class="text-body">{{ $veh->manufacturer }} {{ $veh->model }} @if($veh->manufacturing_year) (Year: {{ $veh->manufacturing_year }}) @endif</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Fuel Type & Color</span>
                                                <span class="text-body">Fuel: <strong>{{ $veh->fuel_type }}</strong> | Color: <strong>{{ $veh->color ?? 'Standard Fleet White' }}</strong></span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Operational Load Capacity (kg)</span>
                                                <strong class="text-primary font-monospace fs-6">🏋️ {{ number_format((float)$veh->load_capacity_kg, 0) }} kg</strong>
                                                <span class="text-muted small">({{ number_format((float)$veh->volume_capacity_m3, 1) }} m³)</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Current Odometer Reading</span>
                                                <strong class="text-body font-monospace">📟 {{ number_format((int)$veh->current_odometer_km) }} km</strong>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <h6 class="fw-bold text-body border-bottom pb-2 mb-3">📄 Legal Documents & Compliance Status</h6>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">RC (Registration Certificate) Number</span>
                                                <span class="text-body font-monospace fw-semibold">{{ $veh->rc_number ?? $veh->vehicle_number }}</span>
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Insurance Policy Number & Expiry</span>
                                                <div class="font-monospace text-body small">{{ $veh->insurance_policy_number ?? 'Not Recorded' }}</div>
                                                @if($veh->insurance_status === 'Expired')
                                                    <span class="badge bg-danger text-white font-monospace px-2.5 py-1">⛔ Expired on {{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('d M Y') : 'N/A' }}</span>
                                                @elseif($veh->insurance_status === 'Expiring Soon')
                                                    <span class="badge bg-warning text-dark font-monospace px-2.5 py-1">⚠️ Expiring on {{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('d M Y') : 'N/A' }}</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success font-monospace px-2.5 py-1">✅ Valid ({{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('d M Y') : 'N/A' }})</span>
                                                @endif
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">Fitness Certificate Expiry</span>
                                                @if($veh->fitness_status === 'Expired')
                                                    <span class="badge bg-danger text-white font-monospace px-2.5 py-1">⛔ Expired on {{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('d M Y') : 'N/A' }}</span>
                                                @elseif($veh->fitness_status === 'Expiring Soon')
                                                    <span class="badge bg-warning text-dark font-monospace px-2.5 py-1">⚠️ Expiring on {{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('d M Y') : 'N/A' }}</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success font-monospace px-2.5 py-1">✅ Valid ({{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('d M Y') : 'N/A' }})</span>
                                                @endif
                                            </div>
                                            <div class="mb-2">
                                                <span class="text-muted small d-block">PUC Expiry Date</span>
                                                @if($veh->puc_status === 'Expired')
                                                    <span class="badge bg-danger text-white font-monospace px-2.5 py-1">⛔ Expired on {{ $veh->puc_expiry_date ? $veh->puc_expiry_date->format('d M Y') : 'N/A' }}</span>
                                                @elseif($veh->puc_status === 'Expiring Soon')
                                                    <span class="badge bg-warning text-dark font-monospace px-2.5 py-1">⚠️ Expiring on {{ $veh->puc_expiry_date ? $veh->puc_expiry_date->format('d M Y') : 'N/A' }}</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success font-monospace px-2.5 py-1">✅ Valid ({{ $veh->puc_expiry_date ? $veh->puc_expiry_date->format('d M Y') : 'N/A' }})</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($veh->isUnderMaintenance())
                                            <div class="col-12">
                                                <div class="p-3 bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-3">
                                                    <h6 class="fw-bold mb-1">🔧 Active Vehicle Maintenance Log</h6>
                                                    <div class="small">
                                                        <div><strong>Reason:</strong> "{{ $veh->maintenance_reason }}"</div>
                                                        <div><strong>Started On:</strong> {{ $veh->maintenance_start_date ? $veh->maintenance_start_date->format('d M Y') : 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($veh->isBreakdown())
                                            <div class="col-12">
                                                <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded-3">
                                                    <h6 class="fw-bold mb-1">💥 Active Vehicle Breakdown Log</h6>
                                                    <div class="small">
                                                        <div><strong>Reported At:</strong> {{ $veh->breakdown_at ? $veh->breakdown_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                        <div><strong>Reason:</strong> "{{ $veh->breakdown_reason }}"</div>
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
                    <!-- MODAL 2: EDIT VEHICLE -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalEditVehicle{{ $veh->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <form method="POST" action="{{ route('transport.vehicles.update', $veh->id) }}">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="modal-header border-bottom bg-body-tertiary rounded-top-4 py-3">
                                        <h5 class="modal-title fw-bold text-body">Edit Vehicle: {{ $veh->vehicle_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4 row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-muted">Permanent Vehicle ID (Immutable)</label>
                                            <input type="text" class="form-control bg-body-tertiary font-monospace fw-bold" 
                                                   value="{{ $veh->vehicle_code ?? ('VL-' . str_pad((string)$veh->id, 5, '0', STR_PAD_LEFT)) }}" disabled>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Registration Number *</label>
                                            <input type="text" name="vehicle_number" class="form-control font-monospace" value="{{ $veh->vehicle_number }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Vehicle Type *</label>
                                            <select name="vehicle_type" class="form-select" required>
                                                <option value="Heavy Commercial Vehicle" {{ $veh->vehicle_type === 'Heavy Commercial Vehicle' ? 'selected' : '' }}>Heavy Commercial Vehicle</option>
                                                <option value="Medium Goods Vehicle" {{ $veh->vehicle_type === 'Medium Goods Vehicle' ? 'selected' : '' }}>Medium Goods Vehicle</option>
                                                <option value="Light Commercial Vehicle" {{ $veh->vehicle_type === 'Light Commercial Vehicle' ? 'selected' : '' }}>Light Commercial Vehicle</option>
                                                <option value="Container Truck" {{ $veh->vehicle_type === 'Container Truck' ? 'selected' : '' }}>Container Truck</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Load Capacity (kg) *</label>
                                            <input type="number" name="load_capacity_kg" class="form-control font-monospace" value="{{ $veh->load_capacity_kg }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Volume Capacity (m³) *</label>
                                            <input type="number" step="0.1" name="volume_capacity_m3" class="form-control font-monospace" value="{{ $veh->volume_capacity_m3 }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Manufacturer / Make *</label>
                                            <input type="text" name="manufacturer" class="form-control" value="{{ $veh->manufacturer }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Model *</label>
                                            <input type="text" name="model" class="form-control" value="{{ $veh->model }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Insurance Expiry Date *</label>
                                            <input type="date" name="insurance_expiry_date" class="form-control font-monospace" 
                                                   value="{{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('Y-m-d') : '' }}" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-body">Fitness Expiry Date *</label>
                                            <input type="date" name="fitness_expiry_date" class="form-control font-monospace" 
                                                   value="{{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('Y-m-d') : '' }}" required>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label small fw-bold text-body">Manager Operational Notes</label>
                                            <textarea name="notes" class="form-control" rows="2">{{ $veh->notes }}</textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                        <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Save Vehicle Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- MODAL 3: MAINTENANCE LOG -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalVehicleMaintenance{{ $veh->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <form method="POST" action="{{ route('transport.vehicles.maintenance', $veh->id) }}">
                                    @csrf
                                    <div class="modal-header border-bottom bg-warning text-dark rounded-top-4 py-3">
                                        <h5 class="modal-title fw-bold">🔧 Mark Maintenance: {{ $veh->vehicle_number }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-body">Maintenance Reason *</label>
                                            <textarea name="maintenance_reason" class="form-control" rows="3" required placeholder="e.g. Engine overhaul, brake replacement..."></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                        <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-warning rounded-3 px-4 fw-bold text-dark">Confirm Maintenance Log</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- ========================================================================= -->
                    <!-- MODAL 4: BREAKDOWN LOG -->
                    <!-- ========================================================================= -->
                    <div class="modal fade" id="modalVehicleBreakdown{{ $veh->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                <form method="POST" action="{{ route('transport.vehicles.breakdown', $veh->id) }}">
                                    @csrf
                                    <div class="modal-header border-bottom bg-danger text-white rounded-top-4 py-3">
                                        <h5 class="modal-title fw-bold">💥 Report Breakdown: {{ $veh->vehicle_number }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>

                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-body">Breakdown Reason *</label>
                                            <textarea name="breakdown_reason" class="form-control" rows="3" required placeholder="e.g. Transmission failure on highway..."></textarea>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                        <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">Report Breakdown Incident</button>
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
                    Showing {{ $vehicles->firstItem() ?? 0 }} to {{ $vehicles->lastItem() ?? 0 }} of {{ $vehicles->total() }} vehicles
                </div>
                <div>
                    {{ $vehicles->appends(request()->query())->links() }}
                </div>
            </div>
        @endif

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: REGISTER NEW VEHICLE (PHASE 2) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalRegisterVehicle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
            <form method="POST" action="{{ route('transport.vehicles.store') }}">
                @csrf
                <div class="modal-header border-bottom bg-body-tertiary rounded-top-4 py-3">
                    <h5 class="modal-title fw-bold text-body">➕ Register New Vehicle Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 row g-3">
                    <div class="col-12">
                        <div class="p-3 bg-primary-subtle text-primary border border-primary-subtle rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace fw-bold">🆔 Vehicle ID Assignment:</span>
                            <span class="badge bg-primary text-white font-monospace fs-6 px-3 py-1 rounded-pill">
                                System Auto-Generated (Format: VL-00001)
                            </span>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Registration Number *</label>
                        <input type="text" name="vehicle_number" class="form-control font-monospace" placeholder="e.g. MH-12-AU-2233" value="{{ old('vehicle_number') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Vehicle Type *</label>
                        <select name="vehicle_type" class="form-select" required>
                            <option value="Heavy Commercial Vehicle" selected>Heavy Commercial Vehicle</option>
                            <option value="Medium Goods Vehicle">Medium Goods Vehicle</option>
                            <option value="Light Commercial Vehicle">Light Commercial Vehicle</option>
                            <option value="Container Truck">Container Truck</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Manufacturer / Make *</label>
                        <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Tata Motors" value="{{ old('manufacturer') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Model *</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. Prima 3530.K" value="{{ old('model') }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Fuel Type *</label>
                        <select name="fuel_type" class="form-select" required>
                            <option value="Diesel" selected>Diesel</option>
                            <option value="CNG">CNG</option>
                            <option value="Electric">Electric</option>
                            <option value="Petrol">Petrol</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Operational Load Capacity (kg) *</label>
                        <input type="number" name="load_capacity_kg" class="form-control font-monospace" placeholder="e.g. 10000" value="{{ old('load_capacity_kg', 10000) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Volume Capacity (m³) *</label>
                        <input type="number" step="0.1" name="volume_capacity_m3" class="form-control font-monospace" placeholder="e.g. 35.0" value="{{ old('volume_capacity_m3', 35.0) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Insurance Policy Number</label>
                        <input type="text" name="insurance_policy_number" class="form-control font-monospace" placeholder="e.g. POL-99887766" value="{{ old('insurance_policy_number') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Insurance Expiry Date *</label>
                        <input type="date" name="insurance_expiry_date" class="form-control font-monospace" value="{{ old('insurance_expiry_date', date('Y-m-d', strtotime('+1 year'))) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Fitness Certificate Expiry *</label>
                        <input type="date" name="fitness_expiry_date" class="form-control font-monospace" value="{{ old('fitness_expiry_date', date('Y-m-d', strtotime('+1 year'))) }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">PUC Expiry Date</label>
                        <input type="date" name="puc_expiry_date" class="form-control font-monospace" value="{{ old('puc_expiry_date', date('Y-m-d', strtotime('+6 months'))) }}">
                    </div>

                    <div class="col-12">
                        <label class="form-label small fw-bold text-body">Manager Operational Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                    <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold">Register Vehicle Master &rarr;</button>
                </div>
            </form>
        </div>
    </div>
</div>
