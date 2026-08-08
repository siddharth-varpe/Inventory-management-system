<div class="col-12">
    <!-- MAIN PAGE HEADER -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-black text-body mb-0">Delivery Orders</h3>
            <p class="text-muted small mb-0 mt-1">Synchronized Sales Orders from CRM & Organize Stock</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold font-monospace">
                Total Orders: {{ $statusCounts['all'] ?? $requests->total() }}
            </span>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-3 px-3 fw-bold d-flex align-items-center gap-1.5" onclick="refreshDeliveryOrders()">
                🔄 Refresh
            </button>
        </div>
    </div>

    <!-- STATUS SUMMARY FILTER CARDS (LAYOUT B) -->
    <div class="row g-3 mb-4">
        @php
            $currentStatusCard = request('status_card', 'all');
        @endphp
        <!-- ALL -->
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'all'])) }}" 
               class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'all' ? 'border-primary bg-primary-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
                <div class="text-muted font-monospace small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">ALL</div>
                <div class="fs-4 fw-black text-primary font-monospace">{{ $statusCounts['all'] ?? 0 }}</div>
            </a>
        </div>

        <!-- READY -->
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'ready'])) }}" 
               class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'ready' ? 'border-success bg-success-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
                <div class="text-muted font-monospace small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">READY</div>
                <div class="fs-4 fw-black text-success font-monospace">{{ $statusCounts['ready'] ?? 0 }}</div>
            </a>
        </div>

        <!-- ASSIGNED -->
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'assigned'])) }}" 
               class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'assigned' ? 'border-purple bg-purple-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
                <div class="text-muted font-monospace small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">ASSIGNED</div>
                <div class="fs-4 fw-black text-purple font-monospace" style="color: #9333ea;">{{ $statusCounts['assigned'] ?? 0 }}</div>
            </a>
        </div>

        <!-- ACTIVE -->
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'active'])) }}" 
               class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'active' ? 'border-warning bg-warning-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
                <div class="text-muted font-monospace small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">ACTIVE</div>
                <div class="fs-4 fw-black text-warning font-monospace">{{ $statusCounts['active'] ?? 0 }}</div>
            </a>
        </div>

        <!-- COMPLETED -->
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'completed'])) }}" 
               class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'completed' ? 'border-info bg-info-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
                <div class="text-muted font-monospace small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">COMPLETED</div>
                <div class="fs-4 fw-black text-info font-monospace">{{ $statusCounts['completed'] ?? 0 }}</div>
            </a>
        </div>

        <!-- CANCELLED -->
        <div class="col-6 col-sm-4 col-md-2">
            <a href="{{ route('transport.index', array_merge(request()->except('page', 'status_card'), ['tab' => 'delivery-orders', 'status_card' => 'cancelled'])) }}" 
               class="card p-3 rounded-4 border text-decoration-none transition-all {{ $currentStatusCard === 'cancelled' ? 'border-danger bg-danger-subtle shadow-sm' : 'border-translucent bg-body hover-bg-tertiary' }}">
                <div class="text-muted font-monospace small fw-bold text-uppercase mb-1" style="font-size: 0.7rem;">CANCELLED</div>
                <div class="fs-4 fw-black text-danger font-monospace">{{ $statusCounts['cancelled'] ?? 0 }}</div>
            </a>
        </div>
    </div>

    <!-- SEARCH & FILTER ROW -->
    <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body mb-4">
        <form method="GET" action="{{ route('transport.index') }}" id="deliveryFilterForm" class="row g-2 align-items-center">
            <input type="hidden" name="tab" value="delivery-orders">
            @if(request('status_card'))
                <input type="hidden" name="status_card" value="{{ request('status_card') }}">
            @endif

            <!-- Search Field -->
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-translucent text-muted">🔍</span>
                    <input type="text" name="search" class="form-control bg-body border-translucent" 
                           placeholder="Search Order ID, Customer, City..." 
                           value="{{ request('search') }}">
                </div>
            </div>

            <!-- Priority Dropdown -->
            <div class="col-md-3">
                <select name="priority" class="form-select form-select-sm bg-body border-translucent" onchange="this.form.submit()">
                    <option value="all">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>🚨 Urgent Priority</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>🔥 High Priority</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>📦 Normal Priority</option>
                </select>
            </div>

            <!-- City Dropdown -->
            <div class="col-md-2">
                <select name="city" class="form-select form-select-sm bg-body border-translucent" onchange="this.form.submit()">
                    <option value="all">All Cities</option>
                    @foreach($availableCities as $c)
                        <option value="{{ $c }}" {{ request('city') == $c ? 'selected' : '' }}>📍 {{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="col-md-2 d-flex gap-1.5 justify-content-end">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold rounded-3">Filter</button>
                @if(request('search') || request('priority') || request('city') || request('status_card'))
                    <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" class="btn btn-sm btn-outline-secondary rounded-3" title="Reset Filters">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- LOADING SKELETON CONTAINER (HIDDEN BY DEFAULT) -->
    <div id="deliveryCardsSkeleton" class="vstack gap-3 d-none mb-4">
        @for($i = 0; $i < 3; $i++)
            <div class="card p-3.5 rounded-4 shadow-sm border-translucent bg-body placeholder-glow">
                <div class="d-flex justify-content-between pb-3 border-bottom border-translucent">
                    <div>
                        <span class="placeholder col-4 rounded-3 py-2 mb-2"></span>
                        <span class="placeholder col-6 rounded-3 py-1"></span>
                    </div>
                    <span class="placeholder col-3 rounded-3 py-2"></span>
                </div>
                <div class="pt-3 d-flex justify-content-between">
                    <span class="placeholder col-8 rounded-3 py-2"></span>
                    <span class="placeholder col-2 rounded-3 py-2"></span>
                </div>
            </div>
        @endfor
    </div>

    <!-- DELIVERY CARDS CONTAINER (LAYOUT B CORE STRUCTURE) -->
    <div id="deliveryCardsContainer" class="vstack gap-3">
        @forelse($requests as $r)
            <div class="card p-3.5 rounded-4 shadow-sm border-translucent bg-body delivery-order-card">
                <!-- CARD HEADER: ORDER ID, STATUS, CUSTOMER, DESTINATION -->
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 pb-3 border-bottom border-translucent">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="fs-5">🚚</span>
                            <a href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})" class="fw-black text-primary text-decoration-none fs-5 font-monospace">
                                {{ $r->order_reference }}
                            </a>
                            <span class="badge rounded-pill {{ $r->priority_badge_class }} px-2.5 py-1 small">
                                {{ strtoupper($r->priority ?? 'NORMAL') }}
                            </span>
                            <span class="badge rounded-pill {{ $r->status_badge_class }} px-2.5 py-1 small">
                                {{ $r->status_label }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <span class="small text-muted font-monospace" style="font-size: 0.78rem;">Task ID: {{ $r->request_number }}</span>
                            <!-- WAREHOUSE FULFILLMENT BADGE -->
                            @if(!empty($r->warehouse_completed_at) || $r->warehouse_status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small" style="font-size: 0.75rem;">
                                    ✓ Seal & Ready to Dispatch {{ $r->warehouse_completed_at ? '('.$r->warehouse_completed_at->format('d M Y, H:i').')' : '' }}
                                </span>
                            @elseif($r->status === 'awaiting_warehouse')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill small" style="font-size: 0.75rem;">
                                    ⏳ Awaiting Warehouse Pick & Pack
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill small" style="font-size: 0.75rem;">
                                    📦 Warehouse In Progress
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- CUSTOMER & DESTINATION -->
                    <div class="text-md-end">
                        <div class="fw-bold text-body fs-6">{{ $r->customer_name }}</div>
                        <div class="small text-muted">📍 {{ $r->city }} — <span class="text-truncate d-inline-block align-bottom" style="max-width: 250px;">{{ $r->delivery_address }}</span></div>
                    </div>
                </div>

                <!-- CARD FOOTER: DRIVER, VEHICLE, EXPECTED DELIVERY, ACTIONS -->
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pt-3">
                    <div class="row g-3 flex-grow-1 align-items-center">
                        <!-- ASSIGNED DRIVER -->
                        <div class="col-6 col-md-4">
                            <div class="text-muted small" style="font-size: 0.75rem;">Assigned Driver</div>
                            @if($r->driver)
                                <div class="fw-bold text-body small">👤 {{ $r->driver->driver_name }}</div>
                                <div class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $r->driver->driver_code }}</div>
                            @else
                                <div class="text-muted small fst-italic">Not Assigned</div>
                            @endif
                        </div>

                        <!-- ASSIGNED VEHICLE -->
                        <div class="col-6 col-md-4">
                            <div class="text-muted small" style="font-size: 0.75rem;">Assigned Vehicle</div>
                            @if($r->vehicle)
                                <div class="fw-bold text-body font-monospace small">🚛 {{ $r->vehicle->vehicle_number }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">{{ $r->vehicle->vehicle_type }}</div>
                            @else
                                <div class="text-muted small fst-italic">Not Assigned</div>
                            @endif
                        </div>

                        <!-- EXPECTED DELIVERY -->
                        <div class="col-12 col-md-4">
                            <div class="text-muted small" style="font-size: 0.75rem;">Expected Delivery</div>
                            <div class="fw-semibold text-body small font-monospace">
                                {{ $r->expected_delivery_date ? $r->expected_delivery_date->format('d M Y') : '—' }}
                            </div>
                        </div>
                    </div>

                    <!-- ACTION BUTTON & THREE-DOT DROPDOWN MENU -->
                    <div class="d-flex align-items-center gap-2 ms-md-auto">
                        <button type="button" class="btn btn-sm btn-primary px-3.5 fw-bold rounded-3 shadow-sm" onclick="openDeliveryOrderProfile({{ $r->id }})">
                            @if(in_array($r->status, ['ready_for_assignment', 'waiting_planning', 'awaiting_warehouse']))
                                View / Assign
                            @elseif(in_array($r->status, ['driver_vehicle_assigned', 'assigned']))
                                View Assignment
                            @elseif(in_array($r->status, ['dispatched', 'in_transit']))
                                View Delivery
                            @else
                                View Profile
                            @endif
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-2.5" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                ⋮
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-translucent">
                                <li>
                                    <a class="dropdown-item small" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                        📄 View Profile
                                    </a>
                                </li>
                                @if(!in_array($r->status, ['dispatched', 'in_transit', 'delivered', 'completed', 'cancelled']))
                                    <li>
                                        <a class="dropdown-item small" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                            👤 Assign Driver & Vehicle
                                        </a>
                                    </li>
                                @endif
                                @if(in_array($r->status, ['driver_vehicle_assigned', 'assigned', 'ready_for_dispatch']))
                                    <li>
                                        <a class="dropdown-item small text-success fw-bold" href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $r->id }})">
                                            🚀 Confirm Dispatch
                                        </a>
                                    </li>
                                @endif
                                @if(in_array($r->status, ['dispatched', 'in_transit']))
                                    <li>
                                        <a class="dropdown-item small text-danger" href="javascript:void(0)" onclick="openCancelDispatchModal({{ $r->id }}, '{{ $r->order_reference }}', '{{ $r->dispatch_number }}')">
                                            🚫 Cancel Dispatch
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- EMPTY STATE CARD -->
            <div class="card p-5 rounded-4 border-translucent bg-body text-center">
                <div class="fs-1 mb-2">📦</div>
                <h5 class="fw-bold text-body mb-1">No delivery orders found</h5>
                <p class="text-muted small mb-3">Orders synchronized from CRM and Warehouse will appear here.</p>
                @if(request('search') || request('priority') || request('city') || request('status_card'))
                    <div>
                        <a href="{{ route('transport.index', ['tab' => 'delivery-orders']) }}" class="btn btn-sm btn-outline-primary px-4 rounded-3 fw-bold">
                            Reset Filters
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    <!-- SERVER-SIDE PAGINATION -->
    @if($requests->hasPages())
        <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mt-4">
            <div class="text-muted small font-monospace">
                Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} delivery orders
            </div>
            <div>
                {{ $requests->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>

<!-- ========================================================================= -->
<!-- DEDICATED DELIVERY ORDER PROFILE & ASSIGNMENT MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDeliveryOrderProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-3 bg-body-tertiary rounded-top-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-4">📦</span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-black text-body mb-0" id="profOrderReference">SO-2026-000001</h5>
                            <span class="badge bg-primary font-monospace px-2.5 py-1" id="profRequestNumber">TRN-000001</span>
                            <span class="badge rounded-pill" id="profPriorityBadge">NORMAL</span>
                        </div>
                        <div class="small text-muted" id="profCustomerName">Customer Company Name</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Order Overview Section -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Destination City</span>
                            <strong class="text-body fs-6" id="profCity">Mumbai</strong>
                            <div class="small text-muted mt-1" id="profAddress">123 Street Address</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Package & Weight</span>
                            <strong class="text-body fs-6" id="profPackages">5 Cartons</strong>
                            <div class="small text-muted mt-1" id="profWeight">120.0 kg | 1.5 m³</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Expected Delivery</span>
                            <strong class="text-body fs-6" id="profExpectedDate">10 Aug 2026</strong>
                            <div class="small text-muted mt-1" id="profSourceModule">CRM Sales Order</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                            <span class="text-muted small d-block">Warehouse Fulfillment</span>
                            <span class="badge rounded-pill mt-1" id="profWarehouseBadge">Completed</span>
                            <div class="small text-muted mt-1" id="profWarehouseTime">H:i, d M Y</div>
                        </div>
                    </div>
                </div>

                <!-- Assignment Card Section -->
                <div class="card border-translucent rounded-3 mb-4 bg-body-tertiary" id="profAssignmentCard">
                    <div class="card-header bg-body border-bottom border-translucent d-flex align-items-center justify-content-between p-3">
                        <h6 class="fw-bold text-body mb-0">👨‍✈️ Driver & Vehicle Resource Assignment</h6>
                        <span class="badge rounded-pill font-monospace px-2.5 py-1" id="profAssignmentStatusBadge">READY FOR ASSIGNMENT</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="alert alert-warning border-warning-subtle small mb-3 d-none" id="profAssignmentLockAlert">
                            🔒 <strong>Assignment Locked:</strong> <span id="profLockReason">Resource assignment locked until Organize Stock completes Pick & Pack.</span>
                        </div>

                        <div id="profActiveAssignmentContainer" class="d-none">
                            <div class="p-3 bg-body rounded-3 border border-translucent mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-primary font-monospace" id="profAssignmentNumber">ASN-000001</span>
                                    <span class="small text-muted" id="profAssignedTime">Assigned: H:i</span>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-tertiary rounded-2 border border-translucent">
                                            <strong>👤 Assigned Driver:</strong>
                                            <div class="fw-bold text-body fs-6" id="profAssignedDriverName">John Doe</div>
                                            <div class="text-muted" id="profAssignedDriverPhone">9876543210</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-2 bg-body-tertiary rounded-2 border border-translucent">
                                            <strong>🚛 Assigned Vehicle:</strong>
                                            <div class="fw-bold text-body fs-6" id="profAssignedVehicleReg">MH12AB1234</div>
                                            <div class="text-muted" id="profAssignedVehicleType">Van (1,000 kg capacity)</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2 small text-muted text-end">
                                    Assigned by: <span id="profAssignedByName">Transport Manager</span>
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-outline-warning w-100 fw-bold rounded-3" onclick="toggleReassignForm()">
                                🔄 Reassign Driver / Vehicle
                            </button>
                        </div>

                        <form id="profAssignmentForm" onsubmit="submitAssignmentForm(event)" class="mt-2">
                            <input type="hidden" id="profTaskId" name="task_id" value="">
                            <input type="hidden" id="profIsReassign" value="0">

                            <div class="mb-3 d-none" id="profReassignReasonGroup">
                                <label class="form-label small fw-bold text-warning">Reassignment Reason <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm bg-body border-translucent" 
                                       id="profReassignReason" placeholder="Enter reason for driver/vehicle replacement...">
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-body">Select Eligible Driver <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bg-body border-translucent" id="profSelectDriver" required>
                                        <option value="">-- Choose Eligible Driver --</option>
                                    </select>
                                    <div class="form-text small text-muted" id="profDriverHelp">Only active, available drivers with valid licenses are displayed.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-body">Select Eligible Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bg-body border-translucent" id="profSelectVehicle" onchange="validateVehicleCapacity()" required>
                                        <option value="">-- Choose Eligible Vehicle --</option>
                                    </select>
                                    <div class="form-text small text-muted" id="profVehicleHelp">Only available, operational vehicles are displayed.</div>
                                </div>
                            </div>

                            <div class="alert alert-danger border-danger-subtle small mt-3 mb-0 d-none" id="profCapacityWarning">
                                🚫 <strong>Capacity Validation Failed:</strong> Selected vehicle does not have sufficient capacity for this order.
                            </div>

                            <div class="mt-3 text-end">
                                <button type="submit" class="btn btn-sm btn-primary px-4 fw-bold rounded-3" id="profSubmitAssignBtn">
                                    Confirm Assignment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- PHASE 5 DISPATCH ACTION SECTION -->
                <div class="card border-primary-subtle rounded-3 mb-4 bg-body-tertiary d-none" id="profDispatchCard">
                    <div class="card-header bg-primary-subtle border-bottom border-primary-subtle d-flex align-items-center justify-content-between p-3">
                        <h6 class="fw-bold text-primary mb-0">🚀 Operational Dispatch Control</h6>
                        <span class="badge bg-primary text-white font-monospace px-2.5 py-1" id="profDispatchBadge">READY FOR DISPATCH</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="alert alert-warning border-warning-subtle small mb-3 d-none" id="profDispatchBlockedAlert">
                            ⚠️ <strong>Dispatch Unavailable:</strong> <span id="profDispatchBlockedReason">Warehouse fulfillment or resource assignment incomplete.</span>
                        </div>

                        <div class="alert alert-success border-success-subtle small mb-0 d-none" id="profDispatchedBanner">
                            🚀 <strong>Shipment Dispatched:</strong> Released under Dispatch ID <strong id="profDispatchedNumber">DSP-2026-000001</strong> on <span id="profDispatchedTime">N/A</span>.
                        </div>

                        <div class="text-end d-none" id="profDispatchActionGroup">
                            <button type="button" class="btn btn-success px-4 fw-bold rounded-3 shadow-sm" id="profTriggerDispatchBtn" onclick="openDispatchConfirmModal()">
                                🚀 Confirm Dispatch
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Real-Event Timeline -->
                <div class="p-3 bg-body rounded-3 border border-translucent">
                    <h6 class="fw-bold text-body mb-3 border-bottom pb-2">📜 Real-Event Transport Timeline</h6>
                    <div class="timeline-container ps-3" id="profTimeline">
                        <!-- Rendered via JavaScript -->
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Close Profile</button>
            </div>
        </div>
    </div>
</div>

<!-- DISPATCH CONFIRMATION MODAL (#modalConfirmDispatch) -->
<div class="modal fade" id="modalConfirmDispatch" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-3 bg-primary-subtle text-primary rounded-top-4">
                <h5 class="modal-title fw-black mb-0">🚀 Confirm Shipment Dispatch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-body mb-3">Are you sure this vehicle is being released for live delivery?</p>
                <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-3">
                    <div class="small mb-2"><strong>Order Reference:</strong> <span class="fw-bold font-monospace text-primary" id="confirmOrderRef">SO-2026-000123</span></div>
                    <div class="small mb-2"><strong>Assigned Driver:</strong> <span class="fw-bold text-body" id="confirmDriver">Rahul Sharma</span></div>
                    <div class="small mb-2"><strong>Assigned Vehicle:</strong> <span class="fw-bold text-body font-monospace" id="confirmVehicle">MH12AB1234</span></div>
                    <div class="small mb-0"><strong>Destination:</strong> <span class="fw-bold text-body" id="confirmDestination">Mumbai</span></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-body">Dispatch Notes (Optional)</label>
                    <input type="text" class="form-control form-control-sm bg-body border-translucent" id="confirmDispatchNotes" placeholder="Enter gate pass notes, seal tag numbers, or route remarks...">
                </div>
                <div class="alert alert-info small mb-0">
                    ℹ️ <strong>Operational Action:</strong> This releases the shipment to active delivery, updates driver status to <strong>ON DELIVERY</strong>, and vehicle status to <strong>ON TRIP</strong>.
                </div>
            </div>
            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success rounded-3 px-4 fw-bold shadow-sm" id="confirmDispatchBtn" onclick="executeDispatchOrder()">
                    Confirm Dispatch
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderData = null;

function refreshDeliveryOrders() {
    window.location.reload();
}

function openDeliveryOrderProfile(id) {
    fetch(`/transport/delivery-orders/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => response.json())
        .then(data => {
            currentOrderData = data;

            document.getElementById('profTaskId').value = data.id;
            document.getElementById('profIsReassign').value = '0';
            document.getElementById('profOrderReference').textContent = data.order_reference;
            document.getElementById('profRequestNumber').textContent = data.request_number;
            document.getElementById('profCustomerName').textContent = data.customer_name;
            document.getElementById('profCity').textContent = data.delivery_city;
            document.getElementById('profAddress').textContent = data.delivery_address || 'No specific address provided';
            document.getElementById('profPackages').textContent = (data.package_count || 1) + ' Cartons';
            document.getElementById('profWeight').textContent = `${data.weight_kg} kg | ${data.volume_m3} m³`;
            document.getElementById('profExpectedDate').textContent = data.expected_delivery_date || 'N/A';
            document.getElementById('profSourceModule').textContent = data.source_module;

            const priorityBadge = document.getElementById('profPriorityBadge');
            priorityBadge.className = `badge rounded-pill ${data.priority_badge_class}`;
            priorityBadge.textContent = (data.priority || 'normal').toUpperCase();

            const warehouseBadge = document.getElementById('profWarehouseBadge');
            warehouseBadge.className = `badge rounded-pill ${data.warehouse_status_badge_class}`;
            warehouseBadge.textContent = data.warehouse_status_label;
            document.getElementById('profWarehouseTime').textContent = data.warehouse_completed_at ? `Completed: ${data.warehouse_completed_at}` : 'Fulfillment In Progress';

            const assignStatusBadge = document.getElementById('profAssignmentStatusBadge');
            assignStatusBadge.className = `badge rounded-pill font-monospace ${data.status_badge_class}`;
            assignStatusBadge.textContent = data.status_label;

            const lockAlert = document.getElementById('profAssignmentLockAlert');
            const assignForm = document.getElementById('profAssignmentForm');
            const activeContainer = document.getElementById('profActiveAssignmentContainer');
            const reassignReasonGroup = document.getElementById('profReassignReasonGroup');
            const submitBtn = document.getElementById('profSubmitAssignBtn');

            lockAlert.classList.add('d-none');
            activeContainer.classList.add('d-none');
            assignForm.classList.remove('d-none');
            reassignReasonGroup.classList.add('d-none');
            document.getElementById('profReassignReason').required = false;

            submitBtn.textContent = 'Confirm Assignment';
            submitBtn.className = 'btn btn-sm btn-primary px-4 fw-bold rounded-3';

            const driverSelect = document.getElementById('profSelectDriver');
            driverSelect.innerHTML = '<option value="">-- Choose Eligible Driver --</option>';
            if (data.eligible_drivers && data.eligible_drivers.length > 0) {
                data.eligible_drivers.forEach(drv => {
                    const opt = document.createElement('option');
                    opt.value = drv.id;
                    opt.textContent = `👤 ${drv.driver_name} (${drv.employee_id}) — ${drv.license_class || 'LMN'}`;
                    driverSelect.appendChild(opt);
                });
            }

            const vehicleSelect = document.getElementById('profSelectVehicle');
            vehicleSelect.innerHTML = '<option value="">-- Choose Eligible Vehicle --</option>';
            if (data.eligible_vehicles && data.eligible_vehicles.length > 0) {
                data.eligible_vehicles.forEach(veh => {
                    const opt = document.createElement('option');
                    opt.value = veh.id;
                    opt.setAttribute('data-capacity', veh.load_capacity_kg || 0);
                    opt.textContent = `🚛 ${veh.vehicle_number} (${veh.vehicle_type}) — Cap: ${veh.load_capacity_kg} kg`;
                    vehicleSelect.appendChild(opt);
                });
            }

            if (data.status === 'awaiting_warehouse') {
                assignForm.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Resource assignment locked until Organize Stock completes Pick & Pack and seals the shipment.';
            } else if (data.status === 'cancelled') {
                assignForm.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Order is cancelled. Resource assignment is unavailable.';
            } else if (data.active_assignment) {
                activeContainer.classList.remove('d-none');
                assignForm.classList.add('d-none');

                document.getElementById('profAssignmentNumber').textContent = data.active_assignment.assignment_number;
                document.getElementById('profAssignedTime').textContent = 'Assigned: ' + data.active_assignment.assigned_at;
                document.getElementById('profAssignedDriverName').textContent = data.active_assignment.driver_name || 'N/A';
                document.getElementById('profAssignedDriverPhone').textContent = data.active_assignment.driver_phone || 'N/A';
                document.getElementById('profAssignedVehicleReg').textContent = data.active_assignment.vehicle_number || 'N/A';
                document.getElementById('profAssignedVehicleType').textContent = (data.active_assignment.vehicle_type || 'Vehicle') + ' (Assigned)';
                document.getElementById('profAssignedByName').textContent = data.active_assignment.assigned_by_name || 'Transport Manager';
            }

            const dispatchCard = document.getElementById('profDispatchCard');
            const dispatchBlockedAlert = document.getElementById('profDispatchBlockedAlert');
            const dispatchedBanner = document.getElementById('profDispatchedBanner');
            const dispatchActionGroup = document.getElementById('profDispatchActionGroup');

            if (dispatchCard) {
                dispatchCard.classList.remove('d-none');
                dispatchBlockedAlert.classList.add('d-none');
                dispatchedBanner.classList.add('d-none');
                dispatchActionGroup.classList.add('d-none');

                if (data.status === 'dispatched' || data.status === 'in_transit') {
                    dispatchedBanner.classList.remove('d-none');
                    document.getElementById('profDispatchedNumber').textContent = data.dispatch_number || 'DSP-ACTIVE';
                    document.getElementById('profDispatchedTime').textContent = data.dispatched_at || 'Dispatched';
                } else {
                    const elig = data.dispatch_eligibility || { eligible: false, reason: 'Dispatch conditions not met.' };
                    if (elig.eligible) {
                        dispatchActionGroup.classList.remove('d-none');
                    } else {
                        dispatchBlockedAlert.classList.remove('d-none');
                        document.getElementById('profDispatchBlockedReason').textContent = elig.reason;
                    }
                }
            }

            const timelineContainer = document.getElementById('profTimeline');
            timelineContainer.innerHTML = '';

            if (data.timeline && data.timeline.length > 0) {
                data.timeline.forEach(item => {
                    const eventDiv = document.createElement('div');
                    eventDiv.className = 'd-flex align-items-start gap-3 mb-3';
                    eventDiv.innerHTML = `
                        <div class="fs-5">${item.icon || '📌'}</div>
                        <div class="flex-grow-1 border-bottom border-translucent pb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-body small">${item.title}</span>
                                <span class="small text-muted font-monospace" style="font-size: 0.75rem;">${item.timestamp || 'Pending'}</span>
                            </div>
                            <div class="small text-muted mt-0.5">${item.description}</div>
                        </div>
                    `;
                    timelineContainer.appendChild(eventDiv);
                });
            } else {
                timelineContainer.innerHTML = '<div class="text-muted small">No timeline events recorded yet.</div>';
            }

            const modal = new bootstrap.Modal(document.getElementById('modalDeliveryOrderProfile'));
            modal.show();
        })
        .catch(error => {
            console.error('Error fetching delivery order profile:', error);
            alert('Failed to fetch delivery order profile details.');
        });
}

function openDispatchConfirmModal() {
    if (!currentOrderData) return;
    document.getElementById('confirmOrderRef').textContent = currentOrderData.order_reference;
    document.getElementById('confirmDriver').textContent = (currentOrderData.driver?.driver_name || 'Driver') + ' (' + (currentOrderData.driver?.driver_code || '') + ')';
    document.getElementById('confirmVehicle').textContent = (currentOrderData.vehicle?.vehicle_number || 'Vehicle') + ' (' + (currentOrderData.vehicle?.vehicle_code || '') + ')';
    document.getElementById('confirmDestination').textContent = currentOrderData.delivery_city || currentOrderData.delivery_address || 'Destination';

    const modal = new bootstrap.Modal(document.getElementById('modalConfirmDispatch'));
    modal.show();
}

function executeDispatchOrder() {
    if (!currentOrderData || !currentOrderData.id) return;

    const confirmBtn = document.getElementById('confirmDispatchBtn');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Processing Dispatch...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const notes = document.getElementById('confirmDispatchNotes')?.value || '';

    fetch(`/transport/delivery-orders/${currentOrderData.id}/dispatch`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ dispatch_notes: notes }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Dispatch Failed: ' + (data.message || 'Validation Error'));
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Dispatch';
        }
    })
    .catch(error => {
        console.error('Dispatch execution error:', error);
        alert('Dispatch could not be completed. Refresh the order and try again.');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm Dispatch';
    });
}

function validateVehicleCapacity() {
    const vehicleSelect = document.getElementById('profSelectVehicle');
    const warning = document.getElementById('profCapacityWarning');
    const submitBtn = document.getElementById('profSubmitAssignBtn');

    if (!vehicleSelect.value || !currentOrderData) {
        warning.classList.add('d-none');
        submitBtn.disabled = false;
        return;
    }

    const selectedOpt = vehicleSelect.options[vehicleSelect.selectedIndex];
    const capacity = parseFloat(selectedOpt.getAttribute('data-capacity') || 0);
    const orderWeight = parseFloat(currentOrderData.weight_kg || 0);

    if (orderWeight > 0 && capacity > 0 && orderWeight > capacity) {
        warning.innerHTML = `🚫 <strong>Capacity Validation Failed:</strong> Selected vehicle capacity (${capacity} kg) is insufficient for shipment weight (${orderWeight} kg).`;
        warning.classList.remove('d-none');
        submitBtn.disabled = true;
    } else {
        warning.classList.add('d-none');
        submitBtn.disabled = false;
    }
}

function toggleReassignForm() {
    const assignForm = document.getElementById('profAssignmentForm');
    const reassignReasonGroup = document.getElementById('profReassignReasonGroup');
    const submitBtn = document.getElementById('profSubmitAssignBtn');
    const isReassign = document.getElementById('profIsReassign');

    assignForm.classList.remove('d-none');
    reassignReasonGroup.classList.remove('d-none');
    document.getElementById('profReassignReason').required = true;
    isReassign.value = '1';
    submitBtn.textContent = 'Confirm Reassignment';
    submitBtn.className = 'btn btn-sm btn-warning px-4 fw-bold rounded-3';
}

function submitAssignmentForm(event) {
    event.preventDefault();

    const taskId = document.getElementById('profTaskId').value;
    const driverId = document.getElementById('profSelectDriver').value;
    const vehicleId = document.getElementById('profSelectVehicle').value;
    const isReassign = document.getElementById('profIsReassign').value === '1';
    const reassignReason = document.getElementById('profReassignReason').value;

    if (!driverId || !vehicleId) {
        alert('Please select both an eligible driver and an eligible vehicle.');
        return;
    }

    const endpoint = isReassign ? `/transport/delivery-orders/${taskId}/reassign` : `/transport/delivery-orders/${taskId}/assign`;
    const payload = {
        driver_id: driverId,
        vehicle_id: vehicleId,
        reassignment_reason: isReassign ? reassignReason : null,
    };

    const submitBtn = document.getElementById('profSubmitAssignBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing Assignment...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify(payload),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Assignment Failed: ' + (data.message || 'Validation error'));
            submitBtn.disabled = false;
            submitBtn.textContent = isReassign ? 'Confirm Reassignment' : 'Confirm Assignment';
        }
    })
    .catch(error => {
        console.error('Error submitting assignment:', error);
        alert('Assignment failed. Please check inputs and try again.');
        submitBtn.disabled = false;
        submitBtn.textContent = isReassign ? 'Confirm Reassignment' : 'Confirm Assignment';
    });
}
</script>
