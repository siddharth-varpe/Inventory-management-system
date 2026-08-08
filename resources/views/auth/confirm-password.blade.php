<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirm Password - StockManager Enterprise ERP</title>

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
                <h4 class="fw-bold text-body mb-1">Confirm Security Access</h4>
                <p class="text-muted small">This is a secure area of the application. Please confirm your password before continuing.</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}">
                @csrf

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold small text-body">Password</label>
                    <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                    Confirm Password
                </button>
            </form>
        </div>
    </div>
</body>
</html>
