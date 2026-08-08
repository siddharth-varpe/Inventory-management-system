<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - StockManager Enterprise ERP</title>

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
                <h4 class="fw-bold text-body mb-1">Password Reset</h4>
                <p class="text-muted small">Enter your account email to receive a password reset link.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success text-center mb-4 small">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold small text-body">Email Address</label>
                    <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus placeholder="name@company.com">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                    Send Password Reset Link
                </button>
            </form>

            <div class="text-center mt-4 pt-3 border-top border-translucent">
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold small">Return to Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>
