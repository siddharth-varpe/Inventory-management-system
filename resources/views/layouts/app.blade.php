<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'StockManager Enterprise ERP'))</title>

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Enterprise Custom Styling & Design Tokens (Full Width Layout & WCAG AA Contrast) -->
    <style>
        :root, [data-bs-theme="light"] {
            --bs-font-sans-serif: 'Inter', system-ui, -apple-system, sans-serif;
            --sidebar-width: 0px;

            /* Light Theme Specific Tokens */
            --erp-badge-purple-bg: #6f42c1;
            --erp-badge-purple-text: #ffffff;
            --erp-badge-purple-border: #5a32a3;

            --erp-subtle-purple-bg: #f3ebff;
            --erp-subtle-purple-text: #5925dc;
            --erp-subtle-purple-border: #d8b4fe;

            --erp-table-header-bg: #f8f9fa;
            --erp-table-header-color: #212529;
        }

        [data-bs-theme="dark"] {
            /* Dark Theme Specific Tokens */
            --erp-badge-purple-bg: #8b5cf6;
            --erp-badge-purple-text: #ffffff;
            --erp-badge-purple-border: #7c3aed;

            --erp-subtle-purple-bg: #2e1065;
            --erp-subtle-purple-text: #ddd6fe;
            --erp-subtle-purple-border: #5b21b6;

            --erp-table-header-bg: #1e293b;
            --erp-table-header-color: #f8fafc;
        }

        body {
            font-family: var(--bs-font-sans-serif);
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Enterprise Purple & Custom Badge Utilities */
        .bg-purple {
            background-color: var(--erp-badge-purple-bg) !important;
            color: var(--erp-badge-purple-text) !important;
        }

        .bg-purple-subtle, .badge-subtle-purple {
            background-color: var(--erp-subtle-purple-bg) !important;
            color: var(--erp-subtle-purple-text) !important;
            border: 1px solid var(--erp-subtle-purple-border) !important;
        }

        .btn-purple {
            background-color: var(--erp-badge-purple-bg) !important;
            border-color: var(--erp-badge-purple-border) !important;
            color: var(--erp-badge-purple-text) !important;
        }
        .btn-purple:hover {
            opacity: 0.9;
            color: #ffffff !important;
        }

        /* High-contrast status badges */
        .badge-status-converted {
            background-color: var(--erp-subtle-purple-bg) !important;
            color: var(--erp-subtle-purple-text) !important;
            border: 1px solid var(--erp-subtle-purple-border) !important;
        }

        /* Layout Container Adjustments - Full Width */
        .app-wrapper {
            display: flex;
            flex: 1;
            width: 100%;
        }

        .main-content {
            flex: 1;
            margin-left: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        /* Modern Card Styling */
        .card {
            border-radius: 0.75rem;
            border: 1px solid var(--bs-border-color-translucent);
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.06);
        }

        /* Table Header Contrast Fix for Dark Mode */
        .table thead th {
            background-color: var(--erp-table-header-bg) !important;
            color: var(--erp-table-header-color) !important;
            border-bottom: 2px solid var(--bs-border-color-translucent) !important;
        }

        /* ========================================================================= */
        /* STOCKMANAGER ENTERPRISE GLOBAL DESIGN SYSTEM                              */
        /* ========================================================================= */

        /* 1. TYPOGRAPHY HIERARCHY */
        .page-title, h1.h3 {
            font-size: 1.75rem !important; /* 28px */
            font-weight: 700 !important;
            letter-spacing: -0.02em !important;
            line-height: 1.25 !important;
        }

        .section-title, h2.h4, h3.h5 {
            font-size: 1.25rem !important; /* 20px */
            font-weight: 600 !important;
            letter-spacing: -0.01em !important;
        }

        .card-title, h4.h5, h5.h6 {
            font-size: 1.05rem !important; /* 16.8px */
            font-weight: 600 !important;
        }

        .body-text, p, td, th {
            font-size: 0.9rem !important; /* ~14.4px */
        }

        .secondary-text, .text-muted {
            font-size: 0.825rem !important; /* ~13.2px */
        }

        .label-text, label, .form-label {
            font-size: 0.8rem !important; /* ~12.8px */
            font-weight: 500 !important;
        }

        .badge-text, .badge {
            font-size: 0.725rem !important; /* ~11.6px */
            font-weight: 600 !important;
            letter-spacing: 0.02em;
        }

        /* 2. CANONICAL CARD COMPONENT */
        .card, .erp-card {
            border-radius: 0.75rem !important; /* 12px */
            border: 1px solid var(--bs-border-color-translucent) !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            background-color: var(--bs-body-bg);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .card-header {
            background-color: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color-translucent);
            padding: 1rem 1.25rem;
        }

        .card-body {
            padding: 1.25rem 1.5rem;
        }

        .card-footer {
            background-color: var(--bs-tertiary-bg);
            border-top: 1px solid var(--bs-border-color-translucent);
            padding: 0.875rem 1.25rem;
        }

        /* 3. CANONICAL BUTTON COMPONENT */
        .btn {
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0.5rem; /* 8px */
            padding: 0.5rem 1rem;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.15s ease-in-out;
        }

        .btn-sm {
            height: 34px;
            padding: 0.25rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 0.375rem;
        }

        .btn-lg {
            height: 48px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 0.5rem;
        }

        /* 4. CANONICAL FORM INPUT SYSTEM */
        .form-control, .form-select {
            height: 40px;
            font-size: 0.875rem;
            border-radius: 0.5rem; /* 8px */
            border: 1px solid var(--bs-border-color-translucent);
            padding: 0.5rem 0.875rem;
            background-color: var(--bs-body-bg);
            color: var(--bs-body-color);
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .form-control-sm, .form-select-sm {
            height: 34px;
            font-size: 0.8rem;
            padding: 0.25rem 0.625rem;
            border-radius: 0.375rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--bs-primary);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }

        /* 5. CANONICAL TABLE SYSTEM */
        .table {
            --bs-table-bg: transparent;
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .table thead th {
            background-color: var(--erp-table-header-bg) !important;
            color: var(--erp-table-header-color) !important;
            font-weight: 600 !important;
            text-transform: uppercase;
            font-size: 0.725rem !important;
            letter-spacing: 0.04em;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--bs-border-color-translucent) !important;
        }

        .table tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--bs-border-color-translucent);
        }

        .table-hover tbody tr:hover {
            background-color: var(--bs-tertiary-bg);
        }

        /* 6. CANONICAL EMPTY STATE */
        .erp-empty-state {
            min-height: 320px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2.5rem 1.5rem;
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color-translucent);
            border-radius: 0.75rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--bs-secondary-bg-subtle);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--bs-secondary-border-subtle);
        }

        /* Strict Pagination Styling & SVG Arrow Constraining */
        .pagination {
            margin-bottom: 0;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .pagination .page-item .page-link {
            border-radius: 0.375rem !important;
            padding: 0.375rem 0.75rem;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--bs-body-color);
            background-color: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color-translucent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.2;
        }
        .pagination .page-item.active .page-link {
            background-color: var(--bs-primary);
            border-color: var(--bs-primary);
            color: #ffffff !important;
        }
        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            background-color: var(--bs-tertiary-bg);
        }
        .pagination svg,
        .page-link svg,
        nav[role="navigation"] svg {
            width: 14px !important;
            height: 14px !important;
            max-width: 14px !important;
            max-height: 14px !important;
            display: inline-block !important;
            vertical-align: middle;
        }
    </style>
    @stack('styles')
