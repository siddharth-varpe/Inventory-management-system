@extends('layouts.app')

@section('title', 'Procurement Reports - Order Supplies PMS')

@section('header', 'Operational Procurement Reports')
@section('subheader', 'Generate exportable purchase registers, supplier ledgers, and landed cost audit reports.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Order Supplies</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reports</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="reports" />
    </div>

    <!-- Right Column: Reports Workspace -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Procurement Analytics & Export Center</h5>

            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-4 border bg-body-tertiary">
                        <h6 class="fw-bold text-body mb-1">Purchase Register Report</h6>
                        <p class="text-muted small mb-3">Detailed list of all purchase orders issued within specified date range.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3">Export CSV</button>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-4 border bg-body-tertiary">
                        <h6 class="fw-bold text-body mb-1">Supplier Ledger & Volume</h6>
                        <p class="text-muted small mb-3">Supplier spend aggregation and open contract balances.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3">Export CSV</button>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-4 border bg-body-tertiary">
                        <h6 class="fw-bold text-body mb-1">Three-Way Invoice Matching Audit</h6>
                        <p class="text-muted small mb-3">Audit trail of verified supplier invoices and payment clearances.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3">Export CSV</button>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-4 border bg-body-tertiary">
                        <h6 class="fw-bold text-body mb-1">Landed Cost Allocation Log</h6>
                        <p class="text-muted small mb-3">Summary of freight, insurance, and customs duty allocations.</p>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-3">Export CSV</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
