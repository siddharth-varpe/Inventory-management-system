@extends('layouts.app')

@section('title', 'Internal Warehouse Stock Transfers - Organize Stock')

@section('header', 'Internal Warehouse Stock Transfers')
@section('subheader', 'Relocate stock between warehouses, zones, racks, shelves, and bins with complete history tracking.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Internal Transfers</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Internal Transfers Area -->
    <div class="col-12 col-lg-9">
        <!-- New Transfer Form Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <h5 class="fw-bold text-body mb-3">Execute Internal Stock Relocation</h5>
            
            <form action="{{ route('organize.transfers.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select rounded-3" required>
                            <option value="">Choose product...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-2">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control rounded-3" value="1" min="1" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Source Location <span class="text-danger">*</span></label>
                        <input type="text" name="from_coordinate" class="form-control rounded-3" value="Main Storage / Rack A-01 / Shelf 1 / Bin 01" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Target Destination <span class="text-danger">*</span></label>
                        <input type="text" name="to_coordinate" class="form-control rounded-3" value="Picking Zone / Rack P-01 / Shelf 2 / Bin 04" required>
                    </div>

                    <div class="col-12 col-md-9">
                        <label class="form-label fw-semibold">Transfer Reason</label>
                        <input type="text" name="reason" class="form-control rounded-3" placeholder="e.g. Relocating fast-moving items closer to picking station">
                    </div>

                    <div class="col-12 col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary rounded-3 w-100 py-2 fw-bold">Execute Relocation</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Transfers History Table -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h5 class="fw-bold text-body mb-0">Internal Transfers Movement Log</h5>
            </div>

            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('organize.transfers.index') }}" class="row g-2 mb-4">
                <div class="col-12 col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="Search Transfer Ref, Product, SKU, Locations..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary rounded-3 w-100 fw-semibold">Search</button>
                    <a href="{{ route('organize.transfers.index') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>

            @if($transfers->isEmpty())
                <x-empty-state title="No Transfer Records" message="Internal stock movement logs will appear here." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Transfer Ref</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Source Location</th>
                                <th>Target Destination</th>
                                <th>Operator</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transfers as $trf)
                            <tr>
                                <td><code>{{ $trf->transfer_number }}</code></td>
                                <td>
                                    <div class="fw-bold text-body">{{ $trf->product->name ?? 'N/A' }}</div>
                                    <code class="small text-muted">{{ $trf->product->sku ?? 'N/A' }}</code>
                                </td>
                                <td class="fw-bold fs-6">{{ $trf->quantity }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $trf->from_coordinate }}</span></td>
                                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle">{{ $trf->to_coordinate }}</span></td>
                                <td class="text-muted small">{{ $trf->operator->name ?? 'System Admin' }}</td>
                                <td><x-status-badge :status="$trf->status" /></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $transfers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
