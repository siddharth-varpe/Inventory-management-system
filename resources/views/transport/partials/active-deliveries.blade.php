<div class="col-12">
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-black text-body mb-0">🚛 Active Transport Deliveries</h4>
                <p class="text-muted small mb-0 mt-1">Live operational tracking of active shipments currently dispatched or in transit.</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-bold">
                    {{ $requests->total() }} Dispatched Deliveries
                </span>
            </div>
        </div>

        <!-- Search Bar & Filters -->
        <form method="GET" action="{{ route('transport.index') }}" class="row g-2 mb-4">
            <input type="hidden" name="tab" value="active">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-translucent text-muted">🔍</span>
                    <input type="text" name="search" class="form-control bg-body border-translucent" 
                           placeholder="Search by Dispatch ID, Order ID, Driver, Vehicle, City..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="priority" class="form-select form-select-sm bg-body border-translucent" onchange="this.form.submit()">
                    <option value="all">All Priorities</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>🚨 Urgent</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>🔥 High</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>📦 Normal</option>
                </select>
            </div>
            <div class="col-md-4 text-end">
                <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold rounded-3">Filter Active</button>
                @if(request('search') || request('priority'))
                    <a href="{{ route('transport.index', ['tab' => 'active']) }}" class="btn btn-sm btn-outline-secondary px-3 rounded-3">Reset</a>
                @endif
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted font-monospace small">
                        <th>DISPATCH ID</th>
                        <th>ORDER ID</th>
                        <th>CUSTOMER</th>
                        <th>DESTINATION</th>
                        <th>DRIVER</th>
                        <th>VEHICLE</th>
                        <th>DISPATCH TIME</th>
                        <th>PRIORITY</th>
                        <th>STATUS</th>
                        <th>EXPECTED DELIVERY</th>
                        <th class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="fw-bold font-monospace text-primary">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2 py-1">
                                    {{ $req->dispatch_number ?? 'DSP-PENDING' }}
                                </span>
                            </td>
                            <td class="fw-bold font-monospace text-body">
                                <a href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $req->id }})" class="text-decoration-none text-body">
                                    {{ $req->order_reference }}
                                </a>
                            </td>
                            <td class="fw-bold text-body">{{ $req->customer_name }}</td>
                            <td class="text-muted small">
                                📍 {{ $req->city }}
                            </td>
                            <td>
                                @if($req->driver)
                                    <div class="fw-semibold text-body small">👤 {{ $req->driver->driver_name }}</div>
                                    <div class="text-muted font-monospace" style="font-size:0.75rem;">{{ $req->driver->driver_code }}</div>
                                @else
                                    <span class="text-muted small">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($req->vehicle)
                                    <div class="fw-bold text-body font-monospace small">🚛 {{ $req->vehicle->vehicle_number }}</div>
                                    <div class="text-muted" style="font-size:0.75rem;">{{ $req->vehicle->vehicle_type }}</div>
                                @else
                                    <span class="text-muted small">Unassigned</span>
                                @endif
                            </td>
                            <td class="small text-muted font-monospace">
                                {{ $req->dispatched_at ? $req->dispatched_at->format('d M, H:i') : 'N/A' }}
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $req->priority_badge_class }}">
                                    {{ ucfirst($req->priority ?? 'normal') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $req->status_badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="small text-muted font-monospace">
                                {{ $req->expected_delivery_date ? $req->expected_delivery_date->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-primary rounded-pill px-3 fw-bold" onclick="openDeliveryOrderProfile({{ $req->id }})">
                                        View Profile
                                    </button>
                                    <button type="button" class="btn btn-outline-danger rounded-pill px-2" title="Cancel Dispatch" onclick="openCancelDispatchModal({{ $req->id }}, '{{ $req->order_reference }}', '{{ $req->dispatch_number }}')">
                                        🚫
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center text-muted py-5">
                                <div class="fs-4 mb-2">🚚</div>
                                <div>No active operational deliveries in transit.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="mt-4">
                {{ $requests->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>

<!-- DISPATCH CANCELLATION MODAL (#modalCancelDispatch) -->
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
let cancelTargetTaskId = null;

function openCancelDispatchModal(taskId, orderRef, dispatchNo) {
    cancelTargetTaskId = taskId;
    document.getElementById('cancelDispatchOrderRef').textContent = orderRef;
    document.getElementById('cancelDispatchNo').textContent = dispatchNo || 'DSP-ACTIVE';
    document.getElementById('cancelDispatchReason').value = '';
    const modal = new bootstrap.Modal(document.getElementById('modalCancelDispatch'));
    modal.show();
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
            alert('Cancellation Failed: ' + (data.message || 'Error occurred'));
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Confirm Cancellation';
        }
    })
    .catch(error => {
        console.error('Cancel dispatch error:', error);
        alert('Cancellation could not be completed. Please refresh and try again.');
        confirmBtn.disabled = false;
        confirmBtn.textContent = 'Confirm Cancellation';
    });
}
</script>
