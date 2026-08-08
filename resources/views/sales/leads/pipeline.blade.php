@extends('layouts.app')

@section('title', 'Visual Opportunity Pipeline - CRM Workspace')

@section('content')
<div class="d-flex gap-4">
    <!-- Sales Workspace Sidebar -->
    <x-sales-sidebar activeTab="leads.pipeline" />

    <!-- Main Content Area -->
    <div class="flex-grow-1">

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

        <div id="pipelineToast" class="alert alert-success border-0 rounded-3 shadow-sm mb-4 d-none"></div>

        <!-- Top Header Card -->
        <div class="card p-4 rounded-4 shadow-sm border-translucent mb-4 bg-body">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h3 class="fw-bold text-body mb-0">Visual Opportunity Pipeline</h3>
                    <p class="text-muted small mb-0 mt-1">Real-time Sales Pipeline, Stage Transitions & Revenue Forecasts</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('sales.leads.index') }}" class="btn btn-outline-secondary rounded-3">Lead Directory</a>
                    <button type="button" class="btn btn-warning rounded-3 fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#createLeadModal">
                        + Add New Lead
                    </button>
                </div>
            </div>
        </div>

        <!-- 7-Stage Kanban Pipeline Grid -->
        <div class="row g-3 overflow-x-auto pb-4" style="min-width: 1200px;">
            @php
                $stageLabels = [
                    'new' => ['name' => 'New Leads', 'color' => 'border-primary', 'badge' => 'bg-primary'],
                    'contacted' => ['name' => 'Contacted', 'color' => 'border-info', 'badge' => 'bg-info'],
                    'qualified' => ['name' => 'Qualified', 'color' => 'border-warning', 'badge' => 'bg-warning text-dark'],
                    'proposal' => ['name' => 'Proposal Sent', 'color' => 'border-purple', 'badge' => 'bg-dark'],
                    'negotiation' => ['name' => 'Negotiation', 'color' => 'border-secondary', 'badge' => 'bg-secondary'],
                    'won' => ['name' => 'Won / Converted', 'color' => 'border-success', 'badge' => 'bg-success'],
                    'lost' => ['name' => 'Lost', 'color' => 'border-danger', 'badge' => 'bg-danger'],
                ];
            @endphp

            @foreach($stages as $stageKey)
                @php
                    $stageInfo = $stageLabels[$stageKey] ?? ['name' => ucfirst($stageKey), 'color' => 'border-secondary', 'badge' => 'bg-secondary'];
                    $stageLeads = $leadsByStage[$stageKey] ?? collect();
                    $stageRev = $stageLeads->sum('expected_revenue');
                @endphp
                <div class="col" style="min-width: 250px; max-width: 320px;">
                    <div class="card bg-body-tertiary rounded-4 border-top border-4 {{ $stageInfo['color'] }} shadow-sm h-100 p-2">
                        <div class="d-flex align-items-center justify-content-between mb-2 p-2 border-bottom border-translucent">
                            <span class="fw-bold text-body small">{{ $stageInfo['name'] }}</span>
                            <span class="badge {{ $stageInfo['badge'] }} rounded-pill" id="stage-count-{{ $stageKey }}">{{ $stageLeads->count() }}</span>
                        </div>
                        <div class="small text-muted px-2 mb-2">Value: <strong class="text-body" id="stage-val-{{ $stageKey }}">₹{{ number_format((float)$stageRev, 2) }}</strong></div>

                        <div class="d-flex flex-column gap-2" id="stage-container-{{ $stageKey }}" style="min-height: 400px; max-height: 700px; overflow-y: auto;">
                            @forelse($stageLeads as $lead)
                                <div class="card p-3 rounded-3 border-translucent shadow-xs bg-body hover-shadow" id="lead-card-{{ $lead->id }}">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">{{ $lead->lead_number }}</span>
                                        <span class="badge bg-light text-dark border">{{ $lead->probability }}% Prob</span>
                                    </div>
                                    <h6 class="fw-bold text-body mb-1 text-truncate">{{ $lead->company_name }}</h6>
                                    <div class="small text-muted mb-2">Contact: {{ $lead->contact_person }}</div>
                                    
                                    <div class="d-flex align-items-baseline justify-content-between mb-2">
                                        <span class="fw-black text-success">₹{{ number_format((float)$lead->expected_revenue, 2) }}</span>
                                        <span class="small text-muted">{{ $lead->salesperson->name ?? 'Unassigned' }}</span>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between border-top border-translucent pt-2">
                                        <a href="{{ route('sales.leads.show', $lead->id) }}" class="btn btn-link btn-sm p-0 text-primary fw-bold text-decoration-none">View 360° Profile &rarr;</a>
                                        
                                        <div class="dropdown">
                                            <button class="btn btn-outline-secondary btn-sm px-1 py-0 rounded-2 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="font-size: 0.7rem;">Move</button>
                                            <ul class="dropdown-menu dropdown-menu-end small">
                                                @foreach($stages as $nextSt)
                                                    @if($nextSt !== $stageKey)
                                                        <li>
                                                            <form method="POST" action="{{ route('sales.leads.status', $lead->id) }}">
                                                                @csrf
                                                                <input type="hidden" name="status" value="{{ $nextSt }}">
                                                                <button type="submit" class="dropdown-item">Move to {{ ucfirst($nextSt) }}</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted small empty-placeholder">No leads in this stage.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

