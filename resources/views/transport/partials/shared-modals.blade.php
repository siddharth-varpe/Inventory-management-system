<!-- ========================================================================= -->
<!-- SHARED TRANSPORT MODALS: DELIVERY PROFILE & CANCEL DISPATCH -->
<!-- ========================================================================= -->

<!-- DEDICATED DELIVERY ORDER PROFILE & ASSIGNMENT MODAL -->
<div class="modal fade" id="modalDeliveryOrderProfile" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-3 bg-body-tertiary rounded-top-4">
                <div class="d-flex align-items-center gap-3">
                    <span class="fs-4">📦</span>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-black text-body mb-0" id="profOrderReference">Loading...</h5>
                            <span class="badge bg-primary font-monospace px-2.5 py-1" id="profRequestNumber">TRN-000000</span>
                            <span class="badge rounded-pill" id="profPriorityBadge">NORMAL</span>
                        </div>
                        <div class="small text-muted" id="profCustomerName">Customer Information</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- LOADING SPINNER CONTAINER -->
                <div id="profModalLoadingSpinner" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <h6 class="fw-bold text-body">Loading Delivery Order Profile...</h6>
                    <p class="text-muted small mb-0">Fetching real-time dispatch, fulfillment & resource assignment data...</p>
                </div>

                <!-- MAIN MODAL CONTENT AREA -->
                <div id="profModalContentArea" class="d-none">
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

                                <button type="button" class="btn btn-sm btn-outline-warning w-100 fw-bold rounded-3" id="btnToggleReassign" onclick="toggleReassignForm()">
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

                    <!-- DISPATCH STATUS & TRACKING SECTION -->
                    <div class="card border-primary-subtle rounded-3 mb-4 bg-body-tertiary d-none" id="profDispatchCard">
                        <div class="card-header bg-primary-subtle border-bottom border-primary-subtle d-flex align-items-center justify-content-between p-3">
                            <h6 class="fw-bold text-primary mb-0">🚛 Transport Delivery Status</h6>
                            <span class="badge bg-primary text-white font-monospace px-2.5 py-1" id="profDispatchBadge">ASSIGNED</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="alert alert-warning border-warning-subtle small mb-0 d-none" id="profDispatchBlockedAlert">
                                ⚠️ <strong>Assignment Required:</strong> <span id="profDispatchBlockedReason">Warehouse fulfillment or resource assignment incomplete.</span>
                            </div>

                            <div class="alert alert-info border-info-subtle small mb-0 d-none" id="profAssignedPendingBanner">
                                ℹ️ <strong>Driver & Vehicle Assigned:</strong> Awaiting delivery acceptance by assigned driver on <strong>Driver Terminal</strong>.
                            </div>

                            <div class="alert alert-success border-success-subtle small mb-0 d-none" id="profDispatchedBanner">
                                🚀 <strong>Shipment Dispatched / In Transit:</strong> Released under Dispatch ID <strong id="profDispatchedNumber">DSP-2026-000001</strong> on <span id="profDispatchedTime">N/A</span>.
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
            </div>

            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Close Profile</button>
            </div>
        </div>
    </div>
</div>

