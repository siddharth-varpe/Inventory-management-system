@extends('layouts.app')

@section('title', 'Warehouse Exception Handling - Organize Stock')

@section('header', 'Warehouse Exception Handling')
@section('subheader', 'Report operational anomalies (Short Pick, Missing Item, Damaged Item, Wrong Location, Quality Failure) and trigger automated write-offs or cycle count audits.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Exceptions</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Exceptions Area -->
    <div class="col-12 col-lg-9">
        <!-- Report New Exception Form Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <h5 class="fw-bold text-body mb-3">Report Warehouse Exception</h5>
            
            <form action="{{ route('organize.exceptions.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label fw-semibold">Exception Type <span class="text-danger">*</span></label>
                        <select name="exception_type" class="form-select rounded-3" required>
                            <option value="short_pick">Short Pick (Qty Deficit)</option>
                            <option value="missing_item">Missing Item</option>
                            <option value="damaged_item">Damaged Item</option>
                            <option value="wrong_location">Wrong Location Storage</option>
                            <option value="quality_failure">Quality Inspection Failure</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-5">
                        <label class="form-label fw-semibold">Affected Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select rounded-3" required>
                            <option value="">Select product...</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label fw-semibold">Affected Qty <span class="text-danger">*</span></label>
                        <input type="number" name="affected_quantity" class="form-control rounded-3" value="1" min="1" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Description / Observation <span class="text-danger">*</span></label>
                        <input type="text" name="description" class="form-control rounded-3" placeholder="Describe the physical anomaly observed during picking/put-away..." required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label class="form-label fw-semibold">Action Trigger <span class="text-danger">*</span></label>
                        <select name="action_taken" class="form-select rounded-3" required>
                            <option value="report_exception">Report Exception (Log Anomaly)</option>
                            <option value="request_cycle_count">Request Immediate Cycle Count Audit</option>
                            <option value="create_writeoff">Create Write-Off Request (Stock Loss)</option>
                            <option value="notify_inventory">Notify Inventory Control Team</option>
                        </select>
                    </div>

                    <div class="col-12 text-end">
                        <button type="submit" class="btn btn-danger rounded-3 px-4 py-2 fw-bold">Submit Exception Report</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Exceptions History Table -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <h5 class="fw-bold text-body mb-0">Reported Warehouse Exceptions</h5>
            </div>

            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('organize.exceptions.index') }}" class="row g-2 mb-4">
                <div class="col-12 col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-body-tertiary border-end-0 rounded-start-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search text-muted" viewBox="0 0 16 16"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/></svg>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="Search Exception Ref, Type, Product, SKU..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary rounded-3 w-100 fw-semibold">Search</button>
                    <a href="{{ route('organize.exceptions.index') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>

            @if($exceptions->isEmpty())
                <x-empty-state title="No Exceptions Logged" message="No warehouse operational exceptions reported." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Ref Number</th>
                                <th>Type</th>
                                <th>Product</th>
                                <th>Affected Qty</th>
                                <th>Action Taken</th>
                                <th>Reported By</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($exceptions as $exc)
                            <tr>
                                <td><code>{{ $exc->exception_number }}</code></td>
                                <td><span class="badge bg-danger-subtle text-danger">{{ str_replace('_', ' ', strtoupper($exc->exception_type)) }}</span></td>
                                <td>
                                    <div class="fw-bold text-body">{{ $exc->product->name ?? 'N/A' }}</div>
                                    <code class="small text-muted">{{ $exc->product->sku ?? 'N/A' }}</code>
                                </td>
                                <td class="fw-bold text-danger fs-6">{{ $exc->affected_quantity }}</td>
                                <td><span class="badge bg-warning-subtle text-warning-emphasis">{{ str_replace('_', ' ', ucfirst($exc->action_taken)) }}</span></td>
                                <td class="text-muted small">{{ $exc->reportedBy->name ?? 'System User' }}</td>
                                <td><x-status-badge :status="$exc->status" /></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $exceptions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
