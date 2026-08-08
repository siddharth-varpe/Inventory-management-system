@extends('layouts.app')

@section('title', 'Expiry Management Station - StockManager ERP')

@section('header', 'Expiry Management Station')
@section('subheader', 'Monitor shelf-life risks, upcoming lot expiries, and execute disposal, returns, or discounting workflows.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Expiry Manager</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-stock-sidebar />
    </div>

    <!-- Right Column: Expiry Station -->
    <div class="col-12 col-lg-9">
        <!-- Expiry Timeline KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('stock.expiry.index', ['range' => 'expired']) }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent {{ $filter === 'expired' ? 'border-danger border-2' : '' }} bg-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Already Expired</div>
                                <div class="fs-3 fw-bold text-danger">{{ $counts['expired'] }} Lots</div>
                            </div>
                            <span class="badge bg-danger-subtle text-danger p-2 rounded-3">Critical</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('stock.expiry.index', ['range' => '7']) }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent {{ $filter === '7' ? 'border-warning border-2' : '' }} bg-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Within 7 Days</div>
                                <div class="fs-3 fw-bold text-warning-emphasis">{{ $counts['days_7'] }} Lots</div>
                            </div>
                            <span class="badge bg-warning-subtle text-warning-emphasis p-2 rounded-3">7 Days</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('stock.expiry.index', ['range' => '30']) }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent {{ $filter === '30' ? 'border-info border-2' : '' }} bg-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Within 30 Days</div>
                                <div class="fs-3 fw-bold text-info">{{ $counts['days_30'] }} Lots</div>
                            </div>
                            <span class="badge bg-info-subtle text-info p-2 rounded-3">30 Days</span>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <a href="{{ route('stock.expiry.index', ['range' => '90']) }}" class="text-decoration-none">
                    <div class="card p-3 rounded-4 shadow-sm border-translucent {{ $filter === '90' ? 'border-secondary border-2' : '' }} bg-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Within 90 Days</div>
                                <div class="fs-3 fw-bold text-secondary">{{ $counts['days_90'] }} Lots</div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary p-2 rounded-3">90 Days</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Expiring Lots Table -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Expiring Inventory Lots (Filter: {{ strtoupper($filter) }})</h5>
            
            @if($expiringLots->isEmpty())
                <x-empty-state title="No Expiring Inventory Lots" message="No inventory lots matched the selected expiry timeline filter." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Batch No</th>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Lot Quantity</th>
                                <th>Expiry Date</th>
                                <th>Status / Days Left</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringLots as $lot)
                            @php
                                $daysLeft = now()->startOfDay()->diffInDays($lot->expiry_date->startOfDay(), false);
                            @endphp
                            <tr>
                                <td><code>{{ $lot->batch_number ?? 'DEFAULT' }}</code></td>
                                <td class="fw-semibold text-body">{{ $lot->product->name ?? 'N/A' }}</td>
                                <td><code>{{ $lot->product->sku ?? 'N/A' }}</code></td>
                                <td class="fw-bold fs-6">{{ $lot->quantity }}</td>
                                <td class="fw-bold">{{ $lot->expiry_date->format('Y-m-d') }}</td>
                                <td>
                                    @if($daysLeft < 0)
                                        <span class="badge bg-danger text-white">Expired {{ abs($daysLeft) }}d ago</span>
                                    @elseif($daysLeft <= 7)
                                        <span class="badge bg-danger-subtle text-danger">{{ $daysLeft }} days left</span>
                                    @elseif($daysLeft <= 30)
                                        <span class="badge bg-warning-subtle text-warning-emphasis">{{ $daysLeft }} days left</span>
                                    @else
                                        <span class="badge bg-info-subtle text-info">{{ $daysLeft }} days left</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-outline-danger btn-sm rounded-3 fw-semibold px-2 py-1" data-bs-toggle="modal" data-bs-target="#actionModal{{ $lot->id }}">
                                        Take Action
                                    </button>

                                    <!-- Action Modal -->
                                    <div class="modal fade text-start" id="actionModal{{ $lot->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 p-2">
                                                <form action="{{ route('stock.expiry.action') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="inventory_id" value="{{ $lot->id }}">
                                                    <div class="modal-header border-0">
                                                        <h5 class="modal-title fw-bold">Expiry Workflow: {{ $lot->product->name ?? '' }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="text-muted small mb-3">Lot <code>#{{ $lot->batch_number }}</code> containing <strong>{{ $lot->quantity }} units</strong> expiring on <strong>{{ $lot->expiry_date->format('Y-m-d') }}</strong>.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Select Action Workflow</label>
                                                            <select name="action_type" class="form-select rounded-3" required>
                                                                <option value="dispose">Dispose (Write-off Expired Stock)</option>
                                                                <option value="return">Return to Supplier</option>
                                                                <option value="discount">Apply Clearance Discount</option>
                                                                <option value="transfer">Transfer to Quarantine Warehouse</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Discount % (If applying discount)</label>
                                                            <input type="number" name="discount_percentage" class="form-control rounded-3" value="20" min="1" max="99">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Target Location (If transferring)</label>
                                                            <input type="text" name="target_location" class="form-control rounded-3" value="Quarantine Shelf Q-01">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-0">
                                                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary rounded-3 fw-bold">Execute Workflow</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div>
                    {{ $expiringLots->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