</head>
<body>

    <div class="app-wrapper">
        <!-- Main Full-Width Workspace Area -->
        <div class="main-content">
            <!-- Navbar with Top Portal Links -->
            @include('layouts.partials.navbar')

            <!-- Main Content Container -->
            <main class="container-fluid px-4 py-4 flex-grow-1">
                <!-- Breadcrumbs -->
                @include('layouts.partials.breadcrumb')

                <!-- Page Header -->
                @hasSection('header')
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-2">
                    <div>
                        <h1 class="h3 fw-bold tracking-tight mb-0 text-body">@yield('header')</h1>
                        @hasSection('subheader')
                            <p class="text-muted mb-0 small mt-1">@yield('subheader')</p>
                        @endif
                    </div>
                    @yield('header-actions')
                </div>
                @endif

                <!-- Dynamic Page Content -->
                @yield('content')
            </main>

            <!-- Footer -->
            @include('layouts.partials.footer')
        </div>
    </div>

    <!-- Modals, Toasts, Loader -->
    @include('layouts.partials.modal')
    @include('layouts.partials.toasts')
    @include('layouts.partials.loader')

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Vanilla JS Enterprise Utilities -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Theme Management (Light / Dark mode)
            const themeToggleBtn = document.getElementById('themeToggleBtn');
            const themeIconSun = document.getElementById('themeIconSun');
            const themeIconMoon = document.getElementById('themeIconMoon');
            const htmlElement = document.documentElement;

            const storedTheme = localStorage.getItem('theme') || 'light';
            setTheme(storedTheme);

            if (themeToggleBtn) {
                themeToggleBtn.addEventListener('click', () => {
                    const currentTheme = htmlElement.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    setTheme(newTheme);
                });
            }

            function setTheme(theme) {
                htmlElement.setAttribute('data-bs-theme', theme);
                localStorage.setItem('theme', theme);
                if (theme === 'dark') {
                    themeIconSun?.classList.remove('d-none');
                    themeIconMoon?.classList.add('d-none');
                } else {
                    themeIconSun?.classList.add('d-none');
                    themeIconMoon?.classList.remove('d-none');
                }
            }

            // Global Vanilla JS Helpers
            window.Toast = {
                show(message, type = 'info', duration = 4000) {
                    const toastContainer = document.getElementById('toastContainer');
                    const toastEl = document.createElement('div');
                    toastEl.className = `toast align-items-center text-bg-${type} border-0 show shadow`;
                    toastEl.setAttribute('role', 'alert');
                    toastEl.setAttribute('aria-live', 'assertive');
                    toastEl.setAttribute('aria-atomic', 'true');
                    toastEl.innerHTML = `
                        <div class="d-flex">
                            <div class="toast-body">${message}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    `;
                    toastContainer.appendChild(toastEl);
                    const bsToast = new bootstrap.Toast(toastEl, { delay: duration });
                    bsToast.show();
                    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
                }
            };

            window.Loader = {
                show() {
                    document.getElementById('globalLoader')?.classList.remove('d-none');
                    document.getElementById('globalLoader')?.classList.add('d-flex');
                },
                hide() {
                    document.getElementById('globalLoader')?.classList.remove('d-flex');
                    document.getElementById('globalLoader')?.classList.add('d-none');
                }
            };

            window.GlobalModal = {
                open(title, contentHtml, footerHtml = null) {
                    document.getElementById('globalModalLabel').innerText = title;
                    document.getElementById('globalModalBody').innerHTML = contentHtml;
                    if (footerHtml) {
                        document.getElementById('globalModalFooter').innerHTML = footerHtml;
                    }
                    const modal = new bootstrap.Modal(document.getElementById('globalModal'));
                    modal.show();
                }
            };
        });
    </script>
    @stack('scripts')
</body>
</html>
