@extends('layouts.app')

@section('title', 'Supplier Directory - Order Supplies PMS')

@section('header', 'Supplier Relationship Management')
@section('subheader', 'Maintain enterprise vendor records, tax registrations, payment terms, and performance ratings.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Order Supplies</a></li>
    <li class="breadcrumb-item active" aria-current="page">Suppliers</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="suppliers" />
    </div>

    <!-- Right Column: Workspace -->
    <div class="col-12 col-lg-9">
        <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold text-body mb-1">Supplier Master Registry</h5>
                    <p class="text-muted small mb-0">Authorized suppliers for purchase order issuance</p>
                </div>
                <button type="button" class="btn btn-primary rounded-3 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#createSupplierModal">
                    + Register New Supplier
                </button>
            </div>

            <!-- Search Bar -->
            <form method="GET" action="{{ route('procurement.suppliers.index') }}" class="row g-2 mb-4">
                <div class="col-12 col-md-9">
                    <input type="text" name="search" class="form-control rounded-3" placeholder="Search Supplier Name, Code, GSTIN, Email..." value="{{ request('search') }}">
                </div>
                <div class="col-12 col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary rounded-3 w-100 fw-semibold">Search</button>
                    <a href="{{ route('procurement.suppliers.index') }}" class="btn btn-outline-secondary rounded-3">Reset</a>
                </div>
            </form>

            @if($suppliers->isEmpty())
                <x-empty-state title="No Suppliers Registered" message="Click '+ Register New Supplier' to add vendor records to the master catalog." />
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-3">
                        <thead>
                            <tr class="text-muted small">
                                <th>Code</th>
                                <th>Supplier Name</th>
                                <th>GSTIN / Tax ID</th>
                                <th>Payment Terms</th>
                                <th>Rating</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                                <tr>
                                    <td><code>{{ $supplier->code }}</code></td>
                                    <td>
                                        <div class="fw-bold text-body">{{ $supplier->name }}</div>
                                        <div class="text-muted small" style="font-size: 0.75rem;">{{ $supplier->email }} | {{ $supplier->phone }}</div>
                                    </td>
                                    <td><code>{{ $supplier->tax_number ?? 'N/A' }}</code></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary">{{ $supplier->payment_terms }}</span></td>
                                    <td><span class="badge bg-warning-subtle text-warning-emphasis">★ {{ $supplier->rating }}</span></td>
                                    <td><span class="badge bg-success-subtle text-success">{{ ucfirst($supplier->status) }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div>{{ $suppliers->links() }}</div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Create Supplier -->
<div class="modal fade" id="createSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-translucent">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Register New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('procurement.suppliers.store') }}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Company / Supplier Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" placeholder="e.g. Apex Industrial Global" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">GSTIN / Tax ID</label>
                            <input type="text" name="tax_number" class="form-control rounded-3" placeholder="e.g. GSTIN27AAACA1234F1Z5">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Payment Terms</label>
                            <select name="payment_terms" class="form-select rounded-3">
                                <option value="Net 15">Net 15 Days</option>
                                <option value="Net 30" selected>Net 30 Days</option>
                                <option value="Net 45">Net 45 Days</option>
                                <option value="Advance">Advance Payment</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="orders@supplier.com">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="+91 98200 11223">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 mt-3">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold">Register Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
