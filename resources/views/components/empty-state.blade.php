@props([
    'title' => 'No Records Found',
    'message' => 'There are currently no records available to display.',
    'actionText' => null,
    'actionUrl' => null,
])

<div class="text-center py-5 px-3">
    <div class="brand-icon bg-body-tertiary text-muted rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="bi bi-inbox" viewBox="0 0 16 16">
            <path d="M4.98 4a.5.5 0 0 0-.39.188L1.54 8H6a.5.5 0 0 1 .5.5 1.5 1.5 0 1 0 3 0A.5.5 0 0 1 10 8h4.46l-3.05-3.812A.5.5 0 0 0 11.02 4zm9.954 5H10.45a2.5 2.5 0 0 1-4.9 0H1.066l.32 2.562a.5.5 0 0 0 .497.438h12.234a.5.5 0 0 0 .496-.438zM3.809 3.563A1.5 1.5 0 0 1 4.981 3h6.038a1.5 1.5 0 0 1 1.172.563l3.7 4.625a1 1 0 0 1 .228.626v3.316a1.5 1.5 0 0 1-1.5 1.5H1.5A1.5 1.5 0 0 1 0 12.13V8.814a1 1 0 0 1 .228-.626l3.581-4.625z"/>
        </svg>
    </div>
    <h5 class="fw-bold text-body mb-1">{{ $title }}</h5>
    <p class="text-muted small mb-4 mx-auto" style="max-width: 360px;">{{ $message }}</p>
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary btn-sm fw-semibold">
            {{ $actionText }}
        </a>
    @endif
</div>
