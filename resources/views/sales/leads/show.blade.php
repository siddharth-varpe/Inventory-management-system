@extends('layouts.app')

@section('title', 'Lead ' . $lead->lead_number . ' - 360° Profile')

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
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary font-monospace px-2.5 py-1 rounded-pill fs-6">{{ $lead->lead_number }}</span>
                        @if($lead->status === 'won')
                            <span class="badge bg-success fs-6">WON / CONVERTED</span>
                        @elseif($lead->status === 'lost')
                            <span class="badge bg-danger fs-6">LOST</span>
                        @else
                            <span class="badge bg-info fs-6">{{ strtoupper($lead->status) }}</span>
                        @endif
                    </div>
                    <h3 class="fw-bold text-body mb-0">{{ $lead->company_name }}</h3>
                    <p class="text-muted small mb-0 mt-1">
                        Contact: <strong>{{ $lead->contact_person }}</strong> | Phone: {{ $lead->phone ?? 'N/A' }} | Email: {{ $lead->email ?? 'N/A' }}
                    </p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    @if($lead->status !== 'won')
                        <form method="POST" action="{{ route('sales.leads.status', $lead->id) }}">
                            @csrf
                            <input type="hidden" name="status" value="won">
                            <button type="submit" class="btn btn-success rounded-3 fw-bold">Mark as Won & Convert to Customer</button>
                        </form>
                    @endif

                    <a href="{{ route('sales.leads.pipeline') }}" class="btn btn-outline-secondary rounded-3">Pipeline View</a>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- LEFT PANEL: Lead Details & Activity Loggers (60%) -->
            <div class="col-lg-7">
                <!-- Activity Logger Tabs Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body mb-4">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <ul class="nav nav-pills card-header-pills gap-2" id="crmTabs" role="tablist">
                            <li class="nav-item"><button class="nav-link active btn-sm rounded-3" id="act-tab" data-bs-toggle="tab" data-bs-target="#act-content">Log Activity</button></li>
                            <li class="nav-item"><button class="nav-link btn-sm rounded-3" id="flw-tab" data-bs-toggle="tab" data-bs-target="#flw-content">Schedule Follow-up</button></li>
                            <li class="nav-item"><button class="nav-link btn-sm rounded-3" id="mtg-tab" data-bs-toggle="tab" data-bs-target="#mtg-content">Schedule Meeting</button></li>
                        </ul>
                    </div>
                    <div class="card-body p-3">
                        <div class="tab-content">
                            <!-- Log Activity Tab -->
                            <div class="tab-pane fade show active" id="act-content">
                                <form method="POST" action="{{ route('sales.activities.store') }}">
                                    @csrf
                                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Activity Type</label>
                                            <select name="activity_type" class="form-select form-select-sm" required>
                                                <option value="call">Phone Call</option>
                                                <option value="email">Email</option>
                                                <option value="whatsapp">WhatsApp Note</option>
                                                <option value="site_visit">Site Visit</option>
                                                <option value="demo">Product Demo</option>
                                                <option value="note">Internal Note</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold">Subject <span class="text-danger">*</span></label>
                                            <input type="text" name="subject" class="form-control form-control-sm" required placeholder="e.g. Phone discussion on RO Water system quote">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Activity Timestamp</label>
                                            <input type="datetime-local" name="activity_date" class="form-control form-control-sm" value="{{ date('Y-m-d\TH:i') }}" required>
                                        </div>
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold">Description / Remarks</label>
                                            <textarea name="description" class="form-control form-control-sm" rows="2" placeholder="Key outcomes of the discussion..."></textarea>
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button type="submit" class="btn btn-warning btn-sm rounded-3 fw-bold text-dark px-3">+ Save Activity</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Schedule Follow-up Tab -->
                            <div class="tab-pane fade" id="flw-content">
                                <form method="POST" action="{{ route('sales.followups.store') }}">
                                    @csrf
                                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold">Follow-up Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Follow up on commercial discount proposal">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Priority</label>
                                            <select name="priority" class="form-select form-select-sm">
                                                <option value="low">Low</option>
                                                <option value="medium" selected>Medium</option>
                                                <option value="high">High</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Due Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="due_date" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button type="submit" class="btn btn-primary btn-sm rounded-3 fw-bold px-3">+ Schedule Reminder</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Schedule Meeting Tab -->
                            <div class="tab-pane fade" id="mtg-content">
                                <form method="POST" action="{{ route('sales.meetings.store') }}">
                                    @csrf
                                    <input type="hidden" name="lead_id" value="{{ $lead->id }}">
                                    <div class="row g-3">
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold">Meeting Title <span class="text-danger">*</span></label>
                                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="e.g. Onsite Technical Assessment Meeting">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold">Meeting Type</label>
                                            <select name="meeting_type" class="form-select form-select-sm">
                                                <option value="in_person">In Person</option>
                                                <option value="online">Online Call</option>
                                                <option value="site_visit">Site Visit</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Meeting Date & Time <span class="text-danger">*</span></label>
                                            <input type="datetime-local" name="meeting_date" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Location</label>
                                            <input type="text" name="location" class="form-control form-control-sm" placeholder="Client HQ, Mumbai">
                                        </div>
                                        <div class="col-md-12 text-end">
                                            <button type="submit" class="btn btn-dark btn-sm rounded-3 fw-bold px-3">+ Schedule Meeting</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lead Details Card -->
                <div class="card rounded-4 border-translucent shadow-sm bg-body">
                    <div class="card-header bg-transparent border-bottom border-translucent p-3">
                        <h6 class="fw-bold text-body mb-0">Commercial Lead Meta Details</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-6"><span class="text-muted small">Expected Revenue:</span><div class="fw-black text-success fs-5">₹{{ number_format((float)$lead->expected_revenue, 2) }}</div></div>
                            <div class="col-md-6"><span class="text-muted small">Win Probability:</span><div class="fw-bold text-body fs-5">{{ $lead->probability }}%</div></div>
                            <div class="col-md-6"><span class="text-muted small">Salesperson Assigned:</span><div class="fw-semibold text-body">{{ $lead->salesperson->name ?? 'Unassigned' }}</div></div>
                            <div class="col-md-6"><span class="text-muted small">Lead Source:</span><div class="fw-semibold text-body">{{ ucfirst($lead->source) }}</div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT PANEL: Universal Workflow Timeline (40%) -->
            <div class="col-lg-5">
                <div class="card rounded-4 border-translucent shadow-sm bg-body sticky-top" style="top: 80px; z-index: 10;">
                    <div class="card-body p-3">
                        <!-- Global Reusable Universal Workflow Timeline Component -->
                        <x-universal-timeline :model="$lead" />
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
