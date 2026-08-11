<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Driver Workspace') — StockManager Driver Terminal</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --dt-bg-canvas: #f8fafc;
            --dt-surface-card: #ffffff;
            --dt-primary: #2563eb;
            --dt-primary-hover: #1d4ed8;
            --dt-primary-light: #eff6ff;
            --dt-text-heading: #0f172a;
            --dt-text-body: #334155;
            --dt-text-muted: #64748b;
            --dt-border-subtle: #e2e8f0;
            --dt-success: #10b981;
            --dt-success-bg: #ecfdf5;
            --dt-warning: #f59e0b;
            --dt-warning-bg: #fffbeb;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #cbd5e1;
            color: var(--dt-text-body);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        /* Mobile Container Shell for Desktop/Tablet Centering */
        .driver-app-shell {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background-color: var(--dt-bg-canvas);
            position: relative;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            border-left: 1px solid var(--dt-border-subtle);
            border-right: 1px solid var(--dt-border-subtle);
        }

        /* Sticky Header */
        .dt-header {
            position: sticky;
            top: 0;
            z-index: 1020;
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid var(--dt-border-subtle);
            padding: 12px 16px;
        }

        .dt-header-btn {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            color: var(--dt-text-heading);
            background: #f1f5f9;
            border: 1px solid var(--dt-border-subtle);
            transition: all 0.15s ease-in-out;
            cursor: pointer;
        }

        .dt-header-btn:active {
            transform: scale(0.94);
            background: #e2e8f0;
        }

        /* Main Content Padding to account for bottom nav */
        .dt-content {
            flex: 1;
            padding: 16px;
            padding-bottom: 90px;
        }

        /* Fixed Bottom Navigation */
        .dt-bottom-nav {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100%;
            max-width: 480px;
            z-index: 1030;
            background-color: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-top: 1px solid var(--dt-border-subtle);
            padding: 8px 12px;
            padding-bottom: calc(8px + env(safe-area-inset-bottom));
            display: flex;
            align-items: center;
            justify-content: space-around;
        }

        .dt-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #64748b;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 12px;
            transition: all 0.15s ease;
            position: relative;
            min-width: 64px;
        }

        .dt-nav-item.active {
            color: var(--dt-primary);
        }

        .dt-nav-item .dt-nav-icon {
            width: 24px;
            height: 24px;
            margin-bottom: 2px;
            transition: transform 0.15s ease;
        }

        .dt-nav-item.active .dt-nav-icon {
            transform: translateY(-1px);
        }

        /* Side Offcanvas Menu */
        .offcanvas.offcanvas-start {
            max-width: 320px;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .dt-menu-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 24px 20px;
            border-top-right-radius: 20px;
        }

        .dt-menu-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--dt-text-heading);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 12px;
            transition: background 0.15s ease;
        }

        .dt-menu-link:hover, .dt-menu-link:active {
            background-color: #f1f5f9;
            color: var(--dt-primary);
        }
    </style>
    @yield('styles')
