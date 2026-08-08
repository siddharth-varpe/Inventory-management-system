@extends('layouts.app')

@section('title', 'Sales Orders Queue - Sales & CRM Portal')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="orders" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Header Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold text-body mb-0">Sales Orders Queue</h3>
                    <p class="text-muted small mb-0 mt-1">Commercial Transaction Core, Inventory Reservations & Fulfillment Forwarding</p>
                </div>
                <div>
                    <a href="{{ route('sales.quotations.index') }}" class="btn btn-warning rounded-3 fw-bold text-dark px-3 py-2">
                        View Quotations Queue
                    </a>
                </div>
            </div>

            <!-- Filter Status Bar -->
            <div class="d-flex gap-2 mt-3 pt-3 border-top border-translucent">
                <a href="{{ route('sales.orders.index') }}" class="btn btn-sm rounded-pill {{ !request('status') ? 'btn-primary' : 'btn-outline-secondary' }}">All Orders</a>
                <a href="{{ route('sales.orders.index', ['status' => 'reserved']) }}" class="btn btn-sm rounded-pill {{ request('status') === 'reserved' ? 'btn-success text-white fw-bold' : 'btn-outline-secondary' }}">Reserved</a>
                <a href="{{ route('sales.orders.index', ['status' => 'approved']) }}" class="btn btn-sm rounded-pill {{ request('status') === 'approved' ? 'btn-info text-white fw-bold' : 'btn-outline-secondary' }}">Approved</a>
            </div>
        </div>

        <!-- Sales Orders Datatable -->
        <div class="card rounded-4 border-translucent shadow-sm bg-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Order #</th>
                            <th>Customer Account</th>
                            <th>Warehouse</th>
                            <th>Order Date</th>
                            <th>Taxable Value</th>
                            <th>Grand Total</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $o)
                            <tr>
                                <td class="ps-3 fw-mono text-primary fw-bold">{{ $o->order_number }}</td>
                                <td>
                                    <div class="fw-bold text-body">{{ $o->customer->company_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ strtoupper($o->customer->customer_type ?? 'Dealer') }}</div>
                                </td>
                                <td>{{ $o->warehouse->name ?? 'Main Warehouse' }}</td>
                                <td>{{ $o->order_date->format('d M Y') }}</td>
                                <td>₹{{ number_format((float)$o->taxable_amount, 2) }}</td>
                                <td class="fw-bold text-success fs-6">₹{{ number_format((float)$o->grand_total, 2) }}</td>
                                <td>
                                    @if($o->status === 'reserved')
                                        <span class="badge bg-success">RESERVED</span>
                                    @elseif($o->status === 'approved')
                                        <span class="badge bg-info">APPROVED</span>
                                    @elseif($o->status === 'cancelled')
                                        <span class="badge bg-secondary">CANCELLED</span>
                                    @else
                                        <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $o->status)) }}</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('sales.orders.show', $o->id) }}" class="btn btn-outline-secondary btn-sm rounded-3">View Order</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="fs-2 mb-2">📦</div>
                                    <div class="fw-bold">No sales orders found in queue.</div>
                                    <div class="small text-muted">Convert an approved quotation from the Quotations Queue to generate an order.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($orders->hasPages())
                <div class="card-footer bg-transparent border-top border-translucent p-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
