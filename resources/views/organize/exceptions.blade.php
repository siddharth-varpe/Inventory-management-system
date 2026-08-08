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
            <h5 class="fw-bold text-body mb-3">Reported Warehouse Exceptions</h5>

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
