<div>
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        
        <!-- PAGE TITLE & HEADER CONTROL BAR -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-black text-body mb-1">Vehicles</h4>
                <p class="text-muted small mb-0">Manage registered vehicles, availability, capacity and compliance.</p>
            </div>
            
            <div class="d-flex align-items-center gap-2.5 flex-wrap">
                <!-- TOP CONTROL BAR: SEARCHABLE VEHICLE SELECTOR DROPDOWN -->
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle rounded-3 px-3.5 py-2 fw-semibold d-flex align-items-center justify-content-between gap-2 shadow-xs" 
                            type="button" id="vehicleSelectorDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 280px;">
                        <span class="d-flex align-items-center gap-2 text-truncate">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-truck text-muted" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                            <span class="small font-monospace">{{ $vehicleSearch ? 'Selected: '.$vehicleSearch : 'Search and select vehicle...' }}</span>
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-start p-3 shadow-lg border-translucent rounded-3" aria-labelledby="vehicleSelectorDropdown" style="width: 340px; max-height: 380px; overflow-y: auto;">
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

                <!-- PRIMARY + ADD VEHICLE BUTTON -->
                <button type="button" class="btn btn-primary rounded-3 px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" 
                        data-bs-toggle="modal" data-bs-target="#modalRegisterVehicle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2Z"/></svg>
                    <span>+ Add Vehicle</span>
                </button>
            </div>
        </div>

        <!-- VEHICLE STATUS FILTERS BAR WITH REAL BACKEND COUNTS -->
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-start mb-4 pb-2 border-bottom border-translucent">
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'all']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                All ({{ $vehicleCounts['all'] ?? $allVehicles->count() }})
            </a>
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'available']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'available' ? 'btn-success' : 'btn-outline-success' }}">
                Available ({{ $vehicleCounts['available'] ?? 0 }})
            </a>
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'on_trip']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'on_trip' ? 'btn-primary' : 'btn-outline-primary' }}">
                On Trip ({{ $vehicleCounts['on_trip'] ?? 0 }})
            </a>
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'maintenance']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'maintenance' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                Maintenance ({{ $vehicleCounts['maintenance'] ?? 0 }})
            </a>
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'breakdown']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'breakdown' ? 'btn-danger' : 'btn-outline-danger' }}">
                Breakdown ({{ $vehicleCounts['breakdown'] ?? 0 }})
            </a>
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'inactive']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                Inactive ({{ $vehicleCounts['inactive'] ?? 0 }})
            </a>
            <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'expiring_documents']) }}" 
               class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'expiring_documents' ? 'btn-danger text-white border-danger' : 'btn-outline-danger' }}">
                ⚠️ Documents Expiring ({{ $vehicleCounts['expiring_documents'] ?? 0 }})
            </a>

            @if($vehicleSearch || $vehicleStatus !== 'all')
                <a href="{{ route('transport.vehicles.index') }}" class="btn btn-sm btn-link text-muted ms-auto small text-decoration-none fw-semibold">
                    Reset Filters
                </a>
            @endif
        </div>

        <!-- VEHICLES CARD GRID CONTAINER -->
        @if($vehicles->isEmpty())
            <!-- EMPTY STATE -->
            <div class="p-5 text-center bg-body-tertiary rounded-4 border border-translucent my-3">
                <div class="avatar-circle bg-danger-subtle text-danger fs-2 fw-bold d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 64px; height: 64px;">
                    🚛
                </div>
                <h5 class="fw-bold text-body mb-1">No vehicles found</h5>
                <p class="text-muted small mb-3">No registered vehicles match your current search query or status filter parameters.</p>
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
            <!-- 3-COLUMN RESPONSIVE CARD GRID -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3.5 mb-4">
                @foreach($vehicles as $veh)
                    <div class="col">
                        <div class="card h-100 p-3.5 rounded-4 shadow-sm border-translucent bg-body vehicle-card d-flex flex-column justify-content-between">
                            
                            <!-- CARD TOP: TRUCK AVATAR, REGISTRATION NUMBER, VEHICLE ID & STATUS BADGE -->
                            <div>
                                <div class="d-flex align-items-start justify-content-between gap-2.5 mb-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- CIRCLE TRUCK AVATAR (52px x 52px) -->
                                        <div class="d-flex align-items-center justify-content-center flex-shrink-0 bg-danger-subtle text-danger rounded-circle" style="width: 52px; height: 52px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-truck" viewBox="0 0 16 16"><path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h9A1.5 1.5 0 0 1 12 3.5V5h1.02a1.5 1.5 0 0 1 1.17.563l1.481 1.85a1.5 1.5 0 0 1 .329.938V10.5a1.5 1.5 0 0 1-1.5 1.5H14a2 2 0 1 1-4 0H5a2 2 0 1 1-4 0H.5A.5.5 0 0 1 0 11.5v-8zM1 3.5v7h.5a2 2 0 0 1 3.163.787 2 2 0 0 1 3.674 0H10.5a2 2 0 0 1 3.163-.787H15V8.28l-1.48-1.85A.5.5 0 0 0 13.15 6H12v4.5a.5.5 0 0 1-1 0V3.5h-10z"/></svg>
                                        </div>
                                        <div>
                                            <a href="javascript:void(0)" onclick="bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVehicleProfile{{ $veh->id }}')).show()" 
                                               class="fw-bold text-body text-decoration-none font-monospace fs-5 d-block line-clamp-1">
                                                {{ $veh->vehicle_number }}
                                            </a>
                                            <span class="small text-muted font-monospace" style="font-size: 0.8rem;">
                                                {{ $veh->vehicle_code ?? ('VEH-' . str_pad((string)$veh->id, 6, '0', STR_PAD_LEFT)) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- COMPACT STATUS BADGE -->
                                    <span class="badge {{ $veh->status_badge_class }} rounded-pill px-2.5 py-1 small flex-shrink-0" style="font-size: 0.725rem; font-weight: 600;">
                                        {{ strtoupper($veh->status_label) }}
                                    </span>
                                </div>

                                <!-- CARD BODY: FIELD METADATA -->
                                <div class="vstack gap-2 pt-1 pb-2">
                                    <!-- VEHICLE TYPE -->
                                    <div class="d-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-box-seam text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2l-2.218-.887zm3.564 1.426L5.596 5 8 5.961 14.154 3.5l-2.404-.961zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM2.5 1a1 1 0 0 0-1 1v1a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-11z"/></svg>
                                        <span class="text-body fw-semibold small">{{ $veh->vehicle_type }}</span>
                                    </div>

                                    <!-- CAPACITY -->
                                    <div class="d-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-speedometer2 text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M8 4a.5.5 0 0 1 .5.5V6a.5.5 0 0 1-1 0V4.5A.5.5 0 0 1 8 4zM3.732 5.732a.5.5 0 0 1 .707 0l.915.914a.5.5 0 1 1-.708.707l-.914-.915a.5.5 0 0 1 0-.706zM9.192 4.5a.5.5 0 0 1 .5.5v1.5a.5.5 0 0 1-1 0V5a.5.5 0 0 1 .5-.5z"/><path d="M0 10a8 8 0 1 1 15.547 2.561l-.828.561A7 7 0 1 0 1.28 10.56l-.828-.56A8 8 0 0 1 0 10zm8-7a7 7 0 0 0-6.603 9.3l.828.56A6 6 0 1 1 13.775 11l.828-.56A7 7 0 0 0 8 3z"/></svg>
                                        <span class="text-primary font-monospace fw-bold small me-1">{{ number_format((float)$veh->load_capacity_kg, 0) }} kg</span>
                                        <span class="text-muted font-monospace small">({{ number_format((float)$veh->volume_capacity_m3, 1) }} m³)</span>
                                    </div>

                                    <!-- MAKE & MODEL -->
                                    <div class="d-flex align-items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-tag text-muted flex-shrink-0" viewBox="0 0 16 16"><path d="M6 4.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm-1 0a.5.5 0 1 0-1 0 .5.5 0 0 0 1 0z"/><path d="M2 1a1 1 0 0 0-1 1v4.586a1 1 0 0 0 .293.707l7 7a1 1 0 0 0 1.414 0l4.586-4.586a1 1 0 0 0 0-1.414l-7-7A1 1 0 0 0 6.586 1H2zm4 3.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/></svg>
                                        <span class="text-body small">{{ $veh->manufacturer }} {{ $veh->model }}</span>
                                    </div>

                                    <!-- INSURANCE & COMPLIANCE STATUS -->
                                    <div class="d-flex align-items-center justify-content-between gap-2 pt-1" style="font-size: 0.75rem;">
                                        <span class="text-muted">Insurance:</span>
                                        @if($veh->insurance_status === 'Expired')
                                            <span class="text-danger fw-bold font-monospace">⛔ Expired</span>
                                        @elseif($veh->insurance_status === 'Expiring Soon')
                                            <span class="text-warning-emphasis fw-bold font-monospace">⚠️ Expiring</span>
                                        @else
                                            <span class="text-body font-monospace">{{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('d M Y') : 'Valid' }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- CARD FOOTER: DIVIDER + CIRCULAR ICON-ONLY ACTION BUTTONS -->
                            <div>
                                <div class="border-bottom border-translucent my-2.5"></div>
                                
                                <div class="d-flex align-items-center justify-content-between gap-1.5">
                                    <!-- ICON 1: VIEW DETAILS / PROFILE -->
                                    <button type="button" class="btn btn-outline-info rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalVehicleProfile{{ $veh->id }}" 
                                            title="View Vehicle Profile" aria-label="View Vehicle Profile">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16"><path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/></svg>
                                    </button>

                                    <!-- ICON 2: EDIT VEHICLE DETAILS -->
                                    <button type="button" class="btn btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalEditVehicle{{ $veh->id }}" 
                                            title="Edit Vehicle Details" aria-label="Edit Vehicle Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg>
                                    </button>

                                    <!-- ICON 3: LEGAL COMPLIANCE AUDIT -->
                                    <button type="button" class="btn btn-outline-secondary rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                            style="width: 40px; height: 40px;" 
                                            data-bs-toggle="modal" data-bs-target="#modalVehicleProfile{{ $veh->id }}" 
                                            title="Legal Compliance Audit" aria-label="Legal Compliance Audit">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-shield-check" viewBox="0 0 16 16"><path d="M5.338 1.59a61.44 61.44 0 0 0-2.837.856.481.481 0 0 0-.328.39c-.554 4.157.726 7.19 2.253 9.188a10.725 10.725 0 0 0 2.287 2.233c.346.244.652.42.893.533.12.057.218.095.293.118a.55.55 0 0 0 .101.025.615.615 0 0 0 .1-.025c.076-.023.174-.061.294-.118.24-.113.547-.29.893-.533a10.726 10.726 0 0 0 2.287-2.233c1.527-1.997 2.807-5.031 2.253-9.188a.48.48 0 0 0-.328-.39c-.651-.213-1.75-.56-2.837-.855C9.552 1.29 8.531 1.067 8 1.067c-.53 0-1.552.223-2.662.524zM5.072.56C6.157.265 7.31 0 8 0s1.843.265 2.928.56c1.11.3 2.229.655 2.887.87a1.54 1.54 0 0 1 1.044 1.262c.596 4.477-.787 7.795-2.465 9.99a11.775 11.775 0 0 1-2.517 2.453 7.159 7.159 0 0 1-1.048.625c-.28.132-.581.24-.829.24s-.548-.108-.829-.24a7.158 7.158 0 0 1-1.048-.625 11.777 11.777 0 0 1-2.517-2.453C1.928 10.487.545 7.169 1.141 2.692A1.54 1.54 0 0 1 2.185 1.43C2.843 1.215 3.961.86 5.072.56z"/><path d="M10.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/></svg>
                                    </button>

                                    <!-- ICON 4: MAINTENANCE ACTION -->
                                    @if($veh->isUnderMaintenance())
                                        <form method="POST" action="{{ route('transport.vehicles.return-maintenance', $veh->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                                    style="width: 40px; height: 40px;" 
                                                    onclick="return confirm('Return vehicle {{ $veh->vehicle_number }} from maintenance?')"
                                                    title="Complete Maintenance" aria-label="Complete Maintenance">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle" viewBox="0 0 16 16"><path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/><path d="M10.97 4.97a.235.235 0 0 0-.02-.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05z"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-warning rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                                style="width: 40px; height: 40px;" 
                                                data-bs-toggle="modal" data-bs-target="#modalVehicleMaintenance{{ $veh->id }}" 
                                                title="Mark Under Maintenance" aria-label="Mark Under Maintenance">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wrench" viewBox="0 0 16 16"><path d="M.102 2.223A3.004 3.004 0 0 0 3.78 5.897l6.341 6.252A3.003 3.003 0 0 0 13 16a3 3 0 1 0-.851-5.878L5.897 3.78A3.004 3.004 0 0 0 2.223.102L3.84 1.72 1.72 3.84.102 2.223zm4.922 4.922-6.252-6.34A2.001 2.001 0 0 1 2.5 0c1.104 0 2 .896 2 2 0 .324-.077.63-.213.901l1.737 1.737-.999.999-.003.508z"/></svg>
                                        </button>
                                    @endif

                                    <!-- ICON 5: BREAKDOWN / DEACTIVATE ACTION -->
                                    @if($veh->isBreakdown())
                                        <form method="POST" action="{{ route('transport.vehicles.recover-breakdown', $veh->id) }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                                    style="width: 40px; height: 40px;" 
                                                    onclick="return confirm('Recover vehicle {{ $veh->vehicle_number }} from breakdown?')"
                                                    title="Recover Breakdown Vehicle" aria-label="Recover Breakdown">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-tools" viewBox="0 0 16 16"><path d="M1 0 0 1l2.2 3.081a1 1 0 0 0 .815.419h.07a1 1 0 0 1 .708.293l2.675 2.675-2.617 2.654A3.003 3.003 0 0 0 0 13a3 3 0 1 0 5.878-.851l2.654-2.617.968.968-.305.914a1 1 0 0 0 .242 1.023l3.356 3.356a1 1 0 0 0 1.414 0l1.586-1.586a1 1 0 0 0 0-1.414l-3.356-3.356a1 1 0 0 0-1.023-.242l-.914.305-.968-.968 2.617-2.654A3.003 3.003 0 0 0 16 3a3 3 0 1 0-5.878.851L7.468 6.504 4.793 3.829A1 1 0 0 1 4.5 3.121v-.07a1 1 0 0 0-.419-.815L1 0zm2.5 13a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm10.5-10a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline-danger rounded-circle d-inline-flex align-items-center justify-content-center p-0" 
                                                style="width: 40px; height: 40px;" 
                                                data-bs-toggle="modal" data-bs-target="#modalVehicleBreakdown{{ $veh->id }}" 
                                                title="Report Vehicle Breakdown" aria-label="Report Vehicle Breakdown">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16"><path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm-1.025-.279a.534.534 0 0 0-.742 0l-6.857 11.667c-.244.414-.07.94.385 1.137A.996.996 0 0 0 1.146 15h13.708c.455 0 .827-.37.827-.827 0-.18-.057-.354-.163-.5L8.66 1.737a.534.534 0 0 0-.747 0z"/><path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/></svg>
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
                                        <div class="avatar-circle bg-danger text-white fs-4 fw-bold d-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px;">
                                            🚛
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2">
                                                <h5 class="modal-title fw-black text-body mb-0">{{ $veh->vehicle_number }}</h5>
                                                <span class="badge bg-danger text-white font-monospace fs-6 px-3 py-1 rounded-pill">
                                                    {{ $veh->vehicle_code ?? ('VEH-' . str_pad((string)$veh->id, 6, '0', STR_PAD_LEFT)) }}
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
                                                        <div><strong>Expected Completion:</strong> {{ $veh->maintenance_expected_completion ? $veh->maintenance_expected_completion->format('d M Y') : 'Open Schedule' }}</div>
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
                                                   value="{{ $veh->vehicle_code ?? ('VEH-' . str_pad((string)$veh->id, 6, '0', STR_PAD_LEFT)) }}" disabled>
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

            <!-- PAGINATION -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-translucent">
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
                        <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded-3 d-flex align-items-center justify-content-between">
                            <span class="small font-monospace fw-bold">🆔 Vehicle ID Assignment:</span>
                            <span class="badge bg-danger text-white font-monospace fs-6 px-3 py-1 rounded-pill">
                                System Auto-Generated (Format: VEH-000001)
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