</head>
<body>

    <div class="driver-app-shell">
        @php
            $currentDriver = request()->attributes->get('current_driver') ?? ($currentDriver ?? null);
            $driverCode = $currentDriver ? strtolower($currentDriver->driver_code) : '';
            
            // Dynamic Time Greeting
            $hour = (int) now()->format('H');
            if ($hour >= 5 && $hour < 12) {
                $greetingPrefix = 'Good Morning';
            } elseif ($hour >= 12 && $hour < 17) {
                $greetingPrefix = 'Good Afternoon';
            } else {
                $greetingPrefix = 'Good Evening';
            }

            $driverFirstName = $currentDriver ? explode(' ', trim($currentDriver->driver_name))[0] : 'Driver';
            $unreadCount = $unreadNotificationsCount ?? 0;
        @endphp

        <!-- HEADER -->
        <header class="dt-header d-flex align-items-center justify-content-between">
            <button class="dt-header-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#driverSideMenu" aria-controls="driverSideMenu" title="Open Menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>

            <div class="text-start flex-grow-1 px-3">
                <h6 class="fw-bold text-dark mb-0 lh-sm fs-6">
                    {{ $greetingPrefix }}, {{ $driverFirstName }}! 👋
                </h6>
                <span class="text-muted small" style="font-size: 0.75rem;">Have a safe and productive day.</span>
            </div>

            @if($driverCode)
            <a href="{{ route('driver-terminal.notifications', ['driver_code' => $driverCode]) }}" class="dt-header-btn position-relative" title="Notifications">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16">
                    <path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1.002A.5.5 0 0 1 14.5 14H1.5a.5.5 0 0 1-.5-.998c.3-.201.557-.555.78-1.002C2.43 10.5 2.5 8.75 2.5 6a5.5 5.5 0 0 1 11 0c0 2.75.07 4.5.72 6z"/>
                </svg>
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white" style="font-size: 0.65rem;">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
            @else
            <div class="dt-header-btn opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" class="bi bi-bell" viewBox="0 0 16 16"><path d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2zM8 1.918l-.797.161A4.002 4.002 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4.002 4.002 0 0 0-3.203-3.92L8 1.917zM14.22 12c.223.447.481.801.78 1.002A.5.5 0 0 1 14.5 14H1.5a.5.5 0 0 1-.5-.998c.3-.201.557-.555.78-1.002C2.43 10.5 2.5 8.75 2.5 6a5.5 5.5 0 0 1 11 0c0 2.75.07 4.5.72 6z"/></svg>
            </div>
            @endif
        </header>

        <!-- MAIN CONTENT AREA -->
        <main class="dt-content">
            @yield('content')
        </main>

        <!-- BOTTOM NAVIGATION -->
        @if($driverCode)
        <nav class="dt-bottom-nav">
            <a href="{{ route('driver-terminal.index', ['driver_code' => $driverCode]) }}" class="dt-nav-item {{ request()->routeIs('driver-terminal.index') ? 'active' : '' }}">
                <svg class="dt-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => $driverCode]) }}" class="dt-nav-item {{ request()->routeIs('driver-terminal.deliveries.*') ? 'active' : '' }}">
                <svg class="dt-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.75 7.5h16.5m-16.5 0l1.455-3.638A1.125 1.125 0 015.76 3h12.48a1.125 1.125 0 011.055.762L20.25 7.5" />
                </svg>
                <span>Trips</span>
            </a>

            <a href="{{ route('driver-terminal.notifications', ['driver_code' => $driverCode]) }}" class="dt-nav-item {{ request()->routeIs('driver-terminal.notifications') ? 'active' : '' }}">
                <svg class="dt-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-1.074-.85c.18-.843.435-1.637.747-2.366C3.766 16.275 3 14.225 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                </svg>
                <span>Messages</span>
            </a>

            <a href="{{ route('driver-terminal.profile', ['driver_code' => $driverCode]) }}" class="dt-nav-item {{ request()->routeIs('driver-terminal.profile') || request()->routeIs('driver-terminal.driver-profile') ? 'active' : '' }}">
                <svg class="dt-nav-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
                <span>Profile</span>
            </a>
        </nav>
        @endif

        <!-- OFFCANVAS SIDE MENU -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="driverSideMenu" aria-labelledby="driverSideMenuLabel">
            <div class="dt-menu-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.2rem;">
                        {{ strtoupper(substr($driverFirstName, 0, 1)) }}
                    </div>
                    <div>
                        <div class="micro-text text-uppercase font-monospace text-info mb-0.5 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">DRIVER TERMINAL</div>
                        <h6 class="fw-bold text-white mb-0" id="driverSideMenuLabel">
                            {{ $currentDriver->driver_name ?? 'Driver' }}
                        </h6>
                        <span class="badge bg-primary-subtle text-primary font-monospace small px-2 py-0.5 mt-1">
                            {{ $currentDriver->driver_code ?? 'DRV-000000' }}
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-3">
                <div class="vstack gap-1">
                    @if($driverCode)
                    <a href="{{ route('driver-terminal.vehicle.status', ['driver_code' => $driverCode]) }}" class="dt-menu-link">
                        <span>🚗</span>
                        <span>Vehicle & Status</span>
                    </a>

                    <a href="{{ route('driver-terminal.driver-profile', ['driver_code' => $driverCode]) }}" class="dt-menu-link">
                        <span>👤</span>
                        <span>Driver Master Profile</span>
                    </a>

                    <a href="{{ route('driver-terminal.deliveries.index', ['driver_code' => $driverCode]) }}" class="dt-menu-link">
                        <span>📜</span>
                        <span>Delivery History</span>
                    </a>

                    <a href="javascript:void(0)" class="dt-menu-link opacity-75" onclick="alert('Location & Navigation module is reserved for Core 2.')">
                        <span>📍</span>
                        <span>Location & Navigation</span>
                    </a>

                    <a href="{{ route('driver-terminal.notifications', ['driver_code' => $driverCode]) }}" class="dt-menu-link">
                        <span>🔔</span>
                        <span>Notification Settings</span>
                    </a>

                    <a href="javascript:void(0)" class="dt-menu-link" onclick="alert('Help & Support: Call StockManager Dispatch Tower at +91-1800-STOCK-ERP.')">
                        <span>❓</span>
                        <span>Help & Support</span>
                    </a>

                    <a href="javascript:void(0)" class="dt-menu-link" onclick="alert('StockManager ERP — Driver Terminal Mobile v1.0.0 (Core 1)')">
                        <span>ℹ️</span>
                        <span>About</span>
                    </a>
                    @endif

                    <div class="border-top border-translucent my-3"></div>

                    @if(Auth::check())
                    <form action="{{ route('driver-terminal.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dt-menu-link text-danger w-100 border-0 bg-transparent text-start">
                            <span>🚪</span>
                            <span>Sign Out from Terminal</span>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
