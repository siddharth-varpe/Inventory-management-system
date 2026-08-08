<div class="card border-translucent shadow-sm rounded-4 bg-body mb-4">
    <!-- Header with Sub-Tab Nav Queues -->
    <div class="card-header border-bottom border-translucent bg-body-tertiary p-4 rounded-top-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h4 class="fw-black text-body mb-1">🚚 Delivery Orders Command Center</h4>
                <p class="text-muted small mb-0">Synchronized Sales Orders from CRM & Organize Stock Warehouse Fulfillment</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold">
                    Total Synced Orders: {{ $requests->total() }}
                </span>
            </div>
        </div>

        <!-- Sub-Tab Queue Filters (Phase 3 & Phase 4 Queues) -->
        <div class="nav nav-pills mt-4 border-bottom border-translucent pb-3 gap-2 flex-wrap">
            @php $currentQueue = request('queue', 'all'); @endphp
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'all', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'all' ? 'active bg-primary' : 'bg-body-secondary text-body' }}">
                All Synced Orders
            </a>
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'awaiting_warehouse', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'awaiting_warehouse' ? 'active bg-warning text-dark' : 'bg-body-secondary text-body' }}">
                ⏳ Queue 1: Awaiting Warehouse
            </a>
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'ready_for_assignment', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'ready_for_assignment' ? 'active bg-success' : 'bg-body-secondary text-body' }}">
                ✅ Queue 2: Ready for Assignment
            </a>
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'driver_vehicle_assigned', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'driver_vehicle_assigned' ? 'active bg-info text-white' : 'bg-body-secondary text-body' }}">
                🚛 Queue 3: Driver & Vehicle Assigned
            </a>
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'in_transit', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'in_transit' ? 'active bg-primary' : 'bg-body-secondary text-body' }}">
                🚀 Queue 4: Active / In Transit
            </a>
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'completed', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'completed' ? 'active bg-secondary' : 'bg-body-secondary text-body' }}">
                🎉 Queue 5: Completed
            </a>
            <a href="{{ route('transport.index', array_merge(request()->except(['page']), ['queue' => 'cancelled', 'tab' => 'delivery_orders'])) }}" 
               class="nav-link rounded-pill px-3 py-1.5 small fw-bold {{ $currentQueue === 'cancelled' ? 'active bg-danger text-white' : 'bg-body-secondary text-body' }}">
                🚫 Queue 6: Cancelled
            </a>
        </div>

        <!-- Filter Controls Bar -->
        <form method="GET" action="{{ route('transport.index') }}" class="row g-3 mt-2 align-items-center">
            <input type="hidden" name="tab" value="delivery_orders">
            <input type="hidden" name="queue" value="{{ $currentQueue }}">

            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-secondary border-translucent"><i class="fas me-1">🔍</i></span>
                    <input type="text" name="search" class="form-control bg-body border-translucent" 
                           placeholder="Search Order ID, Customer, City..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="priority" class="form-select form-select-sm bg-body border-translucent">
                    <option value="">-- Priority (All) --</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="city" class="form-select form-select-sm bg-body border-translucent">
                    <option value="">-- City (All) --</option>
                    @foreach($availableCities ?? [] as $c)
                        <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold rounded-3">Filter</button>
                <a href="{{ route('transport.index', ['tab' => 'delivery_orders']) }}" class="btn btn-sm btn-outline-secondary rounded-3">Reset</a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 border-translucent">
                <thead class="bg-body-tertiary text-muted small text-uppercase font-monospace border-bottom border-translucent">
                    <tr>
                        <th class="ps-4">Enterprise Order ID</th>
                        <th>Customer Name</th>
                        <th>Destination</th>
                        <th>Priority</th>
                        <th>Warehouse Readiness</th>
                        <th>Transport Status</th>
                        <th>Assignment Info</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <!-- Order ID -->
                            <td class="ps-4">
                                <div class="fw-bold font-monospace text-primary mb-0">{{ $req->order_reference }}</div>
                                <span class="small text-muted font-monospace" style="font-size: 0.75rem;">Task: {{ $req->request_number }}</span>
                            </td>

                            <!-- Customer -->
                            <td>
                                <div class="fw-bold text-body small">{{ $req->customer_name }}</div>
                                <div class="small text-muted">{{ $req->phone_number ?? 'No Phone' }}</div>
                            </td>

                            <!-- Destination -->
                            <td>
                                <div class="small fw-bold text-body">{{ $req->city }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 180px;" title="{{ $req->delivery_address }}">
                                    {{ $req->delivery_address }}
                                </div>
                            </td>

                            <!-- Priority -->
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1 {{ $req->priority_badge_class }}">
                                    {{ strtoupper($req->priority ?? 'NORMAL') }}
                                </span>
                            </td>

                            <!-- Warehouse Readiness -->
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1 {{ $req->warehouse_status_badge_class }}">
                                    {{ $req->warehouse_status_label }}
                                </span>
                                @if($req->warehouse_completed_at)
                                    <div class="small text-muted mt-1 font-monospace" style="font-size: 0.7rem;">
                                        Sealed: {{ $req->warehouse_completed_at->format('H:i, d M') }}
                                    </div>
                                @endif
                            </td>

                            <!-- Transport Status -->
                            <td>
                                <span class="badge rounded-pill px-3 py-1.5 fs-7 {{ $req->status_badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>

                            <!-- Assignment Info -->
                            <td>
                                @if($req->driver || $req->vehicle)
                                    <div class="small fw-bold text-body">👤 {{ $req->driver_name ?? $req->driver?->driver_name ?? 'Driver' }}</div>
                                    <div class="small text-muted">🚛 {{ $req->vehicle_number ?? $req->vehicle?->vehicle_number ?? 'Vehicle' }}</div>
                                @else
                                    <span class="small text-muted italic">Unassigned</span>
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold"
                                        onclick="openDeliveryOrderProfile({{ $req->id }})">
                                    👁 View Profile / Assign
                                </button>

                                @if($req->status === 'awaiting_warehouse')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 ms-1" style="font-size: 0.65rem;" title="Resource assignment locked until warehouse completes Pick & Pack">
                                        🔒 Locked
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="fs-2 mb-2">📦</div>
                                <h6 class="fw-bold">No Delivery Orders Found</h6>
                                <p class="small text-muted mb-0">Orders will automatically populate here when created in CRM Sales Orders.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Server-Side Pagination Links -->
        <div class="mt-4 d-flex justify-content-between align-items-center p-4">
            <div class="small text-muted">
                Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} delivery orders
            </div>
            <div>
                {{ $requests->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- DEDICATED DELIVERY ORDER PROFILE & ASSIGNMENT MODAL (#modalDeliveryOrderProfile) -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDeliveryOrderProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-primary font-monospace fs-5 px-3 py-1.5 rounded-pill" id="profOrderRef">SO-2026-000000</span>
                    <div>
                        <h5 class="modal-title fw-black text-body mb-0" id="profCustomerName">Customer Order Profile</h5>
                        <span class="small text-muted" id="profRequestNumber">Transport Task ID: TRN-2026-000000</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Status & Priority Banner -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                            <div class="small text-muted mb-1">Warehouse Readiness Status</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-3 py-1.5 fs-6" id="profWarehouseBadge">Picking & Packing</span>
                                <span class="small text-muted" id="profWarehouseTime">Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border border-translucent">
                            <div class="small text-muted mb-1">Transport Department Status</div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill px-3 py-1.5 fs-6" id="profTransportBadge">Awaiting Warehouse</span>
                                <span class="badge rounded-pill px-2.5 py-1" id="profPriorityBadge">NORMAL</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Details Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 bg-body rounded-3 border border-translucent h-100">
                            <h6 class="fw-bold text-body mb-3 border-bottom pb-2">📍 Destination & Contact Info</h6>
                            <div class="small mb-2"><strong>Customer:</strong> <span id="profCustomer">N/A</span></div>
                            <div class="small mb-2"><strong>Delivery Address:</strong> <span id="profAddress">N/A</span></div>
                            <div class="small mb-2"><strong>Destination City:</strong> <span id="profCity">N/A</span></div>
                            <div class="small mb-0"><strong>Contact Phone:</strong> <span id="profPhone">N/A</span></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-body rounded-3 border border-translucent h-100">
                            <h6 class="fw-bold text-body mb-3 border-bottom pb-2">📦 Package & Delivery Requirements</h6>
                            <div class="small mb-2"><strong>Required Delivery Date:</strong> <span id="profReqDate">N/A</span></div>
                            <div class="small mb-2"><strong>Package Count:</strong> <span id="profPkgCount">1 Cartons</span></div>
                            <div class="small mb-2"><strong>Total Weight:</strong> <span id="profWeight">2.50 kg</span></div>
                            <div class="small mb-0"><strong>Source Module:</strong> <span id="profSource">CRM Sales Order</span></div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================= -->
                <!-- PHASE 4 INTERACTIVE DRIVER & VEHICLE ASSIGNMENT SECTION -->
                <!-- ============================================================= -->
                <div class="card border-translucent rounded-3 mb-4 bg-body-tertiary" id="profAssignmentCard">
                    <div class="card-header bg-transparent border-bottom border-translucent d-flex align-items-center justify-content-between p-3">
                        <h6 class="fw-bold text-body mb-0">🚚 Fleet Resource Assignment</h6>
                        <span class="badge rounded-pill font-monospace px-2.5 py-1" id="profAssignmentStatusBadge">READY FOR ASSIGNMENT</span>
                    </div>
                    <div class="card-body p-3">
                        <!-- Alert Box for Lock/Errors -->
                        <div class="alert alert-warning border-warning-subtle small mb-3 d-none" id="profAssignmentLockAlert">
                            🔒 <strong>Assignment Locked:</strong> <span id="profLockReason">Resource assignment locked until Organize Stock completes Pick & Pack.</span>
                        </div>

                        <!-- Active Assignment Display (When Already Assigned) -->
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

                            <!-- Reassign Action Trigger -->
                            <button type="button" class="btn btn-sm btn-outline-warning w-100 fw-bold rounded-3" onclick="toggleReassignForm()">
                                🔄 Reassign Driver / Vehicle
                            </button>
                        </div>

                        <!-- Assign / Reassign Interactive Form -->
                        <form id="profAssignmentForm" onsubmit="submitAssignmentForm(event)" class="mt-2">
                            <input type="hidden" id="profTaskId" name="task_id" value="">
                            <input type="hidden" id="profIsReassign" value="0">

                            <!-- Reassign Reason Field (Hidden by default) -->
                            <div class="mb-3 d-none" id="profReassignReasonGroup">
                                <label class="form-label small fw-bold text-warning">Reassignment Reason <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm bg-body border-translucent" 
                                       id="profReassignReason" placeholder="Enter reason for driver/vehicle replacement...">
                            </div>

                            <div class="row g-3">
                                <!-- Driver Selection -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-body">Select Eligible Driver <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bg-body border-translucent" id="profSelectDriver" required>
                                        <option value="">-- Choose Eligible Driver --</option>
                                    </select>
                                    <div class="form-text small text-muted" id="profDriverHelp">Only active, available drivers with valid licenses are displayed.</div>
                                </div>

                                <!-- Vehicle Selection -->
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-body">Select Eligible Vehicle <span class="text-danger">*</span></label>
                                    <select class="form-select form-select-sm bg-body border-translucent" id="profSelectVehicle" onchange="validateVehicleCapacity()" required>
                                        <option value="">-- Choose Eligible Vehicle --</option>
                                    </select>
                                    <div class="form-text small text-muted" id="profVehicleHelp">Only available, operational vehicles are displayed.</div>
                                </div>
                            </div>

                            <!-- Live Capacity Indicator & Warning -->
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

                <!-- Universal Real Event Transport Timeline -->
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

<script>
let currentOrderData = null;

function openDeliveryOrderProfile(id) {
    fetch(`/transport/delivery-orders/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data || !data.id) {
                alert('Delivery order profile could not be loaded.');
                return;
            }

            currentOrderData = data;

            document.getElementById('profTaskId').value = data.id;
            document.getElementById('profOrderRef').textContent = data.order_reference;
            document.getElementById('profCustomerName').textContent = data.customer_name;
            document.getElementById('profRequestNumber').textContent = 'Transport Task ID: ' + data.request_number;

            document.getElementById('profCustomer').textContent = data.customer_name;
            document.getElementById('profAddress').textContent = data.delivery_address || 'Primary Customer Address';
            document.getElementById('profCity').textContent = data.delivery_city || 'Mumbai';
            document.getElementById('profPhone').textContent = data.phone_number || 'N/A';

            document.getElementById('profReqDate').textContent = data.expected_delivery_date || 'N/A';
            document.getElementById('profPkgCount').textContent = (data.package_count || 1) + ' Carton(s)';
            document.getElementById('profWeight').textContent = (data.weight_kg || '2.50') + ' kg';
            document.getElementById('profSource').textContent = data.source_module || 'CRM Sales Order';

            // Badges
            const wBadge = document.getElementById('profWarehouseBadge');
            wBadge.textContent = data.warehouse_status_label;
            wBadge.className = 'badge rounded-pill px-3 py-1.5 fs-6 ' + data.warehouse_status_badge_class;

            document.getElementById('profWarehouseTime').textContent = data.warehouse_completed_at ? 'Sealed: ' + data.warehouse_completed_at : 'In Progress';

            const tBadge = document.getElementById('profTransportBadge');
            tBadge.textContent = data.status_label;
            tBadge.className = 'badge rounded-pill px-3 py-1.5 fs-6 ' + data.status_badge_class;

            const pBadge = document.getElementById('profPriorityBadge');
            pBadge.textContent = (data.priority || 'NORMAL').toUpperCase();
            pBadge.className = 'badge rounded-pill px-2.5 py-1 ' + data.priority_badge_class;

            // Phase 4 Assignment Section Rendering Logic
            const lockAlert = document.getElementById('profAssignmentLockAlert');
            const activeContainer = document.getElementById('profActiveAssignmentContainer');
            const assignForm = document.getElementById('profAssignmentForm');
            const reassignReasonGroup = document.getElementById('profReassignReasonGroup');
            const capacityWarning = document.getElementById('profCapacityWarning');
            const submitBtn = document.getElementById('profSubmitAssignBtn');
            const statusBadge = document.getElementById('profAssignmentStatusBadge');

            lockAlert.classList.add('d-none');
            activeContainer.classList.add('d-none');
            assignForm.classList.remove('d-none');
            reassignReasonGroup.classList.add('d-none');
            capacityWarning.classList.add('d-none');
            document.getElementById('profIsReassign').value = '0';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirm Assignment';

            statusBadge.textContent = (data.status_label || 'READY FOR ASSIGNMENT').toUpperCase();
            statusBadge.className = 'badge rounded-pill font-monospace px-2.5 py-1 ' + data.status_badge_class;

            // Populate Driver Dropdown
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

            // Populate Vehicle Dropdown
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
                assignForm.classList.add('d-none'); // Hide initial form, toggleable via reassign

                document.getElementById('profAssignmentNumber').textContent = data.active_assignment.assignment_number;
                document.getElementById('profAssignedTime').textContent = 'Assigned: ' + data.active_assignment.assigned_at;
                document.getElementById('profAssignedDriverName').textContent = data.active_assignment.driver_name || 'N/A';
                document.getElementById('profAssignedDriverPhone').textContent = data.active_assignment.driver_phone || 'N/A';
                document.getElementById('profAssignedVehicleReg').textContent = data.active_assignment.vehicle_number || 'N/A';
                document.getElementById('profAssignedVehicleType').textContent = (data.active_assignment.vehicle_type || 'Vehicle') + ' (Assigned)';
                document.getElementById('profAssignedByName').textContent = data.active_assignment.assigned_by_name || 'Transport Manager';
            }

            // Render Timeline
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
    const isReassign = document.getElementById('profIsReassign').value === '1';
    const driverId = document.getElementById('profSelectDriver').value;
    const vehicleId = document.getElementById('profSelectVehicle').value;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let url = `/transport/delivery-orders/${taskId}/assign`;
    let payload = {
        driver_id: driverId,
        vehicle_id: vehicleId,
    };

    if (isReassign) {
        url = `/transport/delivery-orders/${taskId}/reassign`;
        payload = {
            new_driver_id: driverId,
            new_vehicle_id: vehicleId,
            reassignment_reason: document.getElementById('profReassignReason').value,
        };
    }

    fetch(url, {
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
            alert('Assignment Failed: ' + (data.message || 'Validation Error'));
        }
    })
    .catch(error => {
        console.error('Assignment request error:', error);
        alert('An unexpected error occurred during assignment.');
    });
}
</script>
