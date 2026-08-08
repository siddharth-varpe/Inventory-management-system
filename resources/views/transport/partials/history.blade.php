<div class="col-12">
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-black text-body mb-0">📋 Transport History Archive</h4>
                <p class="text-muted small mb-0 mt-1">Archived records of completed, delivered, and returned transport orders.</p>
            </div>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-bold">
                {{ $requests->total() }} Archived Orders
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr class="text-muted font-monospace small">
                        <th>ORDER ID</th>
                        <th>CUSTOMER</th>
                        <th>DESTINATION</th>
                        <th>DRIVER</th>
                        <th>VEHICLE</th>
                        <th>STATUS</th>
                        <th>ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="fw-bold font-monospace text-primary">
                                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'task_id' => $req->id]) }}" class="text-decoration-none">
                                    {{ $req->order_reference }}
                                </a>
                            </td>
                            <td class="fw-bold text-body">{{ $req->customer_name }}</td>
                            <td class="text-muted">{{ $req->delivery_city }}</td>
                            <td>
                                @if($req->driver)
                                    <span class="fw-semibold text-body">👤 {{ $req->driver->driver_name }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($req->vehicle)
                                    <span class="fw-bold text-body font-monospace">🚛 {{ $req->vehicle->vehicle_number }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $req->status_badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('transport.index', ['tab' => 'delivery-orders', 'task_id' => $req->id]) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    View Record &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <div class="fs-4 mb-2">📜</div>
                                <div>No completed historical records found.</div>
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
