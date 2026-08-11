@extends('driver-terminal.layouts.app')

@section('title', 'Driver Workspace — Driver Terminal')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-8 col-md-6">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-4 shadow-lg text-center">
            <div class="mb-3">
                <span class="display-4">🛡️</span>
            </div>
            <h4 class="fw-extrabold text-white mb-1">DRIVER TERMINAL</h4>
            <p class="text-secondary small mb-4">Welcome, {{ $currentDriver->driver_name ?? 'Driver' }}</p>

            @if(session('error'))
                <div class="alert alert-danger bg-danger-subtle text-danger border-danger-subtle mb-4 p-3 rounded-3 small fw-bold text-start">
                    ⚠️ {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success bg-success-subtle text-success border-success-subtle mb-4 p-3 rounded-3 small fw-bold text-start">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <div class="bg-dark p-3.5 rounded-3 border border-secondary text-start mb-4">
                <div class="mb-3">
                    <div class="text-secondary micro-text fw-bold text-uppercase" style="letter-spacing: 0.5px;">Driver ID</div>
                    <div class="text-info font-monospace fw-extrabold fs-5 mt-0.5">
                        {{ $currentDriver->driver_code ?? 'N/A' }}
                    </div>
                </div>

                <div class="mb-3">
                    <div class="text-secondary micro-text fw-bold text-uppercase" style="letter-spacing: 0.5px;">Driver Name</div>
                    <div class="text-white fw-bold fs-6 mt-0.5">
                        {{ $currentDriver->driver_name ?? 'N/A' }}
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between pt-2.5 border-top border-secondary">
                    <span class="text-secondary micro-text fw-bold text-uppercase">Status</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-7 fw-bold">
                        ● Authenticated
                    </span>
                </div>
            </div>

            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => strtolower($currentDriver->driver_code)]) }}" class="btn btn-primary btn-lg w-100 fw-bold rounded-3 shadow mb-3 d-flex align-items-center justify-content-center gap-2">
                <span>MY DELIVERIES ({{ $assignedCount + $dispatchedCount }})</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"/>
                </svg>
            </a>

            <form action="{{ route('driver-terminal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-lg w-100 fw-bold rounded-3">
                    LOGOUT
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .micro-text {
        font-size: 0.7rem;
    }
    .fs-7 {
        font-size: 0.75rem;
    }
</style>
@endsection