<!-- CANCEL DISPATCH MODAL (#modalCancelDispatch) -->
<div class="modal fade" id="modalCancelDispatch" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-lg border-translucent bg-body">
            <div class="modal-header border-bottom border-translucent p-3 bg-danger-subtle text-danger rounded-top-4">
                <h5 class="modal-title fw-black mb-0">🚫 Cancel Active Dispatch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-body mb-3">You are about to cancel active dispatch for shipment <strong id="cancelDispatchOrderRef" class="text-primary font-monospace">SO-000000</strong> (<span id="cancelDispatchNo" class="font-monospace">DSP-000000</span>).</p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-body">Cancellation Reason <span class="text-danger">*</span></label>
                    <textarea class="form-control form-control-sm bg-body border-translucent" id="cancelDispatchReason" rows="3" placeholder="Enter reason for dispatch cancellation (e.g., breakdown, accident, route closure, customer recall)..." required></textarea>
                    <div class="form-text small text-muted">Minimum 3 characters required for audit trail.</div>
                </div>
                <div class="alert alert-warning small mb-0">
                    ⚠️ <strong>Audit Warning:</strong> This action will mark order status as CANCELLED, release driver & vehicle to fleet (if no other active trips), and record an immutable audit log.
                </div>
            </div>
            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 px-4 fw-bold" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger rounded-3 px-4 fw-bold shadow-sm" id="btnConfirmCancelDispatch" onclick="executeCancelDispatch()">
                    Confirm Cancellation
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentOrderData = null;
let cancelTargetTaskId = null;

function refreshDeliveryOrders() {
    window.location.reload();
}

function openDeliveryOrderProfile(id, btnElement) {
    if (!id) {
        alert('Invalid delivery selection.');
        return;
    }

    const spinner = document.getElementById('profModalLoadingSpinner');
    const content = document.getElementById('profModalContentArea');
    
    if (spinner) spinner.classList.remove('d-none');
    if (content) content.classList.add('d-none');
    
    document.getElementById('profOrderReference').textContent = 'Loading Profile...';
    document.getElementById('profRequestNumber').textContent = `TRN-#${id}`;

    // INSTANTLY SHOW BOOTSTRAP MODAL ON CLICK
    const modalEl = document.getElementById('modalDeliveryOrderProfile');
    if (modalEl) {
        let bsModal = null;
        if (window.bootstrap && window.bootstrap.Modal) {
            bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        }
        
        if (bsModal) {
            bsModal.show();
        } else if (window.jQuery) {
            window.jQuery(modalEl).modal('show');
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }

    // FETCH DELIVER ORDER DATA IN BACKGROUND
    fetch(`/transport/delivery-orders/${id}`, {
        headers: { 'Accept': 'application/json' }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('HTTP error ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            currentOrderData = data;

            document.getElementById('profTaskId').value = data.id;
            document.getElementById('profIsReassign').value = '0';
            document.getElementById('profOrderReference').textContent = data.order_reference || 'N/A';
            document.getElementById('profRequestNumber').textContent = data.request_number || 'N/A';
            document.getElementById('profCustomerName').textContent = data.customer_name || 'N/A';
            document.getElementById('profCity').textContent = data.delivery_city || 'N/A';
            document.getElementById('profAddress').textContent = data.delivery_address || 'No specific address provided';
            document.getElementById('profPackages').textContent = (data.package_count || 1) + ' Cartons';
            document.getElementById('profWeight').textContent = `${data.weight_kg || 0} kg | ${data.volume_m3 || 0} m³`;
            document.getElementById('profExpectedDate').textContent = data.expected_delivery_date || 'N/A';
            document.getElementById('profSourceModule').textContent = data.source_module || 'CRM Sales Order';

            const priorityBadge = document.getElementById('profPriorityBadge');
            priorityBadge.className = `badge rounded-pill ${data.priority_badge_class || 'bg-secondary'}`;
            priorityBadge.textContent = (data.priority || 'normal').toUpperCase();

            const warehouseBadge = document.getElementById('profWarehouseBadge');
            warehouseBadge.className = `badge rounded-pill ${data.warehouse_status_badge_class || 'bg-secondary'}`;
            warehouseBadge.textContent = data.warehouse_status_label || 'In Progress';
            document.getElementById('profWarehouseTime').textContent = data.warehouse_completed_at ? `Completed: ${data.warehouse_completed_at}` : 'Fulfillment In Progress';

            const assignStatusBadge = document.getElementById('profAssignmentStatusBadge');
            assignStatusBadge.className = `badge rounded-pill font-monospace ${data.status_badge_class || 'bg-secondary'}`;
            assignStatusBadge.textContent = data.status_label || 'READY FOR ASSIGNMENT';

            const lockAlert = document.getElementById('profAssignmentLockAlert');
            const assignForm = document.getElementById('profAssignmentForm');
            const activeContainer = document.getElementById('profActiveAssignmentContainer');
            const reassignReasonGroup = document.getElementById('profReassignReasonGroup');
            const submitBtn = document.getElementById('profSubmitAssignBtn');
            const reassignBtn = document.getElementById('btnToggleReassign');

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
                    opt.textContent = `👤 ${drv.driver_name} (${drv.employee_id || drv.driver_code}) — ${drv.license_class || 'LMN'}`;
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

            const isDispatchedOrBeyond = ['dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'completed'].includes(data.status);

            if (isDispatchedOrBeyond) {
                // DISPATCHED LOCK: Assignment is strictly locked
                assignForm.classList.add('d-none');
                if (reassignBtn) reassignBtn.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Driver and vehicle assignment is locked because this delivery has been dispatched.';

                if (data.active_assignment || data.driver || data.vehicle) {
                    activeContainer.classList.remove('d-none');
                    document.getElementById('profAssignmentNumber').textContent = data.active_assignment?.assignment_number || 'ASN-LOCKED';
                    document.getElementById('profAssignedTime').textContent = data.active_assignment?.assigned_at ? ('Assigned: ' + data.active_assignment.assigned_at) : (data.dispatched_at ? 'Dispatched: ' + data.dispatched_at : '');
                    document.getElementById('profAssignedDriverName').textContent = data.driver?.driver_name || data.active_assignment?.driver_name || 'N/A';
                    document.getElementById('profAssignedDriverPhone').textContent = data.driver?.phone_number || data.active_assignment?.driver_phone || 'N/A';
                    document.getElementById('profAssignedVehicleReg').textContent = data.vehicle?.vehicle_number || data.active_assignment?.vehicle_number || 'N/A';
                    document.getElementById('profAssignedVehicleType').textContent = (data.vehicle?.vehicle_type || data.active_assignment?.vehicle_type || 'Vehicle') + ' (Locked)';
                    document.getElementById('profAssignedByName').textContent = data.active_assignment?.assigned_by_name || 'Transport Manager';
                }
            } else if (data.status === 'awaiting_warehouse') {
                assignForm.classList.add('d-none');
                if (reassignBtn) reassignBtn.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Resource assignment locked until Organize Stock completes Pick & Pack and seals the shipment.';
            } else if (data.status === 'cancelled') {
                assignForm.classList.add('d-none');
                if (reassignBtn) reassignBtn.classList.add('d-none');
                lockAlert.classList.remove('d-none');
                document.getElementById('profLockReason').textContent = 'Order is cancelled. Resource assignment is unavailable.';
            } else if (data.active_assignment || data.driver || data.vehicle) {
                activeContainer.classList.remove('d-none');
                assignForm.classList.add('d-none');
                if (reassignBtn) reassignBtn.classList.remove('d-none');

                document.getElementById('profAssignmentNumber').textContent = data.active_assignment?.assignment_number || 'ASN-ACTIVE';
                document.getElementById('profAssignedTime').textContent = data.active_assignment?.assigned_at ? ('Assigned: ' + data.active_assignment.assigned_at) : '';
                document.getElementById('profAssignedDriverName').textContent = data.driver?.driver_name || data.active_assignment?.driver_name || 'N/A';
                document.getElementById('profAssignedDriverPhone').textContent = data.driver?.phone_number || data.active_assignment?.driver_phone || 'N/A';
                document.getElementById('profAssignedVehicleReg').textContent = data.vehicle?.vehicle_number || data.active_assignment?.vehicle_number || 'N/A';
                document.getElementById('profAssignedVehicleType').textContent = (data.vehicle?.vehicle_type || data.active_assignment?.vehicle_type || 'Vehicle') + ' (Assigned)';
                document.getElementById('profAssignedByName').textContent = data.active_assignment?.assigned_by_name || 'Transport Manager';
            }

            const dispatchCard = document.getElementById('profDispatchCard');
            const dispatchBlockedAlert = document.getElementById('profDispatchBlockedAlert');
            const assignedPendingBanner = document.getElementById('profAssignedPendingBanner');
            const dispatchedBanner = document.getElementById('profDispatchedBanner');

            if (dispatchCard) {
                dispatchCard.classList.remove('d-none');
                dispatchBlockedAlert.classList.add('d-none');
                if (assignedPendingBanner) assignedPendingBanner.classList.add('d-none');
                dispatchedBanner.classList.add('d-none');

                if (isDispatchedOrBeyond) {
                    dispatchedBanner.classList.remove('d-none');
                    document.getElementById('profDispatchedNumber').textContent = data.dispatch_number || 'DSP-ACTIVE';
                    document.getElementById('profDispatchedTime').textContent = data.dispatched_at || 'Dispatched';
                    document.getElementById('profDispatchBadge').textContent = 'DISPATCHED';
                } else if (data.status === 'driver_vehicle_assigned' || data.status === 'assigned' || data.active_assignment) {
                    if (assignedPendingBanner) assignedPendingBanner.classList.remove('d-none');
                    document.getElementById('profDispatchBadge').textContent = 'ASSIGNED';
                } else {
                    dispatchBlockedAlert.classList.remove('d-none');
                    const elig = data.dispatch_eligibility || { eligible: false, reason: 'Resource assignment incomplete.' };
                    document.getElementById('profDispatchBlockedReason').textContent = elig.reason || 'Select and confirm driver + vehicle assignment.';
                    document.getElementById('profDispatchBadge').textContent = 'READY FOR ASSIGNMENT';
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

            // HIDE SPINNER AND SHOW CONTENT
            if (spinner) spinner.classList.add('d-none');
            if (content) content.classList.remove('d-none');
        })
        .catch(error => {
            console.error('Error fetching delivery order profile details:', error);
            if (spinner) {
                spinner.innerHTML = `
                    <div class="alert alert-danger border-danger-subtle small my-4">
                        ❌ <strong>Unable to load profile details:</strong> ${error.message || 'Network error'}.
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-3" onclick="openDeliveryOrderProfile(${id})">
                                🔄 Retry Loading
                            </button>
                        </div>
                    </div>
                `;
            }
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
    if (currentOrderData && ['dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'completed'].includes(currentOrderData.status)) {
        alert('Reassignment is not allowed because this delivery has been dispatched.');
        return;
    }

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

    if (currentOrderData && ['dispatched', 'in_transit', 'out_for_delivery', 'delivered', 'completed'].includes(currentOrderData.status)) {
        alert('Reassignment is not allowed because this delivery has been dispatched.');
        return;
    }

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
        new_driver_id: driverId,
        new_vehicle_id: vehicleId,
        reassignment_reason: isReassign ? (reassignReason || 'Reassigned by Transport Manager') : null,
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

function openCancelDispatchModal(taskId, orderRef, dispatchNo) {
    cancelTargetTaskId = taskId;
    document.getElementById('cancelDispatchOrderRef').textContent = orderRef || 'N/A';
    document.getElementById('cancelDispatchNo').textContent = dispatchNo || 'DSP-ACTIVE';
    document.getElementById('cancelDispatchReason').value = '';

    const modalEl = document.getElementById('modalCancelDispatch');
    if (modalEl) {
        let bsModal = null;
        if (window.bootstrap && window.bootstrap.Modal) {
            bsModal = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
        }

        if (bsModal) {
            bsModal.show();
        } else if (window.jQuery) {
            window.jQuery(modalEl).modal('show');
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
            document.body.classList.add('modal-open');
        }
    }
}

function executeCancelDispatch() {
    if (!cancelTargetTaskId) return;

    const reason = document.getElementById('cancelDispatchReason').value.trim();
    if (!reason || reason.length < 3) {
        alert('Please enter a valid cancellation reason (minimum 3 characters).');
        return;
    }

    const confirmBtn = document.getElementById('btnConfirmCancelDispatch');
    confirmBtn.disabled = true;
    confirmBtn.textContent = 'Cancelling Dispatch...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    fetch(`/transport/delivery-orders/${cancelTargetTaskId}/cancel-dispatch`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ cancellation_reason: reason }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Cancellation Failed: ' + (data.message || 'Validation error'));
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Cancellation';
        }
    })
    .catch(error => {
        console.error('Error cancelling dispatch:', error);
        alert('Cancellation failed. Please check network/inputs and try again.');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm Cancellation';
    });
}
</script>
