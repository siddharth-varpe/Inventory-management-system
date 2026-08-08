@extends('layouts.app')

@section('title', 'Lead Directory - CRM Workspace')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="leads.index" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Header Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold text-body mb-0">Lead Directory</h3>
                    <p class="text-muted small mb-0 mt-1">Manage Commercial Sales Prospects & Lead Conversions</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.leads.pipeline') }}" class="btn btn-outline-primary rounded-3 fw-semibold">Visual Opportunity Pipeline &rarr;</a>
                </div>
            </div>
        </div>

        <!-- Leads Datatable -->
        <div class="card rounded-4 border-translucent shadow-sm bg-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Lead #</th>
                            <th>Company Name</th>
                            <th>Contact Person</th>
                            <th>Source</th>
                            <th>Expected Revenue</th>
                            <th>Probability</th>
                            <th>Status Stage</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leads as $lead)
                            <tr>
                                <td class="ps-3 font-monospace text-primary fw-bold">{{ $lead->lead_number }}</td>
                                <td class="fw-bold text-body">{{ $lead->company_name }}</td>
                                <td>
                                    <div>{{ $lead->contact_person }}</div>
                                    <div class="small text-muted">{{ $lead->phone ?? $lead->email }}</div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($lead->source) }}</span></td>
                                <td class="fw-bold text-success">₹{{ number_format((float)$lead->expected_revenue, 2) }}</td>
                                <td>{{ $lead->probability }}%</td>
                                <td>
                                    @if($lead->status === 'won')
                                        <span class="badge bg-success">WON / CONVERTED</span>
                                    @elseif($lead->status === 'lost')
                                        <span class="badge bg-danger">LOST</span>
                                    @elseif($lead->status === 'qualified')
                                        <span class="badge bg-warning text-dark">QUALIFIED</span>
                                    @else
                                        <span class="badge bg-info">{{ strtoupper($lead->status) }}</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('sales.leads.show', $lead->id) }}" class="btn btn-outline-secondary btn-sm rounded-3">360° Profile</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="fs-2 mb-2">📋</div>
                                    <div class="fw-bold">No leads found in directory.</div>
                                    <div class="small text-muted">Click "+ Add New Lead" in the Opportunity Pipeline to create your first lead.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($leads->hasPages())
                <div class="card-footer bg-transparent border-top border-translucent p-3">
                    {{ $leads->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
