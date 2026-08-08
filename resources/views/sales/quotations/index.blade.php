@extends('layouts.app')

@section('title', 'Quotations Queue - Sales & CRM Portal')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="quotations" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Header Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h3 class="fw-bold text-body mb-0">Quotations Queue</h3>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem;">DOCUMENT DESK</span>
                    </div>
                    <p class="text-muted small mb-0">B2B Commercial Proposals, Pricing Estimates & Manager Approval Registry</p>
                </div>
                <div>
                    <a href="{{ route('sales.workspace') }}" class="btn btn-warning rounded-3 fw-bold text-dark px-3 py-2 shadow-sm">
                        + New Quotation (Sales Workspace)
                    </a>
                </div>
            </div>

            <!-- Search & Status Filter Bar -->
            <div class="row g-3 align-items-center mt-3 pt-3 border-top border-translucent">
                <div class="col-12 col-lg-7">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('sales.quotations.index') }}" class="btn btn-sm rounded-pill {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">All Quotations</a>
                        <a href="{{ route('sales.quotations.index', ['status' => 'draft']) }}" class="btn btn-sm rounded-pill {{ request('status') === 'draft' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Drafts</a>
                        <a href="{{ route('sales.quotations.index', ['status' => 'pending_approval']) }}" class="btn btn-sm rounded-pill {{ request('status') === 'pending_approval' ? 'btn-danger text-white fw-bold' : 'btn-outline-secondary' }}">Pending Approval</a>
                        <a href="{{ route('sales.quotations.index', ['status' => 'approved']) }}" class="btn btn-sm rounded-pill {{ request('status') === 'approved' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }}">Approved</a>
                        <a href="{{ route('sales.quotations.index', ['status' => 'converted']) }}" class="btn btn-sm rounded-pill {{ request('status') === 'converted' ? 'btn-purple text-white fw-bold' : 'btn-outline-secondary' }}">Converted</a>
                    </div>
                </div>
                <div class="col-12 col-lg-5">
                    <form method="GET" action="{{ route('sales.quotations.index') }}" class="d-flex gap-2">
                        @if(request('status'))
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <div class="input-group">
                            <span class="input-group-text bg-body border-end-0 text-muted">🔍</span>
                            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search quotation #, customer..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-outline-secondary fw-semibold">Search</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm mb-4">
                ✔ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 shadow-sm mb-4">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <!-- Document List Queue Table -->
        <div class="card rounded-4 border-translucent shadow-sm bg-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small">
                            <th class="ps-4">Quotation #</th>
                            <th>Enterprise Order ID</th>
                            <th>Customer Account</th>
                            <th>Salesperson</th>
                            <th>Quotation Date</th>
                            <th>Expiry Date</th>
                            <th>Priority</th>
                            <th>Grand Total</th>
                            <th class="pe-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($quotations as $q)
                            @php
                                $priorityClass = match($q->order_priority ?? 'normal') {
                                    'urgent' => 'bg-danger-subtle text-danger border-danger-subtle',
                                    'high' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    default => 'bg-secondary-subtle text-secondary border-secondary-subtle',
                                };
                            @endphp
                            <tr onclick="window.location='{{ route('sales.quotations.show', $q->id) }}'" style="cursor: pointer;" class="transition-all">
                                <td class="ps-4 fw-mono text-primary fw-bold">
                                    <div class="d-flex align-items-center gap-2">
                                        <span>📜</span>
                                        <span>{{ $q->quotation_number }}</span>
                                    </div>
                                </td>
                                <td>
                                    <code class="text-muted">{{ $q->salesOrder->order_number ?? $q->order_reference ?? $q->quotation_number }}</code>
                                </td>
                                <td>
                                    <div class="fw-bold text-body">{{ $q->customer->company_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">Code: {{ $q->customer->customer_code ?? 'CUST-N/A' }} | {{ strtoupper($q->customer->customer_type ?? 'Retail') }}</div>
                                </td>
                                <td>
                                    <span class="text-body fw-semibold">{{ $q->salesperson->name ?? 'Unassigned' }}</span>
                                </td>
                                <td>{{ $q->created_at ? $q->created_at->format('d M Y') : 'N/A' }}</td>
                                <td>
                                    @if($q->validity_date)
                                        <span class="{{ $q->validity_date->isPast() && $q->status !== 'converted' ? 'text-danger fw-bold' : 'text-body' }}">
                                            {{ $q->validity_date->format('d M Y') }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge border rounded-pill px-2.5 py-1 text-capitalize {{ $priorityClass }}" style="font-size: 0.7rem;">
                                        {{ ucfirst($q->order_priority ?? 'normal') }}
                                    </span>
                                </td>
                                <td class="fw-bold text-success fs-6">₹{{ number_format((float)$q->grand_total, 2) }}</td>
                                <td class="pe-4 text-center">
                                    <x-status-badge :status="$q->status" />
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="fs-2 mb-2">📜</div>
                                    <div class="fw-bold">No commercial quotations found in queue.</div>
                                    <div class="small text-muted">Use the Sales Workspace to create your first commercial proposal.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($quotations->hasPages())
                <div class="card-footer bg-transparent border-top border-translucent p-3">
                    {{ $quotations->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
