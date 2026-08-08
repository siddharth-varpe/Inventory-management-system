<div class="col-12">
    <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 border-bottom pb-3">
            <div>
                <h4 class="fw-black text-body mb-0">📋 Transport History Archive</h4>
                <p class="text-muted small mb-0 mt-1">Archived records of completed, delivered, and cancelled transport orders.</p>
            </div>
            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 fw-bold">
                {{ $requests->total() }} Archived Orders
            </span>
        </div>

        <!-- Search Bar & Filters -->
        <form method="GET" action="{{ route('transport.index') }}" class="row g-2 mb-4">
            <input type="hidden" name="tab" value="history">
            <div class="col-md-6">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body border-translucent text-muted">🔍</span>
                    <input type="text" name="search" class="form-control bg-body border-translucent" 
                           placeholder="Search history by Dispatch ID, Order ID, Driver, Vehicle, City..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-6 text-end">
                <button type="submit" class="btn btn-sm btn-secondary px-3 fw-bold rounded-3">Search History</button>
                @if(request('search'))
                    <a href="{{ route('transport.index', ['tab' => 'history']) }}" class="btn btn-sm btn-outline-secondary px-3 rounded-3">Reset</a>
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
                        <th>STATUS</th>
                        <th class="text-end">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td class="fw-bold font-monospace text-secondary">
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill font-monospace px-2 py-1">
                                    {{ $req->dispatch_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="fw-bold font-monospace text-body">
                                <a href="javascript:void(0)" onclick="openDeliveryOrderProfile({{ $req->id }})" class="text-decoration-none text-body">
                                    {{ $req->order_reference }}
                                </a>
                            </td>
                            <td class="fw-bold text-body">{{ $req->customer_name }}</td>
                            <td class="text-muted small">📍 {{ $req->city }}</td>
                            <td>
                                @if($req->driver)
                                    <span class="fw-semibold text-body small">👤 {{ $req->driver->driver_name }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($req->vehicle)
                                    <span class="fw-bold text-body font-monospace small">🚛 {{ $req->vehicle->vehicle_number }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ $req->status_badge_class }}">
                                    {{ $req->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" onclick="openDeliveryOrderProfile({{ $req->id }})">
                                    View Record &rarr;
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <div class="fs-4 mb-2">📜</div>
                                <div>No historical records found.</div>
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
