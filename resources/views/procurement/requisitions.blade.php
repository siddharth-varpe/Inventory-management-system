@extends('layouts.app')

@section('title', 'Purchase Requisitions - Order Supplies PMS')

@section('header', 'Purchase Requisitions Workspace')
@section('subheader', 'Capture internal department demand, review requisition items, and process enterprise approvals.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('procurement.dashboard') }}" class="text-decoration-none">Order Supplies</a></li>
    <li class="breadcrumb-item active" aria-current="page">Requisitions</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-procurement-sidebar activeTab="requisitions" />
    </div>

    <!-- Right Column: Split Master-Detail Layout -->
    <div class="col-12 col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold text-body mb-0">Requisition Work Desk</h5>
            <button type="button" class="btn btn-primary rounded-3 px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#createPrModal">
                + New Purchase Requisition
            </button>
        </div>

        <div class="row g-4">
            <!-- Left Master List (Requisition Queue) -->
            <div class="col-12 col-xl-5">
                <div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
                    <h6 class="fw-bold text-body mb-3">Requisition Queue</h6>
                    @if($requisitions->isEmpty())
                        <x-empty-state title="Queue Clear" message="No purchase requisitions found." />
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($requisitions as $pr)
                                @php
                                    $isSelected = $selectedPR && $selectedPR->id === $pr->id;
                                    $prFirstItem = $pr->items->first();
                                    $prProduct = $prFirstItem?->product;
                                @endphp
                                <a href="{{ route('procurement.requisitions.index', ['selected' => $pr->id]) }}" 
                                   class="list-group-item list-group-item-action rounded-3 border mb-2 p-3 {{ $isSelected ? 'border-primary bg-primary-subtle' : 'bg-body' }}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <code class="fw-bold text-primary">{{ $pr->requisition_number }}</code>
                                        <span class="badge bg-warning-subtle text-warning-emphasis">{{ ucfirst($pr->priority) }}</span>
                                    </div>
                                    <!-- Product Name & SKU Display -->
                                    <div class="fw-bold text-body small">
                                        {{ $prProduct->name ?? 'Product Item' }}
                                        @if($prProduct && $prProduct->sku)
                                            <span class="text-muted font-monospace small">({{ $prProduct->sku }})</span>
                                        @endif
                                    </div>
                                    <div class="text-muted small mb-1">{{ $pr->purpose }}</div>
                                    <div class="d-flex justify-content-between align-items-center text-muted small" style="font-size: 0.75rem;">
                                        <span>{{ $pr->created_at->format('Y-m-d') }}</span>
                                        <span class="badge {{ $pr->status === 'rejected' ? 'bg-danger-subtle text-danger' : ($pr->status === 'pending_approval' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success') }}">
                                            {{ strtoupper(str_replace('_', ' ', $pr->status)) }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Detail View (Selected Requisition) -->
            <div class="col-12 col-xl-7">
                <div class="card p-4 rounded-4 shadow-sm border-translucent bg-body">
                    @if(!$selectedPR)
                        <x-empty-state title="Select Requisition" message="Choose a purchase requisition from the left queue to view details." />
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <h5 class="fw-bold text-body mb-1">{{ $selectedPR->requisition_number }}</h5>
                                <span class="text-muted small">Requested on {{ $selectedPR->created_at->format('M d, Y \a\t H:i') }}</span>
                            </div>
                            <span class="badge fs-6 px-3 py-2 rounded-pill {{ $selectedPR->status === 'rejected' ? 'bg-danger' : ($selectedPR->status === 'pending_approval' ? 'bg-warning text-dark' : 'bg-success') }}">
                                {{ strtoupper(str_replace('_', ' ', $selectedPR->status)) }}
                            </span>
                        </div>

                        <div class="mb-4">
                            <div class="text-muted small fw-semibold">Purpose / Business Justification</div>
                            <div class="fw-medium text-body mt-1">{{ $selectedPR->purpose }}</div>
                        </div>

                        <!-- Status Banners & Audit Logs -->
                        @if($selectedPR->status === 'rejected')
                            <div class="alert alert-danger rounded-3 p-3 mb-4">
                                <div class="fw-bold mb-1">Requisition Rejected</div>
                                <div class="small"><strong>Reason:</strong> {{ $selectedPR->rejection_reason }}</div>
                                @if($selectedPR->comments)
                                    <div class="small text-muted mt-1"><strong>Comments:</strong> {{ $selectedPR->comments }}</div>
                                @endif
                                <div class="small text-muted mt-1">Rejected by {{ $selectedPR->rejectedBy->name ?? 'Manager' }} on {{ $selectedPR->rejected_at?->format('M d, Y H:i') }}</div>
                            </div>
                        @elseif(in_array($selectedPR->status, ['approved', 'converted_to_po', 'converted_to_rfq']))
                            <div class="alert alert-success rounded-3 p-3 mb-4">
                                <div class="fw-bold mb-1">Requisition Approved</div>
                                <div class="small">Approved by {{ $selectedPR->approvedBy->name ?? 'Administrator' }} on {{ $selectedPR->approved_at?->format('M d, Y H:i') }}</div>
                            </div>
                        @endif

                        <h6 class="fw-bold text-body mb-3">Requisition Line Items</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered align-middle mb-0">
                                <thead class="table-light small text-muted">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>SKU</th>
                                        <th>Qty Requested</th>
                                        <th>Est Unit Cost</th>
                                        <th>Line Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandTotal = 0; @endphp
                                    @foreach($selectedPR->items as $item)
                                        @php
                                            $lineTotal = $item->quantity_requested * (float)$item->estimated_unit_cost;
                                            $grandTotal += $lineTotal;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold text-body">{{ $item->product->name ?? 'Product Item' }}</td>
                                            <td><code>{{ $item->product->sku ?? 'N/A' }}</code></td>
                                            <td class="fw-bold fs-6">{{ $item->quantity_requested }}</td>
                                            <td>₹{{ number_format((float)$item->estimated_unit_cost, 2) }}</td>
                                            <td class="fw-bold">₹{{ number_format($lineTotal, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <th colspan="4" class="text-end fw-bold">Est. Total Amount:</th>
                                        <th class="fw-black text-primary fs-6">₹{{ number_format($grandTotal, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Action Buttons (Strict Visibility Rule: ONLY when status == 'pending_approval') -->
                        @if($selectedPR->status === 'pending_approval')
                            <div class="d-flex gap-2 justify-content-end pt-3 border-top">
                                <!-- Reject Button opens Modal -->
                                <button type="button" class="btn btn-outline-danger rounded-3 fw-bold px-4" data-bs-toggle="modal" data-bs-target="#rejectPrModal">
                                    Reject PR
                                </button>

                                <!-- Approve Form -->
                                <form action="{{ route('procurement.requisitions.approve', $selectedPR->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success rounded-3 px-4 fw-bold shadow-sm">
                                        ✔ Approve & Convert to PO
                                    </button>
                                </form>
                            </div>
                        @else
                            <div class="text-end text-muted small fst-italic pt-2">
                                Approval actions completed for this requisition.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create PR -->
<div class="modal fade" id="createPrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-translucent">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">New Purchase Requisition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('procurement.requisitions.store') }}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-select rounded-3" required>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Quantity Requested <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" class="form-control rounded-3" value="10" min="1" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select rounded-3">
                                <option value="normal" selected>Normal Priority</option>
                                <option value="urgent">Urgent Priority</option>
                                <option value="low">Low Priority</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Business Justification</label>
                            <textarea name="purpose" class="form-control rounded-3" rows="2" placeholder="e.g. Replenishment for Section A stock depletion"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 mt-3">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold">Submit Requisition</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Reject PR Confirmation -->
@if($selectedPR && $selectedPR->status === 'pending_approval')
<div class="modal fade" id="rejectPrModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-translucent">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold text-danger">Reject Purchase Requisition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('procurement.requisitions.reject', $selectedPR->id) }}" method="POST">
                @csrf
                <div class="modal-body py-0">
                    <div class="alert alert-warning small rounded-3 mb-3">
                        Rejecting this Purchase Requisition will stop the procurement workflow. This action is permanent.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control rounded-3" rows="2" placeholder="State explicit reason for rejection..." required minlength="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Additional Comments (Optional)</label>
                        <textarea name="comments" class="form-control rounded-3" rows="2" placeholder="Optional notes for the requesting department..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-3 fw-bold px-4">Confirm Rejection</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
