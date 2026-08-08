@props([
    'pickingTask' => null,
    'transportRequest' => null,
    'orderRef' => 'N/A',
    'liveStatusUrl' => null,
])

<!-- ======================================================================= -->
<!-- 1. WAREHOUSE EXECUTION READ-ONLY TRACKING MODAL (#warehouseStatusModal) -->
<!-- ======================================================================= -->
<div class="modal fade" id="warehouseStatusModal" tabindex="-1" aria-labelledby="warehouseStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow-lg rounded-4">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-translucent p-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-primary-subtle text-primary rounded-3">
                        <span class="fs-4">📦</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="warehouseStatusModalLabel">Warehouse Execution & Fulfillment Status</h5>
                        <span class="small text-muted">Enterprise Order Ref: <code class="text-primary fw-bold" id="wh-modal-order-ref">{{ $orderRef }}</code></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" id="wh-modal-body">
                @if($pickingTask)
                    <!-- Top Status Metadata Bar -->
                    <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="text-muted small fw-semibold d-block">Task Reference Number</span>
                                <span class="fw-bold font-monospace text-primary fs-6" id="wh-modal-tasknum">{{ $pickingTask->task_number }}</span>
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block">Current Stage</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis text-capitalize px-3 py-1.5 fw-bold fs-7" id="wh-modal-stage">
                                    {{ ucfirst(str_replace('_', ' ', $pickingTask->status)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block">Priority Level</span>
                                <span class="badge bg-secondary-subtle text-secondary text-uppercase px-2.5 py-1 fw-semibold" id="wh-modal-priority">
                                    {{ ucfirst($pickingTask->priority ?? 'Normal') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Item Verification Progress Card -->
                    <div class="card p-3 rounded-3 border-translucent bg-body mb-4 shadow-xs">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-body small">Itemized Verification Progress:</span>
                            <span class="fw-bold text-primary" id="wh-modal-count">
                                {{ $pickingTask->verified_items_count }} / {{ $pickingTask->total_items_count }} Items Verified ({{ $pickingTask->completion_percentage }}%)
                            </span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $pickingTask->progress_color_class }}" id="wh-modal-progressbar" role="progressbar" 
                                 style="width: {{ $pickingTask->completion_percentage }}%; transition: width 0.4s ease;" 
                                 aria-valuenow="{{ $pickingTask->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Operational Details Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                <span class="text-muted small fw-semibold d-block">Distribution Warehouse:</span>
                                <strong class="text-body d-block mt-1" id="wh-modal-warehouse">{{ $pickingTask->warehouse->name ?? 'Main Warehouse' }}</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                <span class="text-muted small fw-semibold d-block">Assigned Warehouse Operator:</span>
                                <strong class="text-body d-block mt-1" id="wh-modal-operator">{{ $pickingTask->assignedUser->name ?? 'Unassigned Operator' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps Footer Strip -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 bg-body-tertiary rounded-3 border border-translucent mb-4 small text-muted">
                        <div>Started: <strong class="text-body" id="wh-modal-started">{{ $pickingTask->started_at ? $pickingTask->started_at->format('d M Y, h:i A') : 'Pending Operator' }}</strong></div>
                        <div>Completed: <strong class="text-body" id="wh-modal-completed">{{ $pickingTask->completed_at ? $pickingTask->completed_at->format('d M Y, h:i A') : 'In Progress' }}</strong></div>
                        <div>Last Synced: <strong class="text-body" id="wh-modal-updated">{{ $pickingTask->updated_at ? $pickingTask->updated_at->format('d M Y, h:i A') : 'Just Now' }}</strong></div>
                    </div>

                    <!-- Itemized Product Table -->
                    @if($pickingTask->items && $pickingTask->items->count() > 0)
                        <div class="card rounded-3 border-translucent overflow-hidden">
                            <div class="card-header bg-light border-bottom p-3">
                                <h6 class="fw-bold text-body mb-0 small">Itemized Verification & Bin Checklist</h6>
                            </div>
                            <div class="table-responsive" style="max-height: 220px;">
                                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-3">SKU</th>
                                            <th>Product Name</th>
                                            <th>Req Qty</th>
                                            <th>Picked</th>
                                            <th class="pe-3 text-end">Verification</th>
                                        </tr>
                                    </thead>
                                    <tbody id="wh-modal-items-tbody">
                                        @foreach($pickingTask->items as $item)
                                            @php
                                                $isVerified = (bool)$item->is_verified || ((int)$item->picked_quantity > 0 && (int)$item->picked_quantity >= (int)$item->requested_quantity);
                                            @endphp
                                            <tr>
                                                <td class="ps-3 font-monospace text-primary fw-bold">{{ $item->product->sku ?? 'N/A' }}</td>
                                                <td class="fw-semibold text-body">{{ $item->product->name ?? 'Product Line' }}</td>
                                                <td>{{ $item->requested_quantity }}</td>
                                                <td class="fw-bold">{{ $item->picked_quantity }}</td>
                                                <td class="pe-3 text-end">
                                                    @if($isVerified)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle">✔ Verified</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                @else
                    <!-- Clean Missing Record State -->
                    <div class="text-center py-5">
                        <div class="p-4 bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="fs-1">📦</span>
                        </div>
                        <h5 class="fw-bold text-body mb-1">No Warehouse Task Created Yet</h5>
                        <p class="text-muted small mx-auto mb-0" style="max-width: 420px;">
                            The warehouse execution task for order <code class="text-primary fw-bold">{{ $orderRef }}</code> has not been generated yet. It will be created automatically upon Sales Order conversion & manager approval.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 fw-semibold px-4" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================================= -->
<!-- 2. TRANSPORT & LOGISTICS READ-ONLY TRACKING MODAL (#transportStatusModal) -->
<!-- ======================================================================= -->
<div class="modal fade" id="transportStatusModal" tabindex="-1" aria-labelledby="transportStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow-lg rounded-4">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-translucent p-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 bg-rose-subtle text-rose rounded-3">
                        <span class="fs-4">🚚</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-body mb-0" id="transportStatusModalLabel">Transport & Logistics Dispatch Tracker</h5>
                        <span class="small text-muted">Enterprise Order Ref: <code class="text-rose fw-bold" id="trp-modal-order-ref">{{ $orderRef }}</code></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4" id="trp-modal-body">
                @if($transportRequest)
                    <!-- Top Status Metadata Bar -->
                    <div class="p-3 bg-body-tertiary rounded-3 border border-translucent mb-4">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <span class="text-muted small fw-semibold d-block">Transport Request Number</span>
                                <span class="fw-bold font-monospace text-rose fs-6" id="trp-modal-reqnum">{{ $transportRequest->request_number }}</span>
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block">Current Stage</span>
                                <span class="badge bg-success-subtle text-success text-capitalize px-3 py-1.5 fw-bold fs-7" id="trp-modal-stage">
                                    {{ ucfirst(str_replace('_', ' ', $transportRequest->status)) }}
                                </span>
                            </div>
                            <div>
                                <span class="text-muted small fw-semibold d-block">Carrier Fleet</span>
                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 fw-semibold" id="trp-modal-carrier">
                                    {{ $transportRequest->carrier ?? 'Internal Logistics' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Logistics Progress Bar Card -->
                    <div class="card p-3 rounded-3 border-translucent bg-body mb-4 shadow-xs">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="fw-bold text-body small">Logistics Dispatch Progress:</span>
                            <span class="fw-bold text-rose" id="trp-modal-pct">{{ $transportRequest->completion_percentage }}% Completed</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar {{ $transportRequest->progress_color_class }}" id="trp-modal-progressbar" role="progressbar" 
                                 style="width: {{ $transportRequest->completion_percentage }}%; transition: width 0.4s ease;" 
                                 aria-valuenow="{{ $transportRequest->completion_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                    <!-- Vehicle & Driver Assignment Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                <span class="text-muted small fw-semibold d-block">Assigned Vehicle Number:</span>
                                <strong class="text-body d-block mt-1 font-monospace" id="trp-modal-vehicle">{{ $transportRequest->vehicle_number ?? 'Unassigned' }}</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                <span class="text-muted small fw-semibold d-block">Assigned Driver Name:</span>
                                <strong class="text-body d-block mt-1" id="trp-modal-driver">{{ $transportRequest->driver_name ?? ($transportRequest->assignedDriver->name ?? 'Unassigned Driver') }}</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                <span class="text-muted small fw-semibold d-block">Tracking Reference ID:</span>
                                <strong class="text-body d-block mt-1 font-monospace text-truncate" id="trp-modal-tracking">{{ $transportRequest->tracking_number ?? 'TRK-PENDING' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Timestamps Footer Strip -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 bg-body-tertiary rounded-3 border border-translucent mb-0 small text-muted">
                        <div>Expected Delivery: <strong class="text-body" id="trp-modal-expected">{{ $transportRequest->expected_delivery_date ? $transportRequest->expected_delivery_date->format('d M Y') : 'Pending Schedule' }}</strong></div>
                        <div>Dispatched At: <strong class="text-body" id="trp-modal-dispatched">{{ $transportRequest->dispatched_at ? $transportRequest->dispatched_at->format('d M Y, h:i A') : 'Awaiting Dispatch' }}</strong></div>
                        <div>Delivered At: <strong class="text-body" id="trp-modal-delivered">{{ $transportRequest->delivered_at ? $transportRequest->delivered_at->format('d M Y, h:i A') : 'In Transit' }}</strong></div>
                    </div>

                @else
                    <!-- Clean Missing Record State -->
                    <div class="text-center py-5">
                        <div class="p-4 bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="fs-1">🚚</span>
                        </div>
                        <h5 class="fw-bold text-body mb-1">Waiting for Warehouse Completion</h5>
                        <p class="text-muted small mx-auto mb-0" style="max-width: 420px;">
                            The transport dispatch request for order <code class="text-rose fw-bold">{{ $orderRef }}</code> will be automatically emitted once warehouse pick & pack verification is completed.
                        </p>
                    </div>
                @endif
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top border-translucent p-3">
                <button type="button" class="btn btn-secondary rounded-3 fw-semibold px-4" data-bs-dismiss="modal">Close Window</button>
            </div>
        </div>
    </div>
</div>

@if($liveStatusUrl)
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var whModal = document.getElementById('warehouseStatusModal');
    var trpModal = document.getElementById('transportStatusModal');

    function syncLiveStatusData() {
        fetch("{{ $liveStatusUrl }}", {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (!data) return;

            // Sync Warehouse Data if present
            if (data.warehouse) {
                var wh = data.warehouse;
                var taskNum = document.getElementById('wh-modal-tasknum');
                if (taskNum) taskNum.textContent = wh.task_number || 'WH-TASK';
                var stage = document.getElementById('wh-modal-stage');
                if (stage) stage.textContent = wh.status_label || wh.status;
                var operator = document.getElementById('wh-modal-operator');
                if (operator) operator.textContent = wh.operator_name || 'Unassigned Operator';
                var warehouse = document.getElementById('wh-modal-warehouse');
                if (warehouse) warehouse.textContent = wh.warehouse_name || 'Main Warehouse';
                var count = document.getElementById('wh-modal-count');
                if (count) count.textContent = (wh.verified_items || 0) + ' / ' + (wh.total_items || 0) + ' Items Verified (' + (wh.completion_percentage || 0) + '%)';
                var bar = document.getElementById('wh-modal-progressbar');
                if (bar) {
                    bar.style.width = (wh.completion_percentage || 0) + '%';
                    bar.className = 'progress-bar ' + (wh.progress_color || wh.progress_color_class || 'bg-primary');
                }
                var updated = document.getElementById('wh-modal-updated');
                if (updated) updated.textContent = wh.updated_at || new Date().toLocaleString();
            }

            // Sync Transport Data if present
            if (data.transport) {
                var trp = data.transport;
                var reqNum = document.getElementById('trp-modal-reqnum');
                if (reqNum) reqNum.textContent = trp.request_number || 'TRP-REQ';
                var stageTrp = document.getElementById('trp-modal-stage');
                if (stageTrp) stageTrp.textContent = trp.status_label || trp.status;
                var carrier = document.getElementById('trp-modal-carrier');
                if (carrier) carrier.textContent = trp.carrier || 'Internal Logistics';
                var driver = document.getElementById('trp-modal-driver');
                if (driver) driver.textContent = trp.driver_name || 'Unassigned Driver';
                var vehicle = document.getElementById('trp-modal-vehicle');
                if (vehicle) vehicle.textContent = trp.vehicle_number || 'Unassigned Vehicle';
                var tracking = document.getElementById('trp-modal-tracking');
                if (tracking) tracking.textContent = trp.tracking_number || 'TRK-PENDING';
                var pct = document.getElementById('trp-modal-pct');
                if (pct) pct.textContent = (trp.completion_percentage || 0) + '% Completed';
                var barTrp = document.getElementById('trp-modal-progressbar');
                if (barTrp) {
                    barTrp.style.width = (trp.completion_percentage || 0) + '%';
                    barTrp.className = 'progress-bar ' + (trp.progress_color || trp.progress_color_class || 'bg-rose');
                }
            }
        })
        .catch(function(err) {
            console.error('Error syncing live status modals:', err);
        });
    }

    if (whModal) whModal.addEventListener('show.bs.modal', syncLiveStatusData);
    if (trpModal) trpModal.addEventListener('show.bs.modal', syncLiveStatusData);
});
</script>
@endpush
@endif
