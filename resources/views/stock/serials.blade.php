@extends('layouts.app')

@section('title', 'Serial Number Tracking - StockManager ERP')

@section('header', 'Serial Number Tracking')
@section('subheader', 'Register and track individual unit serial numbers for serialized product lines.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Serial Numbers</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Serials Area -->
    <div class="col-12 col-lg-9">
        <!-- Add Serial Number Form Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <h5 class="fw-bold text-body mb-3">Register Serial Number</h5>
            <form action="{{ route('stock.serials.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Select Serialized Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select rounded-3 @error('product_id') is-invalid @enderror" required>
                            <option value="">Choose product...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                            @endforeach
                        </select>
                        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Serial Number <span class="text-danger">*</span></label>
                        <input type="text" name="serial_number" class="form-control rounded-3 @error('serial_number') is-invalid @enderror" placeholder="e.g. SN-998822-X1" required>
                        @error('serial_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="available">Available</option>
                            <option value="sold">Sold</option>
                            <option value="damaged">Damaged</option>
                            <option value="returned">Returned</option>
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-bold">Register Serial</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Serials List -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h5 class="fw-bold text-body mb-0">Registered Serial Numbers</h5>
                <form method="GET" action="{{ route('stock.serials.index') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm rounded-3" placeholder="Search Serial No..." value="{{ $filters['search'] ?? '' }}">
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="available" {{ ($filters['status'] ?? '') == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="sold" {{ ($filters['status'] ?? '') == 'sold' ? 'selected' : '' }}>Sold</option>
                        <option value="damaged" {{ ($filters['status'] ?? '') == 'damaged' ? 'selected' : '' }}>Damaged</option>
                    </select>
                    <button type="submit" class="btn btn-secondary btn-sm rounded-3 px-3">Filter</button>
                </form>
            </div>

            @if($serials->isEmpty())
                <x-empty-state title="No Serial Numbers Found" message="Registered unit serial numbers will appear here." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Serial Number</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Registered Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($serials as $sn)
                            <tr>
                                <td><code class="fw-bold text-body fs-6">{{ $sn->serial_number }}</code></td>
                                <td class="fw-semibold">{{ $sn->product->name ?? 'N/A' }}</td>
                                <td><code>{{ $sn->product->sku ?? 'N/A' }}</code></td>
                                <td class="text-muted small">{{ $sn->created_at->format('Y-m-d H:i') }}</td>
                                <td>
                                    @if($sn->status === 'available')
                                        <span class="badge bg-success-subtle text-success">Available</span>
                                    @elseif($sn->status === 'sold')
                                        <span class="badge bg-info-subtle text-info">Sold</span>
                                    @elseif($sn->status === 'damaged')
                                        <span class="badge bg-danger-subtle text-danger">Damaged</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis">{{ $sn->status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $serials->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
