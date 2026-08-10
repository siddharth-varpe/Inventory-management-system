@extends('driver-terminal.layouts.app')

@section('title', 'My Deliveries')

@section('content')
<div class="row g-3">
    <div class="col-12">
        <h5 class="fw-extrabold text-white mb-2">My Deliveries</h5>
        <div class="card bg-slate-800 border-slate-700 rounded-3 p-3">
            <p class="text-secondary small mb-0">
                ℹ️ <strong>Phase 0 Architecture:</strong> Deliveries list view foundation. Active deliveries assigned to your Driver ID will be rendered here upon Phase 1 rollout.
            </p>
        </div>
    </div>
</div>
@endsection
