<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Driver Terminal') — StockManager Enterprise ERP</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --dt-bg-slate: #f8fafc;
            --dt-card-bg: #ffffff;
            --dt-primary-blue: #0284c7;
            --dt-primary-hover: #0369a1;
            --dt-text-navy: #0f172a;
            --dt-text-muted: #64748b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--dt-bg-slate);
            color: var(--dt-text-navy);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        .driver-header {
            background-color: #0f172a;
            border-bottom: 1px solid #1e293b;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .driver-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #0f172a;
            border-top: 1px solid #1e293b;
            z-index: 1030;
        }

        .nav-item-btn {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .nav-item-btn.active {
            color: #38bdf8;
        }
    </style>
    @yield('styles')
</head>
<body>

    @unless(View::hasSection('hide_header'))
        <!-- Driver Terminal Operational Header (Shown on authenticated pages) -->
        <header class="driver-header py-2.5 px-3 d-flex align-items-center justify-content-between text-white">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-4">🚚</span>
                <div>
                    <div class="fw-bold text-white lh-1 small">DRIVER TERMINAL</div>
                    <div class="text-slate-400 font-monospace" style="font-size: 0.7rem; color: #94a3b8;">StockManager ERP</div>
                </div>
            </div>
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" style="font-size: 0.7rem;">
                    ONLINE
                </span>
            </div>
        </header>
    @endunless

    <!-- Main Content Area -->
    <main>
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>

