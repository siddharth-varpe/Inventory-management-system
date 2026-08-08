@extends('layouts.app')

@section('title', 'Packing & Dispatch Workspace - Organize Stock Portal')

@section('header', 'Packing & Dispatch Operations')
@section('subheader', 'Prepare picked inventory for shipment, generate shipping barcode labels, verify package weights, and dispatch to courier.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Packing & Dispatch</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Packing & Dispatch Workspace -->
    <div class="col-12 col-lg-9">
        <!-- KPI Metrics Header Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Total Dispatch Tasks</div>
                    <div class="fs-3 fw-bold text-body mt-1">{{ $kpis['total'] }}</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Awaiting Packing</div>
                    <div class="fs-3 fw-bold text-warning-emphasis mt-1">{{ $kpis['pending_pack'] }}</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Packed & Sealed</div>
                    <div class="fs-3 fw-bold text-primary mt-1">{{ $kpis['packed'] }}</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <div class="text-muted small fw-semibold">Dispatched Today</div>
                    <div class="fs-3 fw-bold text-success mt-1">{{ $kpis['dispatched'] }}</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar Bar -->
        <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <form method="GET" action="{{ route('organize.dispatch.index') }}" class="row g-2 align-items-center">
                <div class="col-12 col-md-5">
                    <input type="text" name="search" class="form-control rounded-3" placeholder="🔍 Search Dispatch #, Order Ref, Customer, Shipping Code..." value="{{ $search }}">
                </div>
                <div class="col-12 col-md-4">
                    <select name="status" class="form-select rounded-3">
                        <option value="">All Statuses</option>
                        <option value="pending_pack" {{ $status === 'pending_pack' ? 'selected' : '' }}>Pending Pack</option>
                        <option value="packed" {{ $status === 'packed' ? 'selected' : '' }}>Packed</option>
                        <option value="dispatched" {{ $status === 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    </select>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold">Filter</button>
                    <a href="{{ route('organize.dispatch.index') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>
        </div>

        <!-- Dispatch Queue Workspace Table -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Packing & Courier Dispatch Queue</h5>

            @if($dispatches->isEmpty())
                <x-empty-state title="No Dispatch Tasks Found" message="No pending pick orders currently waiting for packaging or dispatch." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Dispatch #</th>
                                <th>Order Ref</th>
                                <th>Customer & Destination</th>
                                <th class="text-center">Items</th>
                                <th>Weight</th>
                                <th>Shipping Label</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($dispatches as $d)
                            <tr>
                                <td>
                                    <code class="fw-bold fs-6">{{ $d->dispatch_number }}</code>
                                    <div class="text-muted small" style="font-size: 0.75rem;">Created {{ $d->created_at->diffForHumans() }}</div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-body">{{ $d->order_reference }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-body">{{ $d->customer_name }}</div>
                                    <div class="text-muted small text-truncate" style="max-width: 200px;">{{ $d->delivery_address ?? 'Standard Delivery' }}</div>
                                </td>
                                <td class="text-center fw-bold fs-6">{{ $d->total_items }}</td>
                                <td class="text-muted">{{ $d->total_weight_kg ? $d->total_weight_kg . ' kg' : '0.50 kg' }}</td>
                                <td>
                                    @if($d->shipping_label_code)
                                        <code class="bg-body-tertiary px-2 py-1 rounded border">{{ $d->shipping_label_code }}</code>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Not Generated</span>
                                    @endif
                                </td>
                                <td>
                                    @if($d->status === 'dispatched')
                                        <span class="badge bg-success-subtle text-success">Dispatched</span>
                                    @elseif($d->status === 'packed')
                                        <span class="badge bg-primary-subtle text-primary">Packed & Sealed</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Pending Pack</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm rounded-circle p-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-three-dots-vertical" viewBox="0 0 16 16"><path d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/></svg>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-translucent">
                                            @if($d->status === 'pending_pack')
                                                <li>
                                                    <form action="{{ route('organize.dispatch.update-status', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="packed">
                                                        <button type="submit" class="dropdown-item small text-primary fw-semibold">📦 Mark as Packed & Sealed</button>
                                                    </form>
                                                </li>
                                            @elseif($d->status === 'packed')
                                                <li>
                                                    <form action="{{ route('organize.dispatch.update-status', $d->id) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="status" value="dispatched">
                                                        <button type="submit" class="dropdown-item small text-success fw-bold">🚚 Confirm Courier Dispatch</button>
                                                    </form>
                                                </li>
                                            @endif
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button class="dropdown-item small text-body" data-bs-toggle="modal" data-bs-target="#labelModal{{ $d->id }}">📄 Print Shipping Label</button>
                                            </li>
                                        </ul>
                                    </div>

                                    <!-- Shipping Label Modal -->
                                    <div class="modal fade text-start" id="labelModal{{ $d->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 p-3">
                                                <div class="modal-header border-0 pb-1">
                                                    <h5 class="modal-title fw-bold">WMS Shipping Barcode Manifest</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="p-3 border rounded-4 bg-white text-dark text-center mb-3">
                                                        <div class="fw-bold fs-5 mb-1">StockManager Logistics</div>
                                                        <div class="font-monospace fs-4 bg-light p-2 rounded border mb-2">{{ $d->shipping_label_code ?: 'TRK-' . strtoupper(substr(md5((string)$d->id), 0, 10)) }}</div>
                                                        <div class="small text-muted mb-2">Order Ref: <strong>{{ $d->order_reference }}</strong></div>
                                                        <div class="text-start border-top pt-2 small">
                                                            <div><strong>Recipient:</strong> {{ $d->customer_name }}</div>
                                                            <div><strong>Address:</strong> {{ $d->delivery_address ?: 'Standard Distribution Hub' }}</div>
                                                            <div><strong>Weight:</strong> {{ $d->total_weight_kg ?: '0.50' }} kg | <strong>Items:</strong> {{ $d->total_items }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Close</button>
                                                    <button type="button" class="btn btn-primary rounded-3 fw-bold" onclick="window.print()">🖨️ Print Label</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted small">
                        Showing {{ $dispatches->firstItem() }} to {{ $dispatches->lastItem() }} of {{ $dispatches->total() }} dispatch entries
                    </div>
                    <div>
                        {{ $dispatches->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
