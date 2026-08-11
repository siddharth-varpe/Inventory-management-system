@extends('driver-terminal.layouts.app')

@section('title', 'Driver Authenticated')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-8 col-md-6">
        <div class="card bg-slate-800 border-slate-700 rounded-4 p-4 shadow-lg text-center">
            <div class="mb-3">
                <span class="display-4">🛡️</span>
            </div>
            <h4 class="fw-extrabold text-white mb-1">DRIVER TERMINAL</h4>
            <p class="text-secondary small mb-4">Welcome, {{ $currentDriver->driver_name ?? 'Driver' }}</p>

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

            <div class="alert alert-success border-success-subtle text-center small mb-4">
                ✓ Driver login successful.
            </div>

            <form action="{{ route('driver-terminal.logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold rounded-3 shadow">
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
