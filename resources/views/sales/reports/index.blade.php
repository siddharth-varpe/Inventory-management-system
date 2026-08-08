@extends('layouts.app')

@section('title', 'Commercial Reports & Intelligence Center')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="reports.index" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Header Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold text-body mb-0">Commercial Reports & Analytics Engine</h3>
                    <p class="text-muted small mb-0 mt-1">Live Enterprise Analytics, Revenue Tax Summaries & Export Suite</p>
                </div>
                <div>
                    <a href="{{ route('sales.reports.export', ['report_type' => $reportType]) }}" class="btn btn-dark rounded-3 fw-semibold">
                        📥 Export to CSV
                    </a>
                </div>
            </div>

            <!-- Report Selector Tabs -->
            <div class="d-flex gap-2 mt-3 pt-3 border-top border-translucent">
                <a href="{{ route('sales.reports.index', ['report_type' => 'orders']) }}" class="btn btn-sm rounded-pill {{ $reportType === 'orders' ? 'btn-primary' : 'btn-outline-secondary' }}">Sales Orders Revenue</a>
                <a href="{{ route('sales.reports.index', ['report_type' => 'quotations']) }}" class="btn btn-sm rounded-pill {{ $reportType === 'quotations' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">Quotations Conversion</a>
                <a href="{{ route('sales.reports.index', ['report_type' => 'leads']) }}" class="btn btn-sm rounded-pill {{ $reportType === 'leads' ? 'btn-info text-white fw-bold' : 'btn-outline-secondary' }}">Lead Pipeline</a>
                <a href="{{ route('sales.reports.index', ['report_type' => 'customers']) }}" class="btn btn-sm rounded-pill {{ $reportType === 'customers' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }}">Customer Portfolio</a>
            </div>
        </div>

        <!-- Metrics Overview Grid -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <span class="text-muted small">Total Commercial Sales</span>
                    <h4 class="fw-black text-success mb-0 mt-1">₹{{ number_format((float)($metrics['total_sales'] ?? 0), 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <span class="text-muted small">Sales Orders Processed</span>
                    <h4 class="fw-black text-primary mb-0 mt-1">{{ number_format($metrics['total_orders'] ?? 0) }} Orders</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <span class="text-muted small">Active Stock Reserved</span>
                    <h4 class="fw-black text-info mb-0 mt-1">{{ number_format($metrics['total_reservations'] ?? 0) }} Units</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card p-3 rounded-4 border-translucent shadow-sm bg-body">
                    <span class="text-muted small">Total Customer Accounts</span>
                    <h4 class="fw-black text-body mb-0 mt-1">{{ number_format($metrics['total_customers'] ?? 0) }} Accounts</h4>
                </div>
            </div>
        </div>

        <!-- Report Dynamic Table -->
        <div class="card rounded-4 border-translucent shadow-sm bg-body">
            <div class="card-header bg-transparent border-bottom border-translucent p-3">
                <h6 class="fw-bold text-body mb-0">Live Operational Data Stream ({{ ucfirst($reportType) }})</h6>
            </div>
            <div class="table-responsive">
                @if($reportType === 'orders')
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Order #</th>
                                <th>Customer</th>
                                <th>Salesperson</th>
                                <th>Order Date</th>
                                <th>Taxable Amount</th>
                                <th>CGST + SGST / IGST</th>
                                <th>Grand Total</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $o)
                                <tr>
                                    <td class="ps-3 font-monospace text-primary fw-bold">{{ $o->order_number }}</td>
                                    <td>{{ $o->customer->company_name ?? 'N/A' }}</td>
                                    <td>{{ $o->salesperson->name ?? 'Unassigned' }}</td>
                                    <td>{{ $o->order_date->format('d M Y') }}</td>
                                    <td>₹{{ number_format((float)$o->taxable_amount, 2) }}</td>
                                    <td>₹{{ number_format((float)($o->cgst_amount + $o->sgst_amount + $o->igst_amount), 2) }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format((float)$o->grand_total, 2) }}</td>
                                    <td class="pe-3"><span class="badge bg-success">{{ strtoupper($o->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @elseif($reportType === 'quotations')
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Quotation #</th>
                                <th>Customer</th>
                                <th>Validity Date</th>
                                <th>Taxable Amount</th>
                                <th>Grand Total</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotations as $q)
                                <tr>
                                    <td class="ps-3 font-monospace text-primary fw-bold">{{ $q->quotation_number }}</td>
                                    <td>{{ $q->customer->company_name ?? 'N/A' }}</td>
                                    <td>{{ $q->validity_date->format('d M Y') }}</td>
                                    <td>₹{{ number_format((float)$q->taxable_amount, 2) }}</td>
                                    <td class="fw-bold text-success">₹{{ number_format((float)$q->grand_total, 2) }}</td>
                                    <td class="pe-3"><span class="badge bg-warning text-dark">{{ strtoupper($q->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Code</th>
                                <th>Company Name</th>
                                <th>Contact Person</th>
                                <th>Type</th>
                                <th class="pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $c)
                                <tr>
                                    <td class="ps-3 font-monospace text-primary fw-bold">{{ $c->customer_code }}</td>
                                    <td>{{ $c->company_name }}</td>
                                    <td>{{ $c->contact_person }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ strtoupper($c->customer_type) }}</span></td>
                                    <td class="pe-3"><span class="badge bg-success">{{ strtoupper($c->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
