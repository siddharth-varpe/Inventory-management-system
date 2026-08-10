@extends('driver-terminal.layouts.app')

@section('title', 'Driver Terminal')

@section('content')
<div class="row g-3">
    <div class="col-12">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-4 text-center">
            <div class="mb-2">
                <span class="fs-1">🚛</span>
            </div>
            <h4 class="fw-bold text-white mb-1">DRIVER TERMINAL</h4>
            <p class="text-secondary small mb-3">Phase 1 — Secure Driver Authentication</p>

            <div class="bg-dark p-3 rounded-3 border border-secondary text-start mb-4">
                <div class="row g-2">
                    <div class="col-4 text-secondary small">Driver Name:</div>
                    <div class="col-8 text-white fw-bold small">{{ $currentDriver->driver_name ?? auth()->user()->name }}</div>

                    <div class="col-4 text-secondary small">Driver ID:</div>
                    <div class="col-8 text-info font-monospace fw-bold small">{{ $currentDriver->driver_code ?? 'DRV-000001' }}</div>

                    <div class="col-4 text-secondary small">Status:</div>
                    <div class="col-8 small">
                        <span class="badge bg-success">ACTIVE</span>
                    </div>

                    @if(!empty($currentDriver->phone_number))
                    <div class="col-4 text-secondary small">Phone:</div>
                    <div class="col-8 text-light small">{{ $currentDriver->phone_number }}</div>
                    @endif
                </div>
            </div>

            <form action="{{ route('driver-terminal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-lg w-100 fw-bold rounded-3">
                    Sign Out / Logout
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
