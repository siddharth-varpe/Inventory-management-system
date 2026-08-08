@extends('layouts.app')

@section('title', 'Company Profile - StockManager ERP')

@section('header', 'Organization Profile')
@section('subheader', 'Manage legal details, branding logo, tax IDs, and global defaults.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Company Profile</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12 col-xl-10 mx-auto">
        <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- Company Profile Card -->
            <div class="card mb-4 p-4">
                <div class="d-flex flex-column flex-sm-row align-items-center gap-4 mb-4 pb-3 border-bottom border-translucent">
                    <div class="avatar-logo-wrapper text-center">
                        @if($company->logo)
                            <img src="{{ Storage::url($company->logo) }}" alt="Logo" class="rounded-3 border shadow-sm" style="width: 100px; height: 100px; object-fit: contain;">
                        @else
                            <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 100px; height: 100px; font-size: 2rem;">
                                {{ strtoupper(substr($company->name ?? 'ERP', 0, 2)) }}
                            </div>
                        @endif
                        <div class="mt-2">
                            <label for="logo" class="btn btn-sm btn-outline-secondary">Change Logo</label>
                            <input type="file" id="logo" name="logo" class="d-none" onchange="this.form.submit()">
                        </div>
                    </div>

                    <div>
                        <h4 class="fw-bold text-body mb-1">{{ $company->name }}</h4>
                        <p class="text-muted small mb-1">{{ $company->legal_name ?? 'Legal Name Not Configured' }}</p>
                        <x-status-badge :status="$company->status" />
                    </div>
                </div>

                <h5 class="fw-bold mb-3 text-body">General Information</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label fw-semibold small">Company Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $company->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="legal_name" class="form-label fw-semibold small">Legal Business Name</label>
                        <input type="text" id="legal_name" name="legal_name" class="form-control" value="{{ old('legal_name', $company->legal_name) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="business_type" class="form-label fw-semibold small">Business Type</label>
                        <input type="text" id="business_type" name="business_type" class="form-control" placeholder="e.g. Corporation, LLC, Partnership" value="{{ old('business_type', $company->business_type) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="industry" class="form-label fw-semibold small">Industry Sector</label>
                        <input type="text" id="industry" name="industry" class="form-control" placeholder="e.g. Manufacturing, Wholesale, Retail" value="{{ old('industry', $company->industry) }}">
                    </div>
                </div>

                <h5 class="fw-bold mb-3 text-body">Tax & Statutory Identifiers</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="gst_number" class="form-label fw-semibold small">GST Number</label>
                        <input type="text" id="gst_number" name="gst_number" class="form-control" value="{{ old('gst_number', $company->gst_number) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="pan_number" class="form-label fw-semibold small">PAN Number</label>
                        <input type="text" id="pan_number" name="pan_number" class="form-control" value="{{ old('pan_number', $company->pan_number) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="cin_number" class="form-label fw-semibold small">CIN Number (Corporate ID)</label>
                        <input type="text" id="cin_number" name="cin_number" class="form-control" value="{{ old('cin_number', $company->cin_number) }}">
                    </div>
                </div>

                <h5 class="fw-bold mb-3 text-body">Contact & Location</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="email" class="form-label fw-semibold small">Official Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $company->email) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="phone" class="form-label fw-semibold small">Phone Number</label>
                        <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', $company->phone) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="website" class="form-label fw-semibold small">Website URL</label>
                        <input type="url" id="website" name="website" class="form-control" placeholder="https://company.com" value="{{ old('website', $company->website) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="address_line_1" class="form-label fw-semibold small">Address Line 1</label>
                        <input type="text" id="address_line_1" name="address_line_1" class="form-control" value="{{ old('address_line_1', $company->address_line_1) }}">
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="address_line_2" class="form-label fw-semibold small">Address Line 2</label>
                        <input type="text" id="address_line_2" name="address_line_2" class="form-control" value="{{ old('address_line_2', $company->address_line_2) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="city" class="form-label fw-semibold small">City</label>
                        <input type="text" id="city" name="city" class="form-control" value="{{ old('city', $company->city) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="state" class="form-label fw-semibold small">State / Province</label>
                        <input type="text" id="state" name="state" class="form-control" value="{{ old('state', $company->state) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="country" class="form-label fw-semibold small">Country</label>
                        <input type="text" id="country" name="country" class="form-control" value="{{ old('country', $company->country) }}">
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="postal_code" class="form-label fw-semibold small">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" value="{{ old('postal_code', $company->postal_code) }}">
                    </div>
                </div>

                <h5 class="fw-bold mb-3 text-body">Regional Defaults</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-3">
                        <label for="currency" class="form-label fw-semibold small">Base Currency</label>
                        <select id="currency" name="currency" class="form-select">
                            <option value="USD" {{ old('currency', $company->currency) == 'USD' ? 'selected' : '' }}>USD ($)</option>
                            <option value="EUR" {{ old('currency', $company->currency) == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                            <option value="GBP" {{ old('currency', $company->currency) == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                            <option value="INR" {{ old('currency', $company->currency) == 'INR' ? 'selected' : '' }}>INR (₹)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="timezone" class="form-label fw-semibold small">System Timezone</label>
                        <select id="timezone" name="timezone" class="form-select">
                            <option value="UTC" {{ old('timezone', $company->timezone) == 'UTC' ? 'selected' : '' }}>UTC</option>
                            <option value="America/New_York" {{ old('timezone', $company->timezone) == 'America/New_York' ? 'selected' : '' }}>Eastern Time (US)</option>
                            <option value="Europe/London" {{ old('timezone', $company->timezone) == 'Europe/London' ? 'selected' : '' }}>London (GMT/BST)</option>
                            <option value="Asia/Kolkata" {{ old('timezone', $company->timezone) == 'Asia/Kolkata' ? 'selected' : '' }}>India (IST)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="financial_year" class="form-label fw-semibold small">Financial Year</label>
                        <select id="financial_year" name="financial_year" class="form-select">
                            <option value="Jan-Dec" {{ old('financial_year', $company->financial_year) == 'Jan-Dec' ? 'selected' : '' }}>January - December</option>
                            <option value="Apr-Mar" {{ old('financial_year', $company->financial_year) == 'Apr-Mar' ? 'selected' : '' }}>April - March</option>
                            <option value="Jul-Jun" {{ old('financial_year', $company->financial_year) == 'Jul-Jun' ? 'selected' : '' }}>July - June</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="language" class="form-label fw-semibold small">Default Language</label>
                        <select id="language" name="language" class="form-select">
                            <option value="en" {{ old('language', $company->language) == 'en' ? 'selected' : '' }}>English</option>
                            <option value="es" {{ old('language', $company->language) == 'es' ? 'selected' : '' }}>Spanish</option>
                            <option value="fr" {{ old('language', $company->language) == 'fr' ? 'selected' : '' }}>French</option>
                            <option value="de" {{ old('language', $company->language) == 'de' ? 'selected' : '' }}>German</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary fw-semibold px-4">
                        Save Company Profile
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
