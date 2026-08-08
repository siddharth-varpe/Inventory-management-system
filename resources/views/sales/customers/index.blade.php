@extends('layouts.app')

@section('title', 'Customer Master - Sales & CRM Portal')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="customers" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Header & Search Bar -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold text-body mb-0">Customer Master Directory</h3>
                    <p class="text-muted small mb-0 mt-1">B2B Customer Accounts, Contacts, Groupings, and Territory Maps</p>
                </div>
                <div>
                    <button type="button" class="btn btn-warning rounded-3 fw-bold d-flex align-items-center gap-2 px-3 py-2 text-dark" data-bs-toggle="modal" data-bs-target="#registerCustomerModal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-plus-fill" viewBox="0 0 16 16">
                            <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                            <path fill-rule="evenodd" d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5"/>
                        </svg>
                        <span>Register New Customer</span>
                    </button>
                </div>
            </div>

            <!-- Filters Bar -->
            <form method="GET" action="{{ route('sales.customers.index') }}" class="row g-2 mt-3 pt-3 border-top border-translucent">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control form-control-sm rounded-3" placeholder="Search Code, Name, GST, PAN, Phone..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="customer_type" class="form-select form-select-sm rounded-3">
                        <option value="">All Customer Types</option>
                        <option value="retail" {{ request('customer_type') === 'retail' ? 'selected' : '' }}>Retail</option>
                        <option value="dealer" {{ request('customer_type') === 'dealer' ? 'selected' : '' }}>Dealer</option>
                        <option value="distributor" {{ request('customer_type') === 'distributor' ? 'selected' : '' }}>Distributor</option>
                        <option value="corporate" {{ request('customer_type') === 'corporate' ? 'selected' : '' }}>Corporate</option>
                        <option value="government" {{ request('customer_type') === 'government' ? 'selected' : '' }}>Government</option>
                        <option value="oem" {{ request('customer_type') === 'oem' ? 'selected' : '' }}>OEM</option>
                        <option value="institution" {{ request('customer_type') === 'institution' ? 'selected' : '' }}>Institution</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                        <option value="blacklisted" {{ request('status') === 'blacklisted' ? 'selected' : '' }}>Blacklisted</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="customer_group_id" class="form-select form-select-sm rounded-3">
                        <option value="">All Groups</option>
                        @foreach($groups as $g)
                            <option value="{{ $g->id }}" {{ request('customer_group_id') == $g->id ? 'selected' : '' }}>{{ $g->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="territory_id" class="form-select form-select-sm rounded-3">
                        <option value="">All Territories</option>
                        @foreach($territories as $t)
                            <option value="{{ $t->id }}" {{ request('territory_id') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-secondary btn-sm w-100 rounded-3">Filter</button>
                    @if(request()->hasAny(['search', 'customer_type', 'status', 'customer_group_id', 'territory_id']))
                        <a href="{{ route('sales.customers.index') }}" class="btn btn-outline-danger btn-sm rounded-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Customer Master Datatable -->
        <div class="card rounded-4 border-translucent shadow-sm bg-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Company Name</th>
                            <th>Type</th>
                            <th>Group / Category</th>
                            <th>Territory</th>
                            <th>Salesperson</th>
                            <th>Status</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $c)
                            <tr>
                                <td class="ps-3 fw-mono text-primary fw-bold">{{ $c->customer_code }}</td>
                                <td>
                                    <div class="fw-bold text-body">{{ $c->company_name }}</div>
                                    <div class="small text-muted">
                                        @if($c->gst_number) GST: <code>{{ $c->gst_number }}</code> @endif
                                        @if($c->phone) | 📞 {{ $c->phone }} @endif
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $c->customer_type }}</span></td>
                                <td>
                                    <div class="small fw-semibold text-body">{{ $c->group->name ?? 'Unassigned' }}</div>
                                    <div class="small text-muted">{{ $c->category->name ?? 'Standard' }}</div>
                                </td>
                                <td>
                                    <div class="small fw-semibold text-body">{{ $c->territory->name ?? 'Global' }}</div>
                                    <div class="small text-muted">{{ $c->territory->region ?? '' }}</div>
                                </td>
                                <td>{{ $c->salesperson->name ?? 'Unassigned' }}</td>
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
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="fs-2 mb-2">👥</div>
                                    <div class="fw-bold">No customer accounts found matching criteria.</div>
                                    <div class="small text-muted">Click "Register New Customer" to add your first customer master account.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
                <div class="card-footer bg-transparent border-top border-translucent p-3">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

    </div>
</div>

<!-- Modal: Register New Customer -->
<div class="modal fade" id="registerCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-translucent shadow-lg rounded-4">
            <form method="POST" action="{{ route('sales.customers.store') }}">
                @csrf
                <div class="modal-header border-bottom border-translucent p-4">
                    <h5 class="modal-title fw-bold text-body">Register New Customer Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control rounded-3" required placeholder="e.g. Apex Aquatech Solutions Ltd">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Customer Type <span class="text-danger">*</span></label>
                            <select name="customer_type" class="form-select rounded-3" required>
                                <option value="retail">Retail</option>
                                <option value="dealer" selected>Dealer</option>
                                <option value="distributor">Distributor</option>
                                <option value="corporate">Corporate</option>
                                <option value="government">Government</option>
                                <option value="oem">OEM</option>
                                <option value="institution">Institution</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">GST Number</label>
                            <input type="text" name="gst_number" class="form-control rounded-3" placeholder="e.g. 27AAACG1234F1Z5">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control rounded-3" placeholder="e.g. AAACG1234F">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" placeholder="orders@customer.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control rounded-3" placeholder="+91 98200 11223">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Customer Group</label>
                            <select name="customer_group_id" class="form-select rounded-3">
                                <option value="">None</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Customer Category</label>
                            <select name="customer_category_id" class="form-select rounded-3">
                                <option value="">None</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Territory</label>
                            <select name="territory_id" class="form-select rounded-3">
                                <option value="">None</option>
                                @foreach($territories as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->region }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Assigned Salesperson</label>
                            <select name="salesperson_id" class="form-select rounded-3">
                                <option value="">Unassigned</option>
                                @foreach($salespersons as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Account Status</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="blocked">Blocked</option>
                                <option value="blacklisted">Blacklisted</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-translucent p-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark px-4">Create Customer Master</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
