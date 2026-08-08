<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify Email - StockManager Enterprise ERP</title>

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
            max-width: 480px;
            border-radius: 1rem;
            border: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>
    <div class="container p-3">
        <div class="card auth-card mx-auto bg-body p-4 p-sm-5 text-center">
            <div class="brand-icon bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 56px; height: 56px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="currentColor" class="bi bi-envelope-check" viewBox="0 0 16 16">
                    <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 4.293V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.293l-6.708 4.208a1 1 0 0 1-1.168 0z"/>
                    <path d="M15.854 10.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 12.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                </svg>
            </div>
            <h4 class="fw-bold text-body mb-2">Verify Email Address</h4>
            <p class="text-muted small mb-4">Thanks for signing up! Before getting started, please verify your email address by clicking on the link we just emailed to you.</p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success small mb-4">
                    A new verification link has been sent to the email address provided during registration.
                </div>
            @endif

            <div class="d-flex flex-column gap-2">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                        Resend Verification Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100 btn-sm">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
