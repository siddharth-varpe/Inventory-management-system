@extends('layouts.app')

@section('title', $customer->company_name . ' - Customer 360° Profile')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="customers" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

        <!-- Top Profile Banner Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary fw-mono px-2.5 py-1 rounded-pill">{{ $customer->customer_code }}</span>
                        <span class="badge bg-secondary-subtle text-secondary text-uppercase">{{ $customer->customer_type }}</span>
                        @if($customer->status === 'active')
                            <span class="badge bg-success-subtle text-success">Active</span>
                        @elseif($customer->status === 'blocked')
                            <span class="badge bg-danger-subtle text-danger">Blocked Account</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning">{{ ucfirst($customer->status) }}</span>
                        @endif
                    </div>
                    <h2 class="fw-bold text-body mb-0">{{ $customer->company_name }}</h2>
                    <p class="text-muted small mb-0 mt-1">
                        @if($customer->gst_number) 🏢 GSTIN: <code>{{ $customer->gst_number }}</code> | @endif
                        @if($customer->email) ✉️ {{ $customer->email }} | @endif
                        @if($customer->phone) 📞 {{ $customer->phone }} | @endif
                        📍 Territory: {{ $customer->territory->name ?? 'Global' }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('sales.customers.index') }}" class="btn btn-outline-secondary rounded-3 btn-sm">← Back to Master</a>
                </div>
            </div>
        </div>

        <!-- Tabbed 360 Workspace -->
        <div class="card rounded-4 border-translucent shadow-sm bg-body">
            <div class="card-header bg-transparent border-bottom border-translucent p-0">
                <ul class="nav nav-tabs border-0 px-3 pt-2" id="customerTabs" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active fw-semibold" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">Overview & Profile</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="addresses-tab" data-bs-toggle="tab" data-bs-target="#addresses" type="button">Addresses ({{ $customer->addresses->count() }})</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" type="button">Contacts ({{ $customer->contacts->count() }})</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">Documents ({{ $customer->documents->count() }})</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link fw-semibold" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button">Notes ({{ $customer->notes->count() }})</button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-4">
                <div class="tab-content" id="customerTabsContent">

                    <!-- TAB 1: OVERVIEW -->
                    <div class="tab-pane fade show active" id="overview">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold text-body mb-3 border-bottom pb-2">Business Master Classification</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td class="text-muted w-40">Display Name:</td><td class="fw-semibold">{{ $customer->display_name ?? $customer->company_name }}</td></tr>
                                    <tr><td class="text-muted">Customer Group:</td><td class="fw-semibold">{{ $customer->group->name ?? 'None' }}</td></tr>
                                    <tr><td class="text-muted">Customer Category:</td><td class="fw-semibold">{{ $customer->category->name ?? 'Standard' }}</td></tr>
                                    <tr><td class="text-muted">Assigned Territory:</td><td class="fw-semibold">{{ $customer->territory->name ?? 'Global' }} ({{ $customer->territory->region ?? '' }})</td></tr>
                                    <tr><td class="text-muted">Assigned Salesperson:</td><td class="fw-semibold">{{ $customer->salesperson->name ?? 'Unassigned' }}</td></tr>
                                    <tr><td class="text-muted">GST Number:</td><td class="fw-mono">{{ $customer->gst_number ?? 'N/A' }}</td></tr>
                                    <tr><td class="text-muted">PAN Number:</td><td class="fw-mono">{{ $customer->pan_number ?? 'N/A' }}</td></tr>
                                    <tr><td class="text-muted">Website:</td><td><a href="{{ $customer->website }}" target="_blank">{{ $customer->website ?? 'N/A' }}</a></td></tr>
                                </table>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold text-body mb-3 border-bottom pb-2">Commercial & Contact Profile</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td class="text-muted w-40">Payment Terms:</td><td class="fw-bold text-primary">{{ $customer->payment_term ?? 'Net 30 Days' }}</td></tr>
                                    <tr><td class="text-muted">Contact Person:</td><td class="fw-semibold">{{ $customer->contact_person ?? 'N/A' }}</td></tr>
                                    <tr><td class="text-muted">Primary Email:</td><td class="fw-semibold">{{ $customer->email ?? 'N/A' }}</td></tr>
                                    <tr><td class="text-muted">Primary Phone:</td><td class="fw-semibold">{{ $customer->phone ?? 'N/A' }}</td></tr>
                                    <tr><td class="text-muted">Preferred Channel:</td><td><span class="badge bg-info-subtle text-info text-uppercase">{{ $customer->preferred_communication_channel ?? 'EMAIL' }}</span></td></tr>
                                    <tr><td class="text-muted">Account Status:</td><td>
                                        @if($customer->status === 'active')
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @elseif($customer->status === 'blocked')
                                            <span class="badge bg-danger-subtle text-danger">Blocked</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning">{{ ucfirst($customer->status) }}</span>
                                        @endif
                                    </td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ADDRESSES -->
                    <div class="tab-pane fade" id="addresses">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-body mb-0">Customer Delivery & Billing Addresses</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#addAddressModal">+ Add Address</button>
                        </div>
                        <div class="row g-3">
                            @forelse($customer->addresses as $addr)
                                <div class="col-md-6">
                                    <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-secondary text-uppercase">{{ $addr->type }}</span>
                                            @if($addr->is_primary) <span class="badge bg-primary">PRIMARY</span> @endif
                                        </div>
                                        <div class="fw-bold text-body">{{ $addr->address_line_1 }}</div>
                                        @if($addr->address_line_2) <div class="small text-muted">{{ $addr->address_line_2 }}</div> @endif
                                        <div class="small text-muted mt-1">{{ $addr->city }}, {{ $addr->state }} - {{ $addr->postal_code }}, {{ $addr->country }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted py-4 text-center">No addresses registered for this customer account.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB 3: CONTACTS -->
                    <div class="tab-pane fade" id="contacts">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-body mb-0">Contact Directory</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#addContactModal">+ Add Contact Person</button>
                        </div>
                        <div class="row g-3">
                            @forelse($customer->contacts as $cnt)
                                <div class="col-md-4">
                                    <div class="p-3 bg-body-tertiary rounded-3 border border-translucent h-100">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-info-subtle text-info text-uppercase">{{ $cnt->type }}</span>
                                            <span class="small text-muted">{{ ucfirst($cnt->preferred_contact_method) }}</span>
                                        </div>
                                        <div class="fw-bold text-body">{{ $cnt->name }}</div>
                                        <div class="small text-muted">{{ $cnt->designation ?? 'N/A' }} ({{ $cnt->department ?? 'General' }})</div>
                                        <div class="small mt-2">
                                            @if($cnt->phone) 📞 {{ $cnt->phone }}<br> @endif
                                            @if($cnt->email) ✉️ {{ $cnt->email }} @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted py-4 text-center">No contact persons registered.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- TAB 4: DOCUMENTS -->
                    <div class="tab-pane fade" id="documents">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-body mb-0">Customer Documents & Legal Files</h6>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-3" data-bs-toggle="modal" data-bs-target="#uploadDocModal">+ Attach Document</button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Document Title</th>
                                        <th>Type</th>
                                        <th>File Path</th>
                                        <th>Uploaded By</th>
                                        <th>Timestamp</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($customer->documents as $doc)
                                        <tr>
                                            <td class="fw-bold text-body">{{ $doc->title }}</td>
                                            <td><span class="badge bg-secondary text-uppercase">{{ $doc->document_type }}</span></td>
                                            <td class="fw-mono small text-muted">{{ $doc->file_path }}</td>
                                            <td>{{ $doc->uploader->name ?? 'System' }}</td>
                                            <td class="small text-muted">{{ $doc->created_at->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">No documents uploaded.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TAB 5: NOTES -->
                    <div class="tab-pane fade" id="notes">
                        <form method="POST" action="{{ route('sales.customers.notes.store', $customer->id) }}" class="mb-4">
                            @csrf
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <select name="type" class="form-select rounded-3" required>
                                        <option value="internal">Internal Note</option>
                                        <option value="sales">Sales Note</option>
                                        <option value="support">Support Note</option>
                                        <option value="management">Management Note</option>
                                    </select>
                                </div>
                                <div class="col-md-7">
                                    <input type="text" name="note" class="form-control rounded-3" required placeholder="Type internal note for this account...">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-warning w-100 rounded-3 fw-bold text-dark">+ Add Note</button>
                                </div>
                            </div>
                        </form>

                        <div class="list-group list-group-flush">
                            @forelse($customer->notes as $note)
                                <div class="list-group-item bg-transparent px-0 py-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge bg-secondary text-uppercase">{{ $note->type }}</span>
                                        <span class="small text-muted">{{ $note->created_at->format('d M Y, h:i A') }} by {{ $note->author->name ?? 'User' }}</span>
                                    </div>
                                    <div class="text-body fw-semibold">{{ $note->note }}</div>
                                </div>
                            @empty
                                <div class="text-muted py-4 text-center">No internal notes logged.</div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Add Address -->
<div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.customers.addresses.store', $customer->id) }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Add Address</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <div class="mb-2"><label class="form-label small fw-bold">Type</label><select name="type" class="form-select"><option value="billing">Billing</option><option value="shipping">Shipping</option><option value="branch">Branch</option><option value="warehouse_delivery">Warehouse Delivery</option></select></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Address Line 1</label><input type="text" name="address_line_1" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Address Line 2</label><input type="text" name="address_line_2" class="form-control"></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small fw-bold">City</label><input type="text" name="city" class="form-control" required></div>
                        <div class="col-6"><label class="form-label small fw-bold">State</label><input type="text" name="state" class="form-control" required></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label small fw-bold">Postal Code</label><input type="text" name="postal_code" class="form-control" required></div>
                        <div class="col-6"><label class="form-label small fw-bold">Country</label><input type="text" name="country" class="form-control" value="India" required></div>
                    </div>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-primary rounded-3">Save Address</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Add Contact -->
<div class="modal fade" id="addContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.customers.contacts.store', $customer->id) }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Add Contact Person</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <div class="mb-2"><label class="form-label small fw-bold">Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small fw-bold">Designation</label><input type="text" name="designation" class="form-control"></div>
                        <div class="col-6"><label class="form-label small fw-bold">Department</label><input type="text" name="department" class="form-control"></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small fw-bold">Phone</label><input type="text" name="phone" class="form-control"></div>
                        <div class="col-6"><label class="form-label small fw-bold">Email</label><input type="email" name="email" class="form-control"></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label small fw-bold">Contact Role</label><select name="type" class="form-select"><option value="primary">Primary</option><option value="accounts">Accounts</option><option value="purchase">Purchase</option><option value="technical">Technical</option><option value="management">Management</option></select></div>
                        <div class="col-6"><label class="form-label small fw-bold">Preferred Method</label><select name="preferred_contact_method" class="form-select"><option value="email">Email</option><option value="phone">Phone</option><option value="whatsapp">WhatsApp</option></select></div>
                    </div>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-primary rounded-3">Save Contact</button></div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Upload Document -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.customers.documents.store', $customer->id) }}">
                @csrf
                <div class="modal-header border-bottom p-3"><h6 class="modal-title fw-bold">Attach Document</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body p-3">
                    <div class="mb-2"><label class="form-label small fw-bold">Document Type</label><select name="document_type" class="form-select"><option value="gst_cert">GST Certificate</option><option value="pan_card">PAN Card</option><option value="trade_license">Trade License</option><option value="agreement">Agreement</option><option value="contract">Contract</option></select></div>
                    <div class="mb-2"><label class="form-label small fw-bold">Document Title</label><input type="text" name="title" class="form-control" required placeholder="e.g. GST Registration Certificate"></div>
                    <div class="mb-2"><label class="form-label small fw-bold">File Path / Reference</label><input type="text" name="file_path" class="form-control" required value="/storage/customer_docs/gst_cert_{{ $customer->id }}.pdf"></div>
                </div>
                <div class="modal-footer p-3"><button type="submit" class="btn btn-primary rounded-3">Attach File</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
