@extends('driver-terminal.layouts.app')

@section('title', 'Terminal Workspace')

@section('content')
<div class="row g-3">
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-3 p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="fw-bold mb-0 text-white">Driver Terminal Foundation</h5>
                    <span class="text-secondary small font-monospace">Phase 0 Integration Active</span>
                </div>
                <span class="badge bg-primary px-2.5 py-1">PHASE 0</span>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-3 p-3">
            <h6 class="fw-bold text-info mb-2">📋 Architecture Status</h6>
            <ul class="list-unstyled small text-secondary mb-0">
                <li class="mb-1">✅ Driver Master & ID Verified</li>
                <li class="mb-1">✅ Vehicle Assignment Model Verified</li>
                <li class="mb-1">✅ Transport Portal Manual Dispatch Removed</li>
                <li class="mb-1">✅ Driver-Scoped Authorization Middleware Active</li>
                <li>⏳ Active Delivery & Acceptance (Scheduled for Phase 1+)</li>
            </ul>
        </div>
    </div>
</div>
@endsection
