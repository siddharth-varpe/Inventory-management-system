@extends('layouts.app')

@section('title', 'Sales & CRM Command Center - StockManager ERP')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="dashboard" />

    <!-- Main Workspace Content Area -->
    <div class="flex-grow-1">

        <!-- Top Header & Action Bar -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">COMMERCIAL DESK</span>
                        <h3 class="fw-bold text-body mb-0">Sales & CRM Command Center</h3>
                    </div>
                    <p class="text-muted small mb-0 mt-1">Executive Commercial Intelligence, Real-time Sales Revenue & CRM Pipeline Control</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('sales.workspace') }}" class="btn btn-warning rounded-3 fw-bold d-flex align-items-center gap-2 px-3 py-2 text-dark">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-cart3" viewBox="0 0 16 16"><path d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/></svg>
                        <span>Sales Workspace</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Total Revenue</span>
                            <h3 class="fw-black text-success mb-0 mt-1">₹{{ number_format((float)$totalRevenue, 2) }}</h3>
                        </div>
                        <div class="p-3 bg-success-subtle text-success rounded-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-cash-stack" viewBox="0 0 16 16"><path d="M1 3a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1H1zm7 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/><path d="M0 5a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H1a1 1 0 0 1-1-1V5zm1 0v8h14V5H1z"/></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Pending Orders</span>
                            <h3 class="fw-black text-primary mb-0 mt-1">{{ number_format($pendingOrdersCount) }}</h3>
                        </div>
                        <div class="p-3 bg-primary-subtle text-primary rounded-4">
                            <span class="fs-4">📦</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Lead Win Rate</span>
                            <h3 class="fw-black text-info mb-0 mt-1">{{ $leadConversionRate }}%</h3>
                        </div>
                        <div class="p-3 bg-info-subtle text-info rounded-4">
                            <span class="fs-4">🎯</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold">Blocked Accounts</span>
                            <h3 class="fw-black text-danger mb-0 mt-1">{{ number_format($blockedCustomers) }}</h3>
                        </div>
                        <div class="p-3 bg-danger-subtle text-danger rounded-4">
                            <span class="fs-4">🚫</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Customers & Groups Breakdown Grid -->
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card rounded-4 border-translucent shadow-sm bg-body">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-body mb-0">Recent Customer Registrations</h6>
                        <a href="{{ route('sales.customers.index') }}" class="btn btn-link btn-sm text-decoration-none fw-semibold p-0">View All Customers &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Code</th>
                                    <th>Company Name</th>
                                    <th>Type</th>
                                    <th>Territory</th>
                                    <th>Status</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCustomers as $c)
                                    <tr>
                                        <td class="ps-3 fw-mono text-primary fw-bold">{{ $c->customer_code }}</td>
                                        <td>
                                            <div class="fw-bold text-body">{{ $c->company_name }}</div>
                                            <div class="small text-muted">{{ $c->gst_number ?? 'No GST' }}</div>
                                        </td>
                                        <td><span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $c->customer_type }}</span></td>
                                        <td>{{ $c->territory->name ?? 'Unassigned' }}</td>
                                        <td>
                                            @if($c->status === 'active')
                                                <span class="badge bg-success-subtle text-success">Active</span>
                                            @elseif($c->status === 'blocked')
                                                <span class="badge bg-danger-subtle text-danger">Blocked</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning">{{ ucfirst($c->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 text-end">
                                            <a href="{{ route('sales.customers.show', $c->id) }}" class="btn btn-outline-secondary btn-sm rounded-3">Profile</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No customers registered yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">Customer Groups Breakdown</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="list-group list-group-flush">
                            @forelse($groups as $g)
                                <div class="list-group-item d-flex align-items-center justify-content-between bg-transparent border-0 px-0 py-2">
                                    <div class="fw-semibold text-body">{{ $g->name }}</div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $g->customers_count }} Accounts</span>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-2">No groups configured.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="card rounded-4 border-translucent shadow-sm bg-body">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">Territories Overview</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="list-group list-group-flush">
                            @forelse($territories as $t)
                                <div class="list-group-item d-flex align-items-center justify-content-between bg-transparent border-0 px-0 py-2">
                                    <div>
                                        <div class="fw-semibold text-body">{{ $t->name }}</div>
                                        <div class="small text-muted">{{ $t->region }} ({{ $t->sales_zone }})</div>
                                    </div>
                                    <span class="badge bg-info-subtle text-info rounded-pill px-3">{{ $t->customers_count }} Accounts</span>
                                </div>
                            @empty
                                <div class="text-muted small text-center py-2">No territories configured.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
