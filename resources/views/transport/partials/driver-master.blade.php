<div class="col-12">
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        
        <!-- Header & Register Action -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary text-white font-monospace px-3 py-1 rounded-pill">Drivers Roster</span>
                    <span class="badge bg-body-tertiary text-body border border-translucent rounded-pill px-3 py-1 font-monospace">
                        Total Registered Drivers: {{ $drivers->total() }}
                    </span>
                </div>
                <h4 class="fw-black text-body mb-0">👤 Driver Management</h4>
                <p class="text-muted small mb-0 mt-1">Permanent driver identities (`DRV-000001`), verification, license compliance, operational statuses & suspension audits.</p>
            </div>
            <button class="btn btn-primary rounded-3 px-4 py-2.5 fw-bold d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegisterDriver">
                <span>➕</span> Register New Driver
            </button>
        </div>

        <!-- Search Bar & Status Filters -->
        <div class="row g-3 mb-4 align-items-center">
            <!-- Search Input -->
            <div class="col-md-6 col-lg-5">
                <form method="GET" action="{{ route('transport.drivers.index') }}">
                    <input type="hidden" name="tab" value="drivers">
                    <input type="hidden" name="driver_status" value="{{ $driverStatus }}">
                    <div class="input-group search-box">
                        <span class="input-group-text bg-body-tertiary border-translucent">🔍</span>
                        <input type="text" name="driver_search" class="form-control bg-body-tertiary border-translucent" 
                               placeholder="Search Driver ID (e.g. DRV-000001), Name, Mobile, License..." 
                               value="{{ $driverSearch }}">
                        @if($driverSearch)
                            <a href="{{ route('transport.drivers.index', ['driver_status' => $driverStatus]) }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                        <button type="submit" class="btn btn-primary fw-semibold px-3">Search</button>
                    </div>
                </form>
            </div>

            <!-- Status Filter Pills -->
            <div class="col-md-6 col-lg-7">
                <div class="d-flex flex-wrap gap-1.5 justify-content-md-end">
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'all']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $driverStatus === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}">
                        All ({{ $allDrivers->count() }})
                    </a>
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'available']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $driverStatus === 'available' ? 'btn-success' : 'btn-outline-success' }}">
                        Available ({{ $allDrivers->where('status', 'available')->count() }})
                    </a>
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'on_delivery']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ in_array($driverStatus, ['on_delivery', 'on_trip']) ? 'btn-primary' : 'btn-outline-primary' }}">
                        On Delivery ({{ $allDrivers->whereIn('status', ['on_delivery', 'on_trip'])->count() }})
                    </a>
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'leave']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ in_array($driverStatus, ['leave', 'on_leave']) ? 'btn-info text-white' : 'btn-outline-info' }}">
                        On Leave ({{ $allDrivers->whereIn('status', ['leave', 'on_leave'])->count() }})
                    </a>
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'suspended']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $driverStatus === 'suspended' ? 'btn-danger' : 'btn-outline-danger' }}">
                        Suspended ({{ $allDrivers->where('status', 'suspended')->count() }})
                    </a>
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'inactive']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $driverStatus === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                        Inactive ({{ $allDrivers->where('status', 'inactive')->count() }})
                    </a>
                    <a href="{{ route('transport.drivers.index', ['driver_search' => $driverSearch, 'driver_status' => 'expiring_soon']) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $driverStatus === 'expiring_soon' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                        ⚠️ License Expiring ({{ $allDrivers->filter(fn($d) => $d->isLicenseExpired() || $d->isLicenseExpiringSoon())->count() }})
                    </a>
                </div>
            </div>
        </div>

        <!-- Driver Master Table -->
        @if($drivers->isEmpty())
            <div class="p-5 text-center bg-body-tertiary rounded-4 border border-translucent my-3">
                <div class="fs-1 text-muted mb-2">👤</div>
                <h5 class="fw-bold text-body mb-1">No drivers found</h5>
                <p class="text-muted small mb-3">No registered driver records match your current search query or filter parameters.</p>
                <a href="{{ route('transport.drivers.index') }}" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Reset Filters</a>
            </div>
        @else
            <div class="table-responsive rounded-3 border border-translucent mb-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-body-tertiary text-muted small text-uppercase fw-bold">
                        <tr>
                            <th class="ps-3">Driver ID</th>
                            <th>Driver Name</th>
                            <th>Mobile Number</th>
                            <th>License Category</th>
                            <th>License Expiry</th>
                            <th>Status</th>
                            <th>Current Assignment</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($drivers as $drv)
                            <tr>
                                <!-- Driver ID -->
                                <td class="ps-3">
                                    <span class="badge bg-primary text-white font-monospace fs-6 px-2.5 py-1.5 rounded-pill shadow-xs">
                                        {{ $drv->driver_code ?? ('DRV-' . str_pad((string)$drv->id, 6, '0', STR_PAD_LEFT)) }}
                                    </span>
                                </td>

                                <!-- Driver Name -->
                                <td>
                                    <div class="fw-bold text-body d-flex align-items-center gap-1.5">
                                        <span>{{ $drv->driver_name }}</span>
                                        @if($drv->email)
                                            <span class="text-muted small" title="{{ $drv->email }}">✉️</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted font-monospace">{{ $drv->employee_id }}</div>
                                </td>

                                <!-- Mobile Number -->
                                <td>
                                    <div class="fw-semibold font-monospace text-body">📞 {{ $drv->phone_number }}</div>
                                    @if($drv->emergency_contact_number || $drv->emergency_contact)
                                        <div class="small text-muted" title="Emergency Contact">🆘 {{ $drv->emergency_contact_number ?? $drv->emergency_contact }}</div>
                                    @endif
                                </td>

                                <!-- License Category -->
                                <td>
                                    <span class="badge bg-body-tertiary text-body border border-translucent px-2.5 py-1 font-monospace">
                                        {{ $drv->license_class }}
                                    </span>
                                    <div class="small text-muted font-monospace mt-0.5">{{ $drv->driving_license_number }}</div>
                                </td>

                                <!-- License Expiry -->
                                <td>
                                    @if($drv->isLicenseExpired())
                                        <span class="badge bg-danger text-white px-2.5 py-1 font-monospace">
                                            ⛔ Expired ({{ $drv->license_expiry_date->format('d M Y') }})
                                        </span>
                                    @elseif($drv->isLicenseExpiringSoon())
                                        <span class="badge bg-warning text-dark px-2.5 py-1 font-monospace">
                                            ⚠️ Expiring ({{ $drv->license_expiry_date->format('d M Y') }})
                                        </span>
                                    @else
                                        <span class="text-body font-monospace small">
                                            📅 {{ $drv->license_expiry_date ? $drv->license_expiry_date->format('d M Y') : 'N/A' }}
                                        </span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td>
                                    <span class="badge {{ $drv->status_badge_class }} px-3 py-1.5 rounded-pill font-semibold">
                                        {{ $drv->status_label }}
                                    </span>
                                </td>

                                <!-- Current Assignment -->
                                <td>
                                    <div class="small fw-semibold text-body">{{ $drv->current_assignment ?? 'Available for Assignment' }}</div>
                                    <div class="small text-muted">{{ $drv->trips_count }} Trips Logged</div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="text-end pe-3">
                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                        <!-- Profile View Button -->
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-3 px-2.5" 
                                                data-bs-toggle="modal" data-bs-target="#modalDriverProfile{{ $drv->id }}" 
                                                title="View Complete Driver Profile">
                                            📋 Profile
                                        </button>

                                        <!-- Edit Button -->
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-2.5" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditDriver{{ $drv->id }}" 
                                                title="Edit Driver Information">
                                            ✏️ Edit
                                        </button>

                                        <!-- Suspension / Reactivation Button -->
                                        @if($drv->isSuspended())
                                            <form method="POST" action="{{ route('transport.drivers.activate', $drv->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success rounded-3 px-2.5" 
                                                        onclick="return confirm('Reactivate driver {{ $drv->driver_name }} ({{ $drv->driver_code }})?')"
                                                        title="Reactivate Suspended Driver">
                                                    🔓 Reactivate
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-3 px-2.5" 
                                                    data-bs-toggle="modal" data-bs-target="#modalSuspendDriver{{ $drv->id }}" 
                                                    title="Suspend Driver">
                                                🚫 Suspend
                                            </button>
                                        @endif

                                        <!-- Activation / Deactivation Toggle -->
                                        @if($drv->isInactive())
                                            <form method="POST" action="{{ route('transport.drivers.activate', $drv->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-3 px-2.5" 
                                                        onclick="return confirm('Activate driver {{ $drv->driver_name }}?')"
                                                        title="Activate Driver">
                                                    ✅ Activate
                                                </button>
                                            </form>
                                        @elseif(!$drv->isSuspended())
                                            <form method="POST" action="{{ route('transport.drivers.deactivate', $drv->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-3 px-2.5" 
                                                        onclick="return confirm('Deactivate driver {{ $drv->driver_name }} ({{ $drv->driver_code }})? Driver record and history will be preserved.')"
                                                        title="Deactivate Driver">
                                                    💤 Deactivate
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- ========================================================================= -->
                            <!-- MODAL 1: DRIVER PROFILE (DEDICATED VIEW) -->
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
                                                            {{ $drv->driver_code ?? ('DRV-' . str_pad((string)$drv->id, 6, '0', STR_PAD_LEFT)) }}
                                                        </span>
                                                    </div>
                                                    <span class="small text-muted font-monospace">Employee ID: {{ $drv->employee_id }}</span>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>

                                        <div class="modal-body p-4">
                                            <!-- Status Strip -->
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
                                                <!-- Left Column: Personal & Contact -->
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

                                                <!-- Right Column: License & Employment -->
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

                                                    <div class="mb-2">
                                                        <span class="text-muted small d-block">System Audit Timestamps</span>
                                                        <div class="small text-muted font-monospace">Created: {{ $drv->created_at ? $drv->created_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                        <div class="small text-muted font-monospace">Last Updated: {{ $drv->updated_at ? $drv->updated_at->format('d M Y, H:i') : 'N/A' }}</div>
                                                    </div>
                                                </div>

                                                <!-- Suspension Records Box -->
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

                                                <!-- Notes -->
                                                @if($drv->notes)
                                                    <div class="col-12">
                                                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                                                            <span class="text-muted small fw-bold d-block mb-1">📝 Manager Operational Notes</span>
                                                            <p class="small text-body mb-0">{{ $drv->notes }}</p>
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
                                                <!-- Driver ID (Readonly) -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-muted">Permanent Driver ID (Immutable)</label>
                                                    <input type="text" class="form-control bg-body-tertiary font-monospace fw-bold" 
                                                           value="{{ $drv->driver_code ?? ('DRV-' . str_pad((string)$drv->id, 6, '0', STR_PAD_LEFT)) }}" disabled>
                                                    <span class="form-text text-muted" style="font-size: 0.75rem;">Driver ID is permanent & cannot be edited.</span>
                                                </div>

                                                <!-- Driver Full Name -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Full Name *</label>
                                                    <input type="text" name="driver_name" class="form-control" value="{{ $drv->driver_name }}" required>
                                                </div>

                                                <!-- Phone Number -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Mobile Number (+91 Format) *</label>
                                                    <input type="text" name="phone_number" class="form-control font-monospace" value="{{ $drv->phone_number }}" required>
                                                </div>

                                                <!-- Email -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Email Address</label>
                                                    <input type="email" name="email" class="form-control" value="{{ $drv->email }}">
                                                </div>

                                                <!-- License Class / Category -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">License Category *</label>
                                                    <select name="license_class" class="form-select" required>
                                                        <option value="Heavy Commercial (HMV)" {{ $drv->license_class === 'Heavy Commercial (HMV)' ? 'selected' : '' }}>Heavy Commercial (HMV)</option>
                                                        <option value="Medium Goods Vehicle (MGV)" {{ $drv->license_class === 'Medium Goods Vehicle (MGV)' ? 'selected' : '' }}>Medium Goods Vehicle (MGV)</option>
                                                        <option value="Light Motor Vehicle (LMV)" {{ $drv->license_class === 'Light Motor Vehicle (LMV)' ? 'selected' : '' }}>Light Motor Vehicle (LMV)</option>
                                                        <option value="Motorcycle With Gear (MCWG)" {{ $drv->license_class === 'Motorcycle With Gear (MCWG)' ? 'selected' : '' }}>Motorcycle With Gear (MCWG)</option>
                                                    </select>
                                                </div>

                                                <!-- License Number -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Driving License Number *</label>
                                                    <input type="text" name="driving_license_number" class="form-control font-monospace" value="{{ $drv->driving_license_number }}" required>
                                                </div>

                                                <!-- License Expiry Date -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">License Expiry Date *</label>
                                                    <input type="date" name="license_expiry_date" class="form-control font-monospace" 
                                                           value="{{ $drv->license_expiry_date ? $drv->license_expiry_date->format('Y-m-d') : '' }}" required>
                                                </div>

                                                <!-- Date of Birth -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Date of Birth</label>
                                                    <input type="date" name="date_of_birth" class="form-control font-monospace" 
                                                           value="{{ $drv->date_of_birth ? $drv->date_of_birth->format('Y-m-d') : '' }}">
                                                </div>

                                                <!-- Joining Date -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Joining Date</label>
                                                    <input type="date" name="joining_date" class="form-control font-monospace" 
                                                           value="{{ $drv->joining_date ? $drv->joining_date->format('Y-m-d') : '' }}">
                                                </div>

                                                <!-- Emergency Contact Name -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Emergency Contact Name</label>
                                                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ $drv->emergency_contact_name }}">
                                                </div>

                                                <!-- Emergency Contact Number -->
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-bold text-body">Emergency Contact Number</label>
                                                    <input type="text" name="emergency_contact_number" class="form-control font-monospace" value="{{ $drv->emergency_contact_number ?? $drv->emergency_contact }}">
                                                </div>

                                                <!-- Address -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-body">Address</label>
                                                    <textarea name="address" class="form-control" rows="2">{{ $drv->address }}</textarea>
                                                </div>

                                                <!-- Notes -->
                                                <div class="col-12">
                                                    <label class="form-label small fw-bold text-body">Manager Operational Notes</label>
                                                    <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes regarding performance or medical certificates...">{{ $drv->notes }}</textarea>
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
                            <!-- MODAL 3: SUSPEND DRIVER (REQUIRES REASON) -->
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
                                                    <strong>Caution:</strong> Suspending driver <strong>{{ $drv->driver_name }}</strong> (<code>{{ $drv->driver_code }}</code>) will prevent dispatch assignments. Suspension reason will be recorded permanently in the audit log.
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label small fw-bold text-body">Reason for Suspension *</label>
                                                    <textarea name="suspension_reason" class="form-control" rows="3" required
                                                              placeholder="e.g. License compliance pending, safety policy violation, medical re-examination required..."></textarea>
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
                    </tbody>
                </table>
            </div>

            <!-- Server-Side Pagination Links -->
            <div class="d-flex justify-content-between align-items-center mt-3">
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
                                System Auto-Generated (Format: DRV-000001)
                            </span>
                        </div>
                    </div>

                    <!-- Driver Full Name -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Full Name *</label>
                        <input type="text" name="driver_name" class="form-control" placeholder="e.g. Ramesh Kumar" value="{{ old('driver_name') }}" required>
                    </div>

                    <!-- Mobile Number -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Mobile Number (+91 Format) *</label>
                        <input type="text" name="phone_number" class="form-control font-monospace" placeholder="e.g. +91 98765 43210" value="{{ old('phone_number') }}" required>
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. driver@stockmanager.com" value="{{ old('email') }}">
                    </div>

                    <!-- Date of Birth -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Date of Birth</label>
                        <input type="date" name="date_of_birth" class="form-control font-monospace" value="{{ old('date_of_birth') }}">
                    </div>

                    <!-- Driving License Number -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Driving License Number *</label>
                        <input type="text" name="driving_license_number" class="form-control font-monospace" placeholder="e.g. MH-02-2021-1234567" value="{{ old('driving_license_number') }}" required>
                    </div>

                    <!-- License Category -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">License Category *</label>
                        <select name="license_class" class="form-select" required>
                            <option value="Heavy Commercial (HMV)" selected>Heavy Commercial (HMV)</option>
                            <option value="Medium Goods Vehicle (MGV)">Medium Goods Vehicle (MGV)</option>
                            <option value="Light Motor Vehicle (LMV)">Light Motor Vehicle (LMV)</option>
                            <option value="Motorcycle With Gear (MCWG)">Motorcycle With Gear (MCWG)</option>
                        </select>
                    </div>

                    <!-- License Expiry Date -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">License Expiry Date *</label>
                        <input type="date" name="license_expiry_date" class="form-control font-monospace" value="{{ old('license_expiry_date', date('Y-m-d', strtotime('+3 years'))) }}" required>
                    </div>

                    <!-- Joining Date -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Joining Date</label>
                        <input type="date" name="joining_date" class="form-control font-monospace" value="{{ old('joining_date', date('Y-m-d')) }}">
                    </div>

                    <!-- Emergency Contact Name -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Emergency Contact Name</label>
                        <input type="text" name="emergency_contact_name" class="form-control" placeholder="e.g. Sunita Kumar" value="{{ old('emergency_contact_name') }}">
                    </div>

                    <!-- Emergency Contact Number -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-body">Emergency Contact Number</label>
                        <input type="text" name="emergency_contact_number" class="form-control font-monospace" placeholder="e.g. +91 98765 99999" value="{{ old('emergency_contact_number') }}">
                    </div>

                    <!-- Address -->
                    <div class="col-12">
                        <label class="form-label small fw-bold text-body">Address</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Street address, city, state, pin code...">{{ old('address') }}</textarea>
                    </div>

                    <!-- Operational Notes -->
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
