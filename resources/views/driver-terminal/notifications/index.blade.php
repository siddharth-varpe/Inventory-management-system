@extends('driver-terminal.layouts.app')

@section('title', 'Driver Notifications — Driver Terminal')

@section('content')
<div class="vstack gap-4">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-black text-dark mb-1 fs-4">Notifications</h5>
            <p class="text-muted small mb-0" style="font-size: 0.82rem;">Dispatch Alerts &amp; Terminal System Messages</p>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill font-monospace fw-bold" style="font-size: 0.75rem;">
            ● 3 NEW
        </span>
    </div>

    <div class="card bg-white border border-translucent rounded-4 p-4 shadow-sm">
        <div class="d-flex align-items-start gap-3">
            <div class="rounded-circle bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                🔔
            </div>
            <div>
                <h6 class="fw-bold text-dark mb-1 fs-6">Dispatch Assignment Notice</h6>
                <p class="text-secondary small mb-1 lh-base" style="font-size: 0.82rem;">
                    You have been assigned to Delivery Order #SO-2026-00001 (Destination: Primary Customer Address).
                </p>
                <span class="text-muted micro-text font-monospace" style="font-size: 0.72rem;">Today at 09:30 AM</span>
            </div>
        </div>
    </div>
</div>
@endsection
