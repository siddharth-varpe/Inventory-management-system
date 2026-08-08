@extends('layouts.app')

@section('title', 'Vendor Performance - Order Supplies PMS')

@section('header', 'Vendor Operational Performance Monitoring')
@section('subheader', 'Track supplier on-time delivery percentages, defect/rejection rates, and order fulfillment ratings.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Order Supplies</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vendor Performance</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="vendor-performance" />
    </div>

    <!-- Right Column: Vendor Performance Workspace -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
            <h5 class="fw-bold text-body mb-3">Supplier Quality & Delivery Scorecard</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr class="text-muted small">
                            <th>Supplier Name</th>
                            <th>On-Time Delivery %</th>
                            <th>Quality Clearance %</th>
                            <th>Avg Lead Time</th>
                            <th>Overall Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $s)
                            <tr>
                                <td class="fw-bold text-body">{{ $s->name }}</td>
                                <td><span class="badge bg-success-subtle text-success">98.5% On-Time</span></td>
                                <td><span class="badge bg-success-subtle text-success">99.2% Cleared</span></td>
                                <td class="fw-semibold">5.2 Days</td>
                                <td><span class="badge bg-warning-subtle text-warning-emphasis">★ {{ $s->rating }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
