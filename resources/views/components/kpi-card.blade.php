@props([
    'title',
    'value',
    'subtitle' => null,
    'badge' => null,
    'badgeBg' => 'bg-primary-subtle text-primary',
    'icon' => null,
])

<div class="card p-3 rounded-4 shadow-sm border-translucent bg-body h-100">
    <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="text-muted fw-semibold small">{{ $title }}</span>
        @if($badge)
            <span class="badge border rounded-pill px-2 py-1 small {{ $badgeBg }}">{{ $badge }}</span>
        @endif
    </div>
    <div class="d-flex align-items-center justify-content-between">
        <h3 class="fw-bold text-body mb-0">{{ $value }}</h3>
        @if($icon)
            <div class="text-muted opacity-50">
                {!! $icon !!}
            </div>
        @endif
    </div>
    @if($subtitle)
        <div class="mt-2 text-muted small" style="font-size: 0.75rem;">
            {{ $subtitle }}
        </div>
    @endif
</div>
