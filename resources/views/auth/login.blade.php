<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign In - StockManager Enterprise ERP</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bs-body-tertiary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            border-radius: 1rem;
            border: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="card auth-card mx-auto bg-body p-4 p-sm-5">
            <div class="text-center mb-4">
                <div class="brand-icon bg-primary text-white rounded-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" fill="currentColor" class="bi bi-box-seam" viewBox="0 0 16 16">
                        <path d="M8.186 1.113a.5.5 0 0 0-.372 0L1.846 3.5l2.404.961L10.404 2zm3.564 1.426L5.596 5 8 5.961 14.154 3.5zm3.25 1.7-6.5 2.6v7.922l6.5-2.6V4.24zM7.5 14.762V6.838L1 4.239v7.923l6.5 2.6zM7.443.184a1.5 1.5 0 0 1 1.114 0l7.129 2.852A.5.5 0 0 1 16 3.5v8.662a1.5 1.5 0 0 1-.901 1.37l-7 2.8a1.5 1.5 0 0 1-1.198 0l-7-2.8A1.5 1.5 0 0 1 0 12.162V3.5a.5.5 0 0 1 .314-.464L7.443.184z"/>
                    </svg>
                </div>
                <h4 class="fw-bold text-body mb-1">StockManager ERP</h4>
                <p class="text-muted small">Sign in to your enterprise account</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success text-center mb-4 small">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-body">Email Address</label>
                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@company.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="password" class="form-label fw-semibold small text-body mb-0">Password</label>
                        @if (Route::has('password.request'))
                            <a class="text-decoration-none small" href="{{ route('password.request') }}">Forgot Password?</a>
                        @endif
                    </div>
                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label small text-muted" for="remember">Remember this device</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                    Sign In
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top border-translucent">
                <span class="text-muted small">Don't have an account?</span>
                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold small ms-1">Register Company</a>
            </div>
        </div>
    </div>
</body>
</html>
