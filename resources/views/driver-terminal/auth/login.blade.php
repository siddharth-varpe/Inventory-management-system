@extends('driver-terminal.layouts.app')

@section('title', 'Driver Login — Driver Terminal')
@section('hide_header', 'true')

@section('styles')
<style>
    /* Mobile-first Fleet Driver Login Styles */
    .dt-login-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background-color: #f8fafc;
        padding: 20px 16px;
    }

    .dt-mobile-card {
        width: 100%;
        max-width: 420px;
        margin: 0 auto;
    }

    /* Section A: Top Brand Area */
    .dt-brand-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding: 0 4px;
    }

    .dt-brand-identity {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .dt-brand-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        flex-shrink: 0;
    }

    .dt-brand-title {
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #0f172a;
        text-transform: uppercase;
        line-height: 1.1;
    }

    .dt-brand-subtext {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }

    .dt-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        background-color: #dcfce7;
        border: 1px solid #bbf7d0;
        border-radius: 9999px;
        font-size: 0.725rem;
        font-weight: 700;
        color: #15803d;
        font-family: 'JetBrains Mono', monospace;
    }

    .dt-status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background-color: #16a34a;
        box-shadow: 0 0 0 2px rgba(22, 163, 74, 0.2);
    }

    /* Section B: Welcome Section */
    .dt-welcome-container {
        margin-bottom: 20px;
        padding: 0 4px;
    }

    .dt-welcome-heading {
        font-size: 1.65rem;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.025em;
        margin-bottom: 4px;
        line-height: 1.25;
    }

    .dt-welcome-text {
        font-size: 0.925rem;
        color: #475569;
        font-weight: 400;
        margin-bottom: 0;
        line-height: 1.4;
    }

    /* Section C: Driver Login Card */
    .dt-auth-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 24px 20px;
        box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
    }

    /* Form Inputs */
    .dt-form-group {
        margin-bottom: 18px;
    }

    .dt-label {
        display: block;
        font-size: 0.725rem;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin-bottom: 6px;
    }

    .dt-input-wrapper {
        position: relative;
    }

    .dt-input {
        width: 100%;
        height: 52px;
        padding: 0 16px;
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        background-color: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        transition: all 0.2s ease;
        outline: none;
        box-sizing: border-box;
    }

    .dt-input:focus {
        background-color: #ffffff;
        border-color: #0284c7;
        box-shadow: 0 0 0 4px rgba(2, 132, 199, 0.15);
    }

    /* +91 Fixed Addon Input Group */
    .dt-input-group {
        display: flex;
        align-items: center;
        width: 100%;
    }

    .dt-input-prefix {
        height: 52px;
        padding: 0 14px;
        background-color: #f1f5f9;
        border: 1.5px solid #cbd5e1;
        border-right: none;
        border-top-left-radius: 12px;
        border-bottom-left-radius: 12px;
        color: #0f172a;
        font-size: 1rem;
        font-weight: 700;
        font-family: 'JetBrains Mono', monospace;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
        pointer-events: none;
        flex-shrink: 0;
    }

    .dt-input-with-prefix {
        border-top-left-radius: 0 !important;
        border-bottom-left-radius: 0 !important;
        flex-grow: 1;
    }

    .dt-input-group:focus-within .dt-input-prefix {
        border-color: #0284c7;
        background-color: #e0f2fe;
        color: #0369a1;
    }

    .dt-input-mono {
        font-family: 'JetBrains Mono', monospace;
    }

    .dt-input-uppercase {
        text-transform: uppercase;
    }

    .dt-field-hint {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 5px;
        font-weight: 500;
    }

    /* Section D: Driver ID Visual Support */
    .dt-info-banner {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 14px;
        background-color: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 12px;
        margin-top: 6px;
        margin-bottom: 22px;
    }

    .dt-info-icon {
        color: #0284c7;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .dt-info-text {
        font-size: 0.775rem;
        color: #0369a1;
        font-weight: 500;
        line-height: 1.4;
        margin: 0;
    }

    /* Section E: Login Button */
    .dt-btn-login {
        width: 100%;
        height: 54px;
        background-color: #0284c7;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        cursor: pointer;
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.35);
        transition: all 0.2s ease;
    }

    .dt-btn-login:hover {
        background-color: #0369a1;
        box-shadow: 0 6px 18px rgba(2, 132, 199, 0.4);
    }

    .dt-btn-login:active {
        transform: translateY(1px);
        box-shadow: 0 2px 8px rgba(2, 132, 199, 0.3);
    }

    .dt-btn-login:disabled {
        background-color: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
    }

    /* Inline Alert Component */
    .dt-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border-radius: 12px;
        padding: 12px 14px;
        font-size: 0.825rem;
        margin-bottom: 18px;
        line-height: 1.4;
    }

    .dt-alert-danger {
        background-color: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    .dt-alert-success {
        background-color: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }

    .dt-alert-icon {
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* Footer */
    .dt-footer-note {
        text-align: center;
        font-size: 0.75rem;
        color: #94a3b8;
        margin-top: 20px;
        font-weight: 500;
    }

    /* Spinner */
    .dt-spinner {
        width: 18px;
        height: 18px;
        border: 2.5px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: #ffffff;
        animation: dt-spin 0.8s linear infinite;
    }

    @keyframes dt-spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="dt-login-page">
    <div class="dt-mobile-card">

        <!-- SECTION A: TOP BRAND AREA -->
        <header class="dt-brand-header">
            <div class="dt-brand-identity">
                <div class="dt-brand-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13" rx="2" ry="2"/>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/>
                        <circle cx="5.5" cy="18.5" r="2.5"/>
                        <circle cx="18.5" cy="18.5" r="2.5"/>
                    </svg>
                </div>
                <div>
                    <div class="dt-brand-title">STOCKMANAGER</div>
                    <div class="dt-brand-subtext">DRIVER TERMINAL</div>
                </div>
            </div>
            <div class="dt-status-pill">
                <span class="dt-status-dot"></span>
                <span>System Online</span>
            </div>
        </header>

        <!-- SECTION B: WELCOME SECTION -->
        <div class="dt-welcome-container">
            <h1 class="dt-welcome-heading">Welcome back</h1>
            <p class="dt-welcome-text">Sign in to access your assigned deliveries.</p>
        </div>

        <!-- SECTION C: DRIVER LOGIN CARD -->
        <div class="dt-auth-card">

            <!-- ERROR ALERTS -->
            @if ($errors->any())
                <div class="dt-alert dt-alert-danger" role="alert">
                    <svg class="dt-alert-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="dt-alert dt-alert-danger" role="alert">
                    <svg class="dt-alert-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if (session('success'))
                <div class="dt-alert dt-alert-success" role="alert">
                    <svg class="dt-alert-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form action="{{ route('driver-terminal.login.post') }}" method="POST" id="driverLoginForm">
                @csrf

                <!-- FIELD 1: DRIVER ID -->
                <div class="dt-form-group">
                    <label for="driver_id" class="dt-label">DRIVER ID</label>
                    <div class="dt-input-wrapper">
                        <input
                            type="text"
                            name="driver_id"
                            id="driver_id"
                            class="dt-input dt-input-mono dt-input-uppercase"
                            placeholder="DRV-000001"
                            value="{{ old('driver_id') }}"
                            required
                            autofocus
                            autocomplete="username"
                            spellcheck="false"
                        >
                    </div>
                    <div class="dt-field-hint">Enter the Driver ID provided by your Transport Manager.</div>
                </div>

                <!-- FIELD 2: REGISTERED MOBILE NUMBER (+91 FIXED PREFIX) -->
                <div class="dt-form-group">
                    <label for="mobile_number" class="dt-label">REGISTERED MOBILE NUMBER</label>
                    <div class="dt-input-group">
                        <span class="dt-input-prefix" aria-label="Country code">+91</span>
                        <input
                            type="tel"
                            inputmode="numeric"
                            pattern="[0-9]{10}"
                            maxlength="10"
                            name="mobile_number"
                            id="mobile_number"
                            class="dt-input dt-input-mono dt-input-with-prefix"
                            placeholder="9876543210"
                            value="{{ old('mobile_number') }}"
                            required
                            autocomplete="tel"
                            spellcheck="false"
                        >
                    </div>
                </div>

                <!-- SECTION D: DRIVER ID VISUAL SUPPORT -->
                <div class="dt-info-banner">
                    <svg class="dt-info-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <p class="dt-info-text">
                        Use the mobile number registered with the Transport Department.
                    </p>
                </div>

                <!-- SECTION E: LOGIN BUTTON -->
                <button type="submit" class="dt-btn-login" id="loginSubmitBtn">
                    <span id="btnText">LOGIN</span>
                </button>
            </form>
        </div>

        <div class="dt-footer-note">
            Secure Driver Terminal — StockManager ERP
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('driverLoginForm');
    const submitBtn = document.getElementById('loginSubmitBtn');
    const btnText = document.getElementById('btnText');
    const driverIdInput = document.getElementById('driver_id');
    const mobileInput = document.getElementById('mobile_number');

    // 1. Format Driver ID: Uppercase & Trim as user types
    if (driverIdInput) {
        driverIdInput.addEventListener('input', (e) => {
            const cursorPosition = e.target.selectionStart;
            e.target.value = e.target.value.toUpperCase().trimStart();
            e.target.setSelectionRange(cursorPosition, cursorPosition);
        });
    }

    // 2. Format Mobile Input: Digits only, max 10 digits
    if (mobileInput) {
        mobileInput.addEventListener('input', (e) => {
            let digits = e.target.value.replace(/[^0-9]/g, '');
            if (digits.length > 10) {
                digits = digits.slice(0, 10);
            }
            e.target.value = digits;
        });
    }

    // 3. Prevent duplicate submissions and show loading state
    if (loginForm && submitBtn) {
        loginForm.addEventListener('submit', (e) => {
            if (mobileInput && mobileInput.value.length !== 10) {
                e.preventDefault();
                alert('Please enter your 10-digit mobile number.');
                mobileInput.focus();
                return false;
            }

            if (loginForm.checkValidity()) {
                submitBtn.disabled = true;
                btnText.innerHTML = '<span class="dt-spinner"></span> Signing in...';
            }
        });
    }
});
</script>
@endsection