<!-- Modal: Create New Lead -->
<div class="modal fade" id="createLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-translucent">
            <form method="POST" action="{{ route('sales.leads.store') }}" id="createLeadForm">
                @csrf
                <div class="modal-header border-bottom p-3">
                    <h6 class="modal-title fw-bold">Create New Sales Lead</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div id="leadModalError" class="alert alert-danger d-none mb-3"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control form-control-sm" required placeholder="e.g. Apex Aquatech Ltd">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Contact Person <span class="text-danger">*</span></label>
                            <input type="text" name="contact_person" class="form-control form-control-sm" required placeholder="e.g. Rajesh Kumar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-sm" placeholder="+91 9876543210">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Email</label>
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="info@apexaqua.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Lead Source</label>
                            <select name="source" class="form-select form-select-sm">
                                <option value="website">Website</option>
                                <option value="referral">Referral</option>
                                <option value="exhibition">Exhibition</option>
                                <option value="cold_call">Cold Call</option>
                                <option value="partner">Partner</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Industry</label>
                            <input type="text" name="industry" class="form-control form-control-sm" placeholder="e.g. Manufacturing">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Priority Level</label>
                            <select name="priority" class="form-select form-select-sm">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Expected Revenue (₹)</label>
                            <input type="number" name="expected_revenue" class="form-control form-control-sm" value="100000" step="0.01">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Probability (%)</label>
                            <input type="number" name="probability" class="form-control form-control-sm" value="50" min="0" max="100">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Assigned Salesperson</label>
                            <select name="salesperson_id" class="form-select form-select-sm">
                                <option value="">-- Select Representative --</option>
                                @foreach($salespeople as $sp)
                                    <option value="{{ $sp->id }}">{{ $sp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Territory / Region</label>
                            <select name="territory_id" class="form-select form-select-sm">
                                <option value="">-- Select Territory --</option>
                                @foreach($territories as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Expected Closing Date</label>
                            <input type="date" name="expected_closing_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Remarks / Internal Notes</label>
                            <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Initial requirement notes..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3">
                    <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-3 fw-bold text-dark px-4" id="btnSaveLead">Create Lead & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('createLeadForm');
    var errBox = document.getElementById('leadModalError');
    var toast = document.getElementById('pipelineToast');
    var modalEl = document.getElementById('createLeadModal');

    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        errBox.classList.add('d-none');
        errBox.textContent = '';

        var btn = document.getElementById('btnSaveLead');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving Lead...';

        var formData = new FormData(form);

        fetch("{{ route('sales.leads.store') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(function(res) {
            return res.json().then(function(data) {
                return { status: res.status, data: data };
            });
        })
        .then(function(resObj) {
            btn.disabled = false;
            btn.textContent = 'Create Lead & Save';

            if (!resObj.data.success) {
                var msg = resObj.data.message || 'Validation failed. Please check inputs.';
                if (resObj.data.errors) {
                    var errs = Object.values(resObj.data.errors).flat();
                    msg = errs.join(' ');
                }
                errBox.textContent = '⚠️ ' + msg;
                errBox.classList.remove('d-none');
                return;
            }

            // SUCCESS!
            var lead = resObj.data.lead;

            // Close Modal
            var bsModal = bootstrap.Modal.getInstance(modalEl);
            if (bsModal) {
                bsModal.hide();
            }

            // Reset Form
            form.reset();

            // Display Toast Alert
            toast.textContent = "✔ Lead Created Successfully! ID: " + lead.lead_number + " | Company: " + lead.company_name + " | Salesperson: " + lead.salesperson_name + " | Stage: New Lead";
            toast.classList.remove('d-none');

            // Inject Lead Card into 'New Leads' Stage Column
            var container = document.getElementById('stage-container-new');
            if (container) {
                // Remove empty placeholder if any
                var empty = container.querySelector('.empty-placeholder');
                if (empty) empty.remove();

                var showUrl = "{{ url('/sales/leads') }}/" + lead.id;

                var cardHtml = `
                    <div class="card p-3 rounded-3 border-translucent shadow-xs bg-body hover-shadow" id="lead-card-${lead.id}">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <span class="badge bg-secondary font-monospace" style="font-size: 0.65rem;">${lead.lead_number}</span>
                            <span class="badge bg-light text-dark border">${lead.probability}% Prob</span>
                        </div>
                        <h6 class="fw-bold text-body mb-1 text-truncate">${lead.company_name}</h6>
                        <div class="small text-muted mb-2">Contact: ${lead.contact_person}</div>
                        
                        <div class="d-flex align-items-baseline justify-content-between mb-2">
                            <span class="fw-black text-success">₹${parseFloat(lead.expected_revenue).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
                            <span class="small text-muted">${lead.salesperson_name}</span>
                        </div>

                        <div class="d-flex align-items-center justify-content-between border-top border-translucent pt-2">
                            <a href="${showUrl}" class="btn btn-link btn-sm p-0 text-primary fw-bold text-decoration-none">View 360° Profile &rarr;</a>
                            <span class="badge bg-primary text-white">New Lead</span>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('afterbegin', cardHtml);

                // Update Count Badge
                var countBadge = document.getElementById('stage-count-new');
                if (countBadge) {
                    var currentCount = parseInt(countBadge.textContent) || 0;
                    countBadge.textContent = currentCount + 1;
                }
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = 'Create Lead & Save';
            errBox.textContent = '⚠️ Network or server error occurred. Please try again.';
            errBox.classList.remove('d-none');
            console.error('Lead store error:', err);
        });
    });
});
</script>
@endpush
@endsection
