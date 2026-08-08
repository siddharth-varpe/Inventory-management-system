<div class="col-12">
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
        <!-- Top Title & Queue Stats Header -->
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-primary font-monospace fs-7 px-3 py-1 rounded-pill">PHASE 3 — DELIVERY ORDERS & WAREHOUSE SYNC</span>
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-1 font-monospace fs-7">
                        ⚡ Realtime Sales & Warehouse Event Sync
                    </span>
                </div>
                <h4 class="fw-black text-body mb-0">Delivery Orders Command Center</h4>
                <p class="text-muted small mb-0">Enterprise order lifecycle synchronization between CRM Sales, Organize Stock Warehouse, and Transport Logistics.</p>
            </div>

            <!-- Queue Counter Badges Strip -->
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'awaiting_warehouse']) }}" class="p-2 px-3 rounded-3 text-decoration-none border border-translucent text-center {{ request('queue', 'awaiting_warehouse') === 'awaiting_warehouse' ? 'bg-warning-subtle text-warning-emphasis fw-bold shadow-sm' : 'bg-body-tertiary text-muted' }}">
                    <div class="small" style="font-size: 0.7rem;">1. Awaiting Warehouse</div>
                    <div class="fs-6 font-monospace">⏳ {{ $requests->where('status', 'awaiting_warehouse')->count() }} Orders</div>
                </a>
                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'ready_for_assignment']) }}" class="p-2 px-3 rounded-3 text-decoration-none border border-translucent text-center {{ request('queue') === 'ready_for_assignment' ? 'bg-success-subtle text-success fw-bold shadow-sm' : 'bg-body-tertiary text-muted' }}">
                    <div class="small" style="font-size: 0.7rem;">2. Ready for Assignment</div>
                    <div class="fs-6 font-monospace">✅ {{ $requests->whereIn('status', ['ready_for_assignment', 'waiting_planning', 'planning_completed'])->count() }} Orders</div>
                </a>
                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'in_transit']) }}" class="p-2 px-3 rounded-3 text-decoration-none border border-translucent text-center {{ request('queue') === 'in_transit' ? 'bg-primary-subtle text-primary fw-bold shadow-sm' : 'bg-body-tertiary text-muted' }}">
                    <div class="small" style="font-size: 0.7rem;">3. Active / In Transit</div>
                    <div class="fs-6 font-monospace">🚀 {{ $requests->whereIn('status', ['in_transit', 'dispatched', 'out_for_delivery'])->count() }} Orders</div>
                </a>
                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'completed']) }}" class="p-2 px-3 rounded-3 text-decoration-none border border-translucent text-center {{ request('queue') === 'completed' ? 'bg-secondary-subtle text-secondary fw-bold shadow-sm' : 'bg-body-tertiary text-muted' }}">
                    <div class="small" style="font-size: 0.7rem;">4. Completed</div>
                    <div class="fs-6 font-monospace">🎉 {{ $requests->whereIn('status', ['delivered', 'completed'])->count() }} Orders</div>
                </a>
                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'cancelled']) }}" class="p-2 px-3 rounded-3 text-decoration-none border border-translucent text-center {{ request('queue') === 'cancelled' ? 'bg-danger-subtle text-danger fw-bold shadow-sm' : 'bg-body-tertiary text-muted' }}">
                    <div class="small" style="font-size: 0.7rem;">5. Cancelled</div>
                    <div class="fs-6 font-monospace">❌ {{ $requests->where('status', 'cancelled')->count() }} Orders</div>
                </a>
            </div>
        </div>

        <!-- Search & Filter Controls -->
        <form method="GET" action="{{ route('transport.index') }}" class="row g-3 mb-4 align-items-center">
            <input type="hidden" name="tab" value="delivery-orders">
            <input type="hidden" name="queue" value="{{ request('queue', 'awaiting_warehouse') }}">

            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-body-tertiary border-translucent text-muted">🔍</span>
                    <input type="text" name="search" class="form-select border-translucent text-body" placeholder="Search Order ID (SO-2026-X), Customer, City..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3 col-lg-2">
                <select name="priority" class="form-select border-translucent text-body" onchange="this.form.submit()">
                    <option value="" {{ !request('priority') ? 'selected' : '' }}>-- All Priorities --</option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent Priority</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High Priority</option>
                    <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal Priority</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low Priority</option>
                </select>
            </div>

            <div class="col-md-3 col-lg-2">
                <select name="city" class="form-select border-translucent text-body" onchange="this.form.submit()">
                    <option value="" {{ !request('city') ? 'selected' : '' }}>-- All Destinations --</option>
                    @foreach($availableCities as $c)
                        <option value="{{ $c }}" {{ request('city') === $c ? 'selected' : '' }}>📍 {{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 col-lg-2 text-end">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-bold">Filter Orders</button>
            </div>
        </form>

        <!-- Queue Sub-Tab Navigation Bar -->
        <div class="nav nav-pills gap-2 border-bottom border-translucent pb-3 mb-4">
            <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'awaiting_warehouse', 'search' => request('search'), 'priority' => request('priority'), 'city' => request('city')]) }}" class="nav-link rounded-pill px-3.5 py-2 fw-bold {{ request('queue', 'awaiting_warehouse') === 'awaiting_warehouse' ? 'active bg-warning text-dark' : 'bg-body-tertiary text-body border border-translucent' }}">
                ⏳ Queue 1: Awaiting Warehouse
            </a>
            <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'ready_for_assignment', 'search' => request('search'), 'priority' => request('priority'), 'city' => request('city')]) }}" class="nav-link rounded-pill px-3.5 py-2 fw-bold {{ request('queue') === 'ready_for_assignment' ? 'active bg-success text-white' : 'bg-body-tertiary text-body border border-translucent' }}">
                ✅ Queue 2: Ready for Assignment
            </a>
            <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'in_transit', 'search' => request('search'), 'priority' => request('priority'), 'city' => request('city')]) }}" class="nav-link rounded-pill px-3.5 py-2 fw-bold {{ request('queue') === 'in_transit' ? 'active bg-primary text-white' : 'bg-body-tertiary text-body border border-translucent' }}">
                🚀 Queue 3: Active / In Transit
            </a>
            <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'completed', 'search' => request('search'), 'priority' => request('priority'), 'city' => request('city')]) }}" class="nav-link rounded-pill px-3.5 py-2 fw-bold {{ request('queue') === 'completed' ? 'active bg-secondary text-white' : 'bg-body-tertiary text-body border border-translucent' }}">
                📜 Queue 4: Completed
            </a>
            <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'queue' => 'cancelled', 'search' => request('search'), 'priority' => request('priority'), 'city' => request('city')]) }}" class="nav-link rounded-pill px-3.5 py-2 fw-bold {{ request('queue') === 'cancelled' ? 'active bg-danger text-white' : 'bg-body-tertiary text-body border border-translucent' }}">
                ❌ Queue 5: Cancelled
            </a>
        </div>

        <!-- Delivery Orders Data Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle border-translucent mb-0" id="tableDeliveryOrders">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th class="py-3 px-3 text-body">Enterprise Order ID</th>
                        <th class="py-3 px-3 text-body">Customer</th>
                        <th class="py-3 px-3 text-body">Destination</th>
                        <th class="py-3 px-3 text-body">Priority</th>
                        <th class="py-3 px-3 text-body">Required Date</th>
                        <th class="py-3 px-3 text-body">Warehouse Status</th>
                        <th class="py-3 px-3 text-body">Transport Status</th>
                        <th class="py-3 px-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="px-3">
                                <div class="fw-bold font-monospace text-primary fs-6">{{ $req->order_reference }}</div>
                                <div class="small text-muted font-monospace">{{ $req->request_number }}</div>
                            </td>
                            <td class="px-3">
                                <div class="fw-bold text-body">{{ $req->customer_name }}</div>
                                <div class="small text-muted">Contact: {{ $req->phone_number ?? 'N/A' }}</div>
                            </td>
                            <td class="px-3">
                                <div class="fw-semibold text-body">📍 {{ $req->city }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 180px;">{{ $req->delivery_address }}</div>
                            </td>
                            <td class="px-3">
                                <span class="badge {{ $req->priority_badge_class }} rounded-pill px-2.5 py-1">
                                    {{ strtoupper($req->priority ?? 'NORMAL') }}
                                </span>
                            </td>
                            <td class="px-3">
                                <div class="fw-semibold text-body">{{ $req->expected_delivery_date ? $req->expected_delivery_date->format('d M Y') : 'N/A' }}</div>
                                <div class="small text-muted">{{ $req->created_at->format('H:i, d M') }}</div>
                            </td>
                            <td class="px-3">
                                <span class="badge {{ $req->warehouse_status_badge_class }} rounded-pill px-3 py-1 fs-7">
                                    {{ $req->warehouse_status_label }}
                                </span>
                                @if($req->warehouse_completed_at)
                                    <div class="small text-muted mt-0.5">Sealed: {{ $req->warehouse_completed_at->format('H:i, d M') }}</div>
                                @endif
                            </td>
                            <td class="px-3">
                                <span class="badge {{ $req->status_badge_class }} rounded-pill px-3 py-1 fs-7">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="px-3 text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="openDeliveryOrderProfile({{ $req->id }})">
                                    👁 View Order
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
        <div class="mt-4 d-flex justify-content-between align-items-center">
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
<!-- DEDICATED DELIVERY ORDER PROFILE MODAL (#modalDeliveryOrderProfile) -->
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
                            <div class="small mb-2"><strong>Total Weight:</strong> <span id="profWeight">2.5 kg</span></div>
                            <div class="small mb-0"><strong>Source Module:</strong> <span id="profSource">CRM Sales Order</span></div>
                        </div>
                    </div>
                </div>

                <!-- Phase 4 Future Assignment Placeholders (Disabled) -->
                <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-body small">🚛 Resource Assignment (Phase 4 Functionality)</span>
                        <span class="badge bg-secondary text-white font-monospace" style="font-size: 0.65rem;">LOCKED IN PHASE 3</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 rounded-3 text-start" disabled>
                                👤 Driver: <span id="profDriverName">Unassigned</span>
                            </button>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-100 rounded-3 text-start" disabled>
                                🚛 Vehicle: <span id="profVehicleNumber">Unassigned</span>
                            </button>
                        </div>
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
function openDeliveryOrderProfile(id) {
    fetch(`/transport/delivery-orders/${id}`)
        .then(response => response.json())
        .then(data => {
            if (!data || !data.id) {
                alert('Delivery order profile could not be loaded.');
                return;
            }

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

            document.getElementById('profDriverName').textContent = data.driver_name || 'Unassigned';
            document.getElementById('profVehicleNumber').textContent = data.vehicle_number || 'Unassigned';

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
</script>
