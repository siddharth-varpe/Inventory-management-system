<div class="col-12">
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        
        <!-- Header & Register Action -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary text-white font-monospace px-3 py-1 rounded-pill">Fleet Register</span>
                    <span class="badge bg-body-tertiary text-body border border-translucent rounded-pill px-3 py-1 font-monospace">
                        Total Registered Fleet: {{ $vehicles->total() }} Vehicles
                    </span>
                </div>
                <h4 class="fw-black text-body mb-0">🚛 Vehicle Management</h4>
                <p class="text-muted small mb-0 mt-1">Permanent vehicle identities (`VEH-000001`), legal registration numbers, weight capacities (kg), document compliance & maintenance logs.</p>
            </div>
            <button class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegisterVehicle">
                <span>➕</span> Register New Vehicle
            </button>
        </div>

        <!-- Search Bar & Status Filters -->
        <div class="row g-3 mb-4 align-items-center">
            <!-- Search Input -->
            <div class="col-md-6 col-lg-5">
                <form method="GET" action="{{ route('transport.vehicles.index') }}">
                    <input type="hidden" name="tab" value="vehicles">
                    <input type="hidden" name="vehicle_status" value="{{ $vehicleStatus }}">
                    <div class="input-group search-box">
                        <span class="input-group-text bg-body-tertiary border-translucent">🔍</span>
                        <input type="text" name="vehicle_search" class="form-control bg-body-tertiary border-translucent" 
                               placeholder="Search Vehicle ID (VEH-000001), Reg No (MH12AB1234), Make..." 
                               value="{{ $vehicleSearch }}">
                        @if($vehicleSearch)
                            <a href="{{ route('transport.vehicles.index', ['vehicle_status' => $vehicleStatus]) }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                        <button type="submit" class="btn btn-primary fw-semibold px-3">Search</button>
                    </div>
                </form>
            </div>

            <!-- Status Filter Pills -->
            <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap gap-1.5 justify-content-md-end">
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'all']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                        All ({{ $allVehicles->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'available']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'available' ? 'btn-success' : 'btn-outline-success' }}">
                        Available ({{ $allVehicles->where('status', 'available')->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'reserved']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'reserved' ? 'btn-info text-white' : 'btn-outline-info' }}">
                        Reserved ({{ $allVehicles->where('status', 'reserved')->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'on_trip']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'on_trip' ? 'btn-primary' : 'btn-outline-primary' }}">
                        On Trip ({{ $allVehicles->where('status', 'on_trip')->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'maintenance']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'maintenance' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                        Maintenance ({{ $allVehicles->where('status', 'maintenance')->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'breakdown']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'breakdown' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Breakdown ({{ $allVehicles->where('status', 'breakdown')->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'inactive']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Inactive ({{ $allVehicles->where('status', 'inactive')->count() }})
                    </a>
                    <a href="{{ route('transport.vehicles.index', ['vehicle_search' => $vehicleSearch, 'vehicle_status' => 'expiring_documents']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $vehicleStatus === 'expiring_documents' ? 'btn-danger' : 'btn-outline-danger' }}">
                        ⚠️ Expiring Docs ({{ $allVehicles->filter(fn($v) => $v->hasExpiringOrExpiredDocuments())->count() }})
                    </a>
                </div>
            </div>
        </div>

        <!-- Vehicle Master Table -->
        @if($vehicles->isEmpty())
            <div class="p-5 text-center bg-body-tertiary rounded-4 border border-translucent my-3">
                <div class="fs-1 text-muted mb-2">🚛</div>
                <h5 class="fw-bold text-body mb-1">No vehicles found</h5>
                <p class="text-muted small mb-3">No registered vehicle records match your current search query or filter parameters.</p>
                <a href="{{ route('transport.vehicles.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Reset Filters</a>
            </div>
        @else
            <div class="table-responsive rounded-3 border border-translucent mb-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-3">Vehicle ID</th>
                            <th>Registration Number & Type</th>
                            <th>Make & Fuel</th>
                            <th>Load Capacity</th>
                            <th>Compliance Expiries</th>
                            <th>Status</th>
                            <th>Current Location</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $veh)
                            <tr>
                                <!-- Vehicle ID -->
                                <td class="ps-3">
                                    <span class="badge bg-danger text-white font-monospace fs-6 px-2.5 py-1.5 rounded-pill shadow-xs">
                                        {{ $veh->vehicle_code ?? ('VEH-' . str_pad((string)$veh->id, 6, '0', STR_PAD_LEFT)) }}
                                    </span>
                                </td>

                                <!-- Registration Number & Type -->
                                <td>
                                    <div class="fw-bold text-body font-monospace fs-6">{{ $veh->vehicle_number }}</div>
                                    <div class="small text-muted">{{ $veh->vehicle_type }}</div>
                                </td>

                                <!-- Make & Fuel -->
                                <td>
                                    <div class="fw-semibold text-body">{{ $veh->manufacturer }} {{ $veh->model }}</div>
                                    <div class="small text-muted">Fuel: <strong>{{ $veh->fuel_type }}</strong> @if($veh->manufacturing_year) ({{ $veh->manufacturing_year }}) @endif</div>
                                </td>

                                <!-- Capacity (kg) -->
                                <td>
                                    <div class="fw-bold text-primary font-monospace fs-6">{{ number_format((float)$veh->load_capacity_kg, 0) }} kg</div>
                                    <div class="small text-muted font-monospace">{{ number_format((float)$veh->volume_capacity_m3, 1) }} m³</div>
                                </td>

                                <!-- Compliance Expiries -->
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <!-- Insurance Status -->
                                        <div class="d-flex align-items-center justify-content-between gap-2" style="font-size: 0.75rem;">
                                            <span class="text-muted">Ins:</span>
                                            @if($veh->insurance_status === 'Expired')
                                                <span class="badge bg-danger text-white font-monospace">⛔ Expired</span>
                                            @elseif($veh->insurance_status === 'Expiring Soon')
                                                <span class="badge bg-warning text-dark font-monospace">⚠️ Expiring</span>
                                            @else
                                                <span class="text-body font-monospace">{{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('d M Y') : 'N/A' }}</span>
                                            @endif
                                        </div>

                                        <!-- Fitness Status -->
                                        <div class="d-flex align-items-center justify-content-between gap-2" style="font-size: 0.75rem;">
                                            <span class="text-muted">Fit:</span>
                                            @if($veh->fitness_status === 'Expired')
                                                <span class="badge bg-danger text-white font-monospace">⛔ Expired</span>
                                            @elseif($veh->fitness_status === 'Expiring Soon')
                                                <span class="badge bg-warning text-dark font-monospace">⚠️ Expiring</span>
                                            @else
                                                <span class="text-body font-monospace">{{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('d M Y') : 'N/A' }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td>
                                    <span class="badge {{ $veh->status_badge_class }} px-3 py-1.5 rounded-pill font-semibold">
                                        {{ $veh->status_label }}
                                    </span>
                                </td>

                                <!-- Current Location -->
                                <td>
                                    <div class="small fw-semibold text-body">📍 {{ $veh->current_location ?? 'Central Warehouse Yard' }}</div>
                                    <div class="small text-muted font-monospace">{{ number_format((int)$veh->current_odometer_km) }} km</div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-end pe-3">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <!-- Profile View Button -->
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-3 px-2.5" 
                                                data-bs-toggle="modal" data-bs-target="#modalVehicleProfile{{ $veh->id }}" 
                                                title="View Complete Vehicle Profile">
                                            📋 Profile
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-2.5" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditVehicle{{ $veh->id }}" 
                                                title="Edit Vehicle Details">
                                            ✏️ Edit
                                        </button>

                                        <!-- Maintenance Actions -->
                                        @if($veh->isUnderMaintenance())
                                            <form method="POST" action="{{ route('transport.vehicles.return-maintenance', $veh->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-2.5" 
                                                        onclick="return confirm('Return vehicle {{ $veh->vehicle_number }} from maintenance to Available?')"
                                                        title="Return to Available">
                                                    ✅ Complete Maintenance
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-3 px-2.5" 
                                                    data-bs-toggle="modal" data-bs-target="#modalVehicleMaintenance{{ $veh->id }}" 
                                                    title="Mark Under Maintenance">
                                                🔧 Maintenance
                                            </button>
                                        @endif

                                        <!-- Breakdown Actions -->
                                        @if($veh->isBreakdown())
                                            <form method="POST" action="{{ route('transport.vehicles.recover-breakdown', $veh->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-2.5" 
                                                        onclick="return confirm('Recover vehicle {{ $veh->vehicle_number }} from breakdown to Available?')"
                                                        title="Recover Vehicle">
                                                    🛠️ Recover
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2.5" 
                                                    data-bs-toggle="modal" data-bs-target="#modalVehicleBreakdown{{ $veh->id }}" 
                                                    title="Report Vehicle Breakdown">
                                                💥 Breakdown
                                            </button>
                                        @endif

                                        <!-- Activation / Deactivation Toggle -->
                                        @if($veh->isInactive())
                                            <form method="POST" action="{{ route('transport.vehicles.activate', $veh->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2.5" 
                                                        onclick="return confirm('Activate vehicle {{ $veh->vehicle_number }}?')"
                                                        title="Activate Vehicle">
                                                    🟢 Activate
                                                </button>
                                            </form>
                                        @elseif(!$veh->isUnderMaintenance() && !$veh->isBreakdown())
                                            <form method="POST" action="{{ route('transport.vehicles.deactivate', $veh->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 px-2.5" 
                                                        onclick="return confirm('Deactivate vehicle {{ $veh->vehicle_number }} ({{ $veh->vehicle_code }})? Vehicle record and history will be preserved.')"
                                                        title="Deactivate Vehicle">
                                                    💤 Deactivate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- ========================================================================= -->
                            <!-- MODAL 1: VEHICLE PROFILE (DEDICATED VIEW) -->
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
                                            <!-- Status Strip -->
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
                                                <!-- Left Column: Specification & Capacity -->
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

                                                <!-- Right Column: Compliance & Document Expiries -->
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-body border-bottom pb-2 mb-3">📄 Legal Documents & Compliance Status</h6>

                                                    <!-- RC Number -->
                                                    <div class="mb-2">
                                                        <span class="text-muted small d-block">RC (Registration Certificate) Number</span>
                                                        <span class="text-body font-monospace fw-semibold">{{ $veh->rc_number ?? $veh->vehicle_number }}</span>
                                                    </div>

                                                    <!-- Insurance -->
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

                                                    <!-- Fitness Certificate -->
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

                                                    <!-- PUC Expiry -->
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

                                                    <!-- System Timestamps -->
                                                    <div class="mb-2 border-top pt-2">
                                                        <span class="text-muted small d-block">System Audit Timestamps</span>
                                                        <div class="small text-muted font-monospace">Registered: {{ $veh->created_at ? $veh->created_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                        <div class="small text-muted font-monospace">Last Updated: {{ $veh->updated_at ? $veh->updated_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                    </div>
                                                </div>

                                                <!-- Maintenance Record Box -->
                                                @if($veh->isUnderMaintenance())
                                                    <div class="col-12">
                                                        <div class="p-3 bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-3">
                                                            <h6 class="fw-bold mb-1">🔧 Active Vehicle Maintenance Log</h6>
                                                            <div class="small">
                                                                <div><strong>Reason:</strong> "{{ $veh->maintenance_reason }}"</div>
                                                                <div><strong>Started On:</strong> {{ $veh->maintenance_start_date ? $veh->maintenance_start_date->format('d M Y') : 'N/A' }}</div>
                                                                <div><strong>Expected Completion:</strong> {{ $veh->maintenance_expected_completion ? $veh->maintenance_expected_completion->format('d M Y') : 'Open Schedule' }}</div>
                                                                @if($veh->maintenance_notes)
                                                                    <div class="mt-1"><strong>Notes:</strong> {{ $veh->maintenance_notes }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Breakdown Record Box -->
                                                @if($veh->isBreakdown())
                                                    <div class="col-12">
                                                        <div class="p-3 bg-danger-subtle text-danger border border-danger-subtle rounded-3">
                                                            <h6 class="fw-bold mb-1">💥 Active Vehicle Breakdown Incident Log</h6>
                                                            <div class="small">
                                                                <div><strong>Reported At:</strong> {{ $veh->breakdown_at ? $veh->breakdown_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                                <div><strong>Reason:</strong> "{{ $veh->breakdown_reason }}"</div>
                                                                @if($veh->breakdown_notes)
                                                                    <div class="mt-1"><strong>Notes:</strong> {{ $veh->breakdown_notes }}</div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- Operational Notes -->
                                                @if($veh->notes)
                                                    <div class="col-12">
                                                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                            <span class="text-muted small fw-bold d-block mb-1">📝 Manager Notes</span>
                                                            <p class="small text-body mb-0">{{ $veh->notes }}</p>
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
                                                <!-- Permanent Vehicle ID (Readonly) -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Permanent Vehicle ID (Immutable)</label>
                                                    <input type="text" class="form-control bg-body-tertiary font-monospace fw-bold" 
                                                           value="{{ $veh->vehicle_code ?? ('VEH-' . str_pad((string)$veh->id, 6, '0', STR_PAD_LEFT)) }}" disabled>
                                                </div>

                                                <!-- Legal Registration Number -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Registration Number *</label>
                                                    <input type="text" name="vehicle_number" class="form-control font-monospace" value="{{ $veh->vehicle_number }}" required>
                                                </div>

                                                <!-- Vehicle Type -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Vehicle Type *</label>
                                                    <select name="vehicle_type" class="form-select" required>
                                                        <option value="Mini Truck" {{ $veh->vehicle_type === 'Mini Truck' ? 'selected' : '' }}>Mini Truck</option>
                                                        <option value="Light Commercial Vehicle" {{ $veh->vehicle_type === 'Light Commercial Vehicle' ? 'selected' : '' }}>Light Commercial Vehicle</option>
                                                        <option value="Medium Commercial Vehicle" {{ $veh->vehicle_type === 'Medium Commercial Vehicle' ? 'selected' : '' }}>Medium Commercial Vehicle</option>
                                                        <option value="Heavy Commercial Vehicle" {{ $veh->vehicle_type === 'Heavy Commercial Vehicle' ? 'selected' : '' }}>Heavy Commercial Vehicle</option>
                                                        <option value="Van" {{ $veh->vehicle_type === 'Van' ? 'selected' : '' }}>Van</option>
                                                        <option value="Tempo" {{ $veh->vehicle_type === 'Tempo' ? 'selected' : '' }}>Tempo</option>
                                                        <option value="Pickup" {{ $veh->vehicle_type === 'Pickup' ? 'selected' : '' }}>Pickup</option>
                                                        <option value="Other" {{ $veh->vehicle_type === 'Other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                </div>

                                                <!-- Manufacturer -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Manufacturer *</label>
                                                    <input type="text" name="manufacturer" class="form-control" value="{{ $veh->manufacturer }}" required>
                                                </div>

                                                <!-- Model -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Model *</label>
                                                    <input type="text" name="model" class="form-control" value="{{ $veh->model }}" required>
                                                </div>

                                                <!-- Manufacturing Year -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Manufacturing Year</label>
                                                    <input type="number" name="manufacturing_year" class="form-control font-monospace" value="{{ $veh->manufacturing_year ?? 2022 }}">
                                                </div>

                                                <!-- Fuel Type -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Fuel Type *</label>
                                                    <select name="fuel_type" class="form-select" required>
                                                        <option value="Diesel" {{ $veh->fuel_type === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                                                        <option value="Petrol" {{ $veh->fuel_type === 'Petrol' ? 'selected' : '' }}>Petrol</option>
                                                        <option value="CNG" {{ $veh->fuel_type === 'CNG' ? 'selected' : '' }}>CNG</option>
                                                        <option value="Electric" {{ $veh->fuel_type === 'Electric' ? 'selected' : '' }}>Electric</option>
                                                        <option value="Hybrid" {{ $veh->fuel_type === 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                                                        <option value="Other" {{ $veh->fuel_type === 'Other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                </div>

                                                <!-- Load Capacity (kg) -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Load Capacity (kg) *</label>
                                                    <input type="number" step="0.01" name="load_capacity_kg" class="form-control font-monospace" value="{{ $veh->load_capacity_kg }}" required>
                                                </div>

                                                <!-- Odometer Reading -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Current Odometer Reading (km)</label>
                                                    <input type="number" name="current_odometer_km" class="form-control font-monospace" value="{{ $veh->current_odometer_km }}">
                                                </div>

                                                <!-- Insurance Policy Number -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Insurance Policy No</label>
                                                    <input type="text" name="insurance_policy_number" class="form-control font-monospace" value="{{ $veh->insurance_policy_number }}">
                                                </div>

                                                <!-- Insurance Expiry Date -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Insurance Expiry Date</label>
                                                    <input type="date" name="insurance_expiry_date" class="form-control font-monospace" 
                                                           value="{{ $veh->insurance_expiry_date ? $veh->insurance_expiry_date->format('Y-m-d') : '' }}">
                                                </div>

                                                <!-- Fitness Expiry Date -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Fitness Certificate Expiry</label>
                                                    <input type="date" name="fitness_expiry_date" class="form-control font-monospace" 
                                                           value="{{ $veh->fitness_expiry_date ? $veh->fitness_expiry_date->format('Y-m-d') : '' }}">
                                                </div>

                                                <!-- PUC Expiry Date -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">PUC Expiry Date</label>
                                                    <input type="date" name="puc_expiry_date" class="form-control font-monospace" 
                                                           value="{{ $veh->puc_expiry_date ? $veh->puc_expiry_date->format('Y-m-d') : '' }}">
                                                </div>

                                                <!-- Notes -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-body">Notes</label>
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
                            <!-- MODAL 3: MARK MAINTENANCE -->
                            <!-- ========================================================================= -->
                            <div class="modal fade" id="modalVehicleMaintenance{{ $veh->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                        <form method="POST" action="{{ route('transport.vehicles.maintenance', $veh->id) }}">
                                            @csrf
                                            <div class="modal-header border-bottom bg-warning text-dark rounded-top-4 py-3">
                                                <h5 class="modal-title fw-bold">🔧 Mark Vehicle Under Maintenance: {{ $veh->vehicle_number }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                <div class="alert alert-warning border-0 rounded-3 mb-3 small">
                                                    <strong>Notice:</strong> Marking vehicle <strong>{{ $veh->vehicle_number }}</strong> under maintenance will remove it from dispatch availability until maintenance is completed.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-body">Reason for Maintenance *</label>
                                                    <textarea name="maintenance_reason" class="form-control" rows="2" required 
                                                              placeholder="e.g. Engine oil overhaul, brake pad replacement, scheduled 20k km service..."></textarea>
                                                </div>

                                                <div class="row g-2 mb-3">
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold text-body">Start Date</label>
                                                        <input type="date" name="maintenance_start_date" class="form-control font-monospace" value="{{ date('Y-m-d') }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small fw-bold text-body">Expected Completion</label>
                                                        <input type="date" name="maintenance_expected_completion" class="form-control font-monospace" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small fw-bold text-body">Additional Maintenance Notes</label>
                                                    <textarea name="maintenance_notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                                <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning text-dark rounded-3 px-4 fw-bold">Confirm Maintenance</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- ========================================================================= -->
                            <!-- MODAL 4: REPORT BREAKDOWN -->
                            <!-- ========================================================================= -->
                            <div class="modal fade" id="modalVehicleBreakdown{{ $veh->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow-lg bg-body">
                                        <form method="POST" action="{{ route('transport.vehicles.breakdown', $veh->id) }}">
                                            @csrf
                                            <div class="modal-header border-bottom bg-danger text-white rounded-top-4 py-3">
                                                <h5 class="modal-title fw-bold">💥 Report Vehicle Breakdown: {{ $veh->vehicle_number }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body p-4">
                                                <div class="alert alert-danger border-0 rounded-3 mb-3 small">
                                                    <strong>Emergency Warning:</strong> Reporting breakdown for <strong>{{ $veh->vehicle_number }}</strong> (<code>{{ $veh->vehicle_code }}</code>) will immediately mark the vehicle as unusable.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-body">Reason for Breakdown *</label>
                                                    <textarea name="breakdown_reason" class="form-control" rows="3" required 
                                                              placeholder="e.g. Tire blowout on NH-48, transmission failure, battery dead..."></textarea>
                                                </div>

                                                <div class="mb-2">
                                                    <label class="form-label small fw-bold text-body">Breakdown Notes / Location</label>
                                                    <textarea name="breakdown_notes" class="form-control" rows="2" placeholder="Towed to service station..."></textarea>
                                                </div>
                                            </div>

                                            <div class="modal-footer border-top bg-body-tertiary rounded-bottom-4 py-2.5">
                                                <button type="button" class="btn btn-secondary rounded-3 px-3 fw-bold" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger rounded-3 px-4 fw-bold">Report Breakdown</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Links -->
            <div class="d-flex justify-content-between align-items-center mt-3">
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

                    <!-- Registration Number -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Registration Number *</label>
                        <input type="text" name="vehicle_number" class="form-control font-monospace" placeholder="e.g. MH-12-AB-1234" value="{{ old('vehicle_number') }}" required>
                    </div>

                    <!-- Vehicle Type -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Vehicle Type *</label>
                        <select name="vehicle_type" class="form-select" required>
                            <option value="Mini Truck">Mini Truck</option>
                            <option value="Light Commercial Vehicle" selected>Light Commercial Vehicle</option>
                            <option value="Medium Commercial Vehicle">Medium Commercial Vehicle</option>
                            <option value="Heavy Commercial Vehicle">Heavy Commercial Vehicle</option>
                            <option value="Van">Van</option>
                            <option value="Tempo">Tempo</option>
                            <option value="Pickup">Pickup</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Manufacturer -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Manufacturer *</label>
                        <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Tata Motors" value="{{ old('manufacturer', 'Tata Motors') }}" required>
                    </div>

                    <!-- Model -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Model *</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. Ultra T.14" value="{{ old('model') }}" required>
                    </div>

                    <!-- Manufacturing Year -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Manufacturing Year</label>
                        <input type="number" name="manufacturing_year" class="form-control font-monospace" placeholder="e.g. 2022" value="{{ old('manufacturing_year', 2022) }}">
                    </div>

                    <!-- Fuel Type -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Fuel Type *</label>
                        <select name="fuel_type" class="form-select" required>
                            <option value="Diesel" selected>Diesel</option>
                            <option value="Petrol">Petrol</option>
                            <option value="CNG">CNG</option>
                            <option value="Electric">Electric</option>
                            <option value="Hybrid">Hybrid</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <!-- Load Capacity (kg) -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Load Capacity (kg) *</label>
                        <input type="number" step="0.01" name="load_capacity_kg" class="form-control font-monospace" placeholder="e.g. 7500" value="{{ old('load_capacity_kg', 7500) }}" required>
                    </div>

                    <!-- Volume Capacity (m³) -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Volume Capacity (m³)</label>
                        <input type="number" step="0.01" name="volume_capacity_m3" class="form-control font-monospace" placeholder="e.g. 22.5" value="{{ old('volume_capacity_m3', 22.5) }}">
                    </div>

                    <!-- Current Odometer -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Current Odometer Reading (km)</label>
                        <input type="number" name="current_odometer_km" class="form-control font-monospace" placeholder="e.g. 15000" value="{{ old('current_odometer_km', 12000) }}">
                    </div>

                    <!-- Insurance Policy Number -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Insurance Policy No</label>
                        <input type="text" name="insurance_policy_number" class="form-control font-monospace" placeholder="POL-INS-998877" value="{{ old('insurance_policy_number') }}">
                    </div>

                    <!-- Insurance Expiry Date -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Insurance Expiry Date</label>
                        <input type="date" name="insurance_expiry_date" class="form-control font-monospace" value="{{ old('insurance_expiry_date', date('Y-m-d', strtotime('+1 year'))) }}">
                    </div>

                    <!-- Fitness Expiry Date -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Fitness Certificate Expiry</label>
                        <input type="date" name="fitness_expiry_date" class="form-control font-monospace" value="{{ old('fitness_expiry_date', date('Y-m-d', strtotime('+1 year'))) }}">
                    </div>

                    <!-- PUC Expiry Date -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">PUC Expiry Date</label>
                        <input type="date" name="puc_expiry_date" class="form-control font-monospace" value="{{ old('puc_expiry_date', date('Y-m-d', strtotime('+6 months'))) }}">
                    </div>

                    <!-- Notes -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-body">Notes</label>
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
