<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
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
            --dt-bg-dark: #0f172a;
            --dt-card-bg: #1e293b;
            --dt-accent-blue: #38bdf8;
            --dt-accent-green: #22c55e;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dt-bg-dark);
            color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 70px; /* Space for bottom nav */
        }

        .driver-header {
            background-color: #1e293b;
            border-bottom: 1px solid #334155;
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
            border-top: 1px solid #334155;
            z-index: 1030;
        }

        .nav-item-btn {
            color: #94a3b8;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .nav-item-btn.active {
            color: var(--dt-accent-blue);
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Driver Terminal Top Header -->
    <header class="driver-header py-2.5 px-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-4">🚛</span>
            <div>
                <div class="fw-extrabold text-white lh-1 small">DRIVER TERMINAL</div>
                <div class="text-muted font-monospace" style="font-size: 0.7rem;">StockManager ERP</div>
            </div>
        </div>
        <div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" style="font-size: 0.7rem;">
                ONLINE
            </span>
        </div>
    </header>

    <!-- Main Mobile Content Area -->
    <main class="container-fluid px-3 py-3">
        @yield('content')
    </main>

    <!-- Driver Terminal Bottom Navigation Bar -->
    <nav class="driver-bottom-nav py-2 px-3 d-flex justify-content-around align-items-center">
        <a href="{{ route('driver-terminal.index') }}" class="nav-item-btn text-center {{ request()->routeIs('driver-terminal.index') ? 'active' : '' }}">
            <div class="fs-5">🏠</div>
            <div>Home</div>
        </a>
        <a href="{{ route('driver-terminal.deliveries') }}" class="nav-item-btn text-center {{ request()->routeIs('driver-terminal.deliveries*') ? 'active' : '' }}">
            <div class="fs-5">📦</div>
            <div>Deliveries</div>
        </a>
        <a href="{{ route('driver-terminal.notifications') }}" class="nav-item-btn text-center {{ request()->routeIs('driver-terminal.notifications') ? 'active' : '' }}">
            <div class="fs-5">🔔</div>
            <div>Alerts</div>
        </a>
        <a href="{{ route('driver-terminal.profile') }}" class="nav-item-btn text-center {{ request()->routeIs('driver-terminal.profile') ? 'active' : '' }}">
            <div class="fs-5">👤</div>
            <div>Profile</div>
        </a>
    </nav>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
