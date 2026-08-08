@extends('layouts.app')

@section('title', 'Batch Management - StockManager ERP')

@section('header', 'Batch & Lot Management')
@section('subheader', 'Track inventory batches, manufacturing dates, expiry dates, and lot balances.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Batches</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Batches Table -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold text-body mb-1">Batch & Lot Master</h5>
                    <p class="text-muted small mb-0">Showing active batch lots across all products</p>
                </div>
                <a href="{{ route('stock.receive.index') }}" class="btn btn-primary rounded-3 px-3 fw-semibold">Receive New Batch</a>
            </div>

            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('stock.batches.index') }}" class="row g-2 mb-4">
                <div class="col-12 col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="Search Batch No, Lot No, Product Name, SKU..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary rounded-3 w-100 fw-semibold">Search</button>
                    <a href="{{ route('stock.batches.index') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>

            @if($batches->isEmpty())
                <x-empty-state title="No Active Batches" message="Received stock lots with batch numbers will appear here." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Batch No</th>
                                <th>Lot No</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Cost</th>
                                <th>Mfg Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batches as $batch)
                            <tr>
                                <td><code>{{ $batch->batch_number }}</code></td>
                                <td><code>{{ $batch->lot_number }}</code></td>
                                <td>
                                    @if($batch->product)
                                        <a href="{{ route('products.show', $batch->product) }}" class="fw-bold text-decoration-none text-body">{{ $batch->product->name }}</a>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $batch->product->sku }}</div>
                                    @else
                                        <span class="text-muted fw-semibold">Unknown / Deleted Product</span>
                                    @endif
                                </td>
                                <td class="fw-bold fs-6">{{ $batch->quantity }}</td>
                                <td>₹{{ number_format((float)$batch->unit_cost, 2) }}</td>
                                <td>{{ $batch->mfg_date ? $batch->mfg_date->format('Y-m-d') : 'N/A' }}</td>
                                <td>
                                    @if($batch->expiry_date)
                                        @if($batch->expiry_date->isPast())
                                            <span class="badge bg-danger-subtle text-danger">{{ $batch->expiry_date->format('Y-m-d') }} (Expired)</span>
                                        @elseif($batch->expiry_date->diffInDays(now()) <= 30)
                                            <span class="badge bg-warning-subtle text-warning-emphasis">{{ $batch->expiry_date->format('Y-m-d') }}</span>
                                        @else
                                            <span>{{ $batch->expiry_date->format('Y-m-d') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><span class="badge bg-success-subtle text-success">{{ $batch->status }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $batches->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
