@extends('driver-terminal.layouts.app')

@section('title', 'Driver Terminal Authentication')

@section('content')
<div class="row justify-content-center mt-4">
    <div class="col-12 col-sm-8 col-md-6">
        <div class="card bg-slate-800 border-slate-700 shadow-lg rounded-4 p-4 text-center">
            <div class="mb-3">
                <span class="display-3">🚛</span>
            </div>
            <h4 class="fw-extrabold text-white mb-1">Driver Terminal Sign In</h4>
            <p class="text-secondary small mb-4">Enterprise Mobile Terminal Access</p>

            @if ($errors->any())
                <div class="alert alert-danger border-danger-subtle text-start small mb-4">
                    @foreach ($errors->all() as $error)
                        <div>⚠️ {{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success border-success-subtle text-start small mb-4">
                    ✓ {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('driver-terminal.login.post') }}" method="POST">
                @csrf
                <div class="mb-3 text-start">
                    <label class="form-label text-secondary small fw-bold">Driver ID / Email / Phone</label>
                    <input type="text" name="driver_identifier" class="form-control form-control-lg bg-dark text-white border-secondary" placeholder="e.g. DRV-000001 or email..." value="{{ old('driver_identifier') }}" required autofocus>
                </div>
                <div class="mb-4 text-start">
                    <label class="form-label text-secondary small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg bg-dark text-white border-secondary" placeholder="••••••••" required>
                </div>
                <div class="mb-4 text-start form-check">
                    <input type="checkbox" name="remember" class="form-check-input bg-dark border-secondary" id="rememberCheck">
                    <label class="form-check-label text-secondary small" for="rememberCheck">Keep me signed in</label>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold rounded-3 shadow">
                    Sign In to Driver Terminal
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
