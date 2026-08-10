@extends('driver-terminal.layouts.app')

@section('title', 'Driver Login')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-8 col-md-6">
        <div class="card bg-slate-800 border-slate-700 shadow-lg rounded-4 p-4 text-center">
            <div class="mb-3">
                <span class="display-3">🔑</span>
            </div>
            <h4 class="fw-extrabold text-white mb-1">Driver Terminal Sign In</h4>
            <p class="text-secondary small mb-4">Phase 0 Architectural Authentication Foundation</p>

            <div class="alert alert-info border-info-subtle text-start small mb-4">
                ℹ️ <strong>Phase 0 Foundation Note:</strong> OTP authentication (SMS/WhatsApp) is scheduled for Phase 1. Driver terminals inherit ERP authentication boundaries.
            </div>

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3 text-start">
                    <label class="form-label text-secondary small fw-bold">Driver Email / Mobile</label>
                    <input type="text" name="email" class="form-control form-control-lg bg-dark text-white border-secondary" placeholder="Enter driver credentials..." required>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label text-secondary small fw-bold">Password / Passcode</label>
                    <input type="password" name="password" class="form-control form-control-lg bg-dark text-white border-secondary" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-3 shadow">
                    Sign In to Terminal
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
