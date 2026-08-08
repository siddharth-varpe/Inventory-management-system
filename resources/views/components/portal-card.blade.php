@props([
    'title',
    'description',
    'icon',
    'badge',
    'theme' => 'emerald',
    'route' => '#',
    'isComingSoon' => false,
    'actionCount' => 0,
])

@php
    // Theme Icon Container Styles
    $themeStyles = [
        'emerald' => ['bg' => 'bg-success-subtle', 'text' => 'text-success', 'badge' => 'bg-success-subtle text-success border-success-subtle'],
        'sky' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'badge' => 'bg-info-subtle text-info border-info-subtle'],
        'indigo' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'badge' => 'bg-primary-subtle text-primary border-primary-subtle'],
        'amber' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'badge' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
        'teal' => ['bg' => 'bg-primary-subtle', 'text' => 'text-primary', 'badge' => 'bg-primary-subtle text-primary border-primary-subtle'],
        'rose' => ['bg' => 'bg-danger-subtle', 'text' => 'text-danger', 'badge' => 'bg-danger-subtle text-danger border-danger-subtle'],
        'cyan' => ['bg' => 'bg-info-subtle', 'text' => 'text-info', 'badge' => 'bg-info-subtle text-info border-info-subtle'],
        'purple' => ['bg' => 'bg-secondary-subtle', 'text' => 'text-secondary', 'badge' => 'bg-secondary-subtle text-secondary border-secondary-subtle'],
        'orange' => ['bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis', 'badge' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
    ];

    $style = $themeStyles[$theme] ?? $themeStyles['emerald'];
@endphp

<div class="col-12 col-sm-6 col-lg-4 col-xl-3">
    <div 
        @if($isComingSoon)
            data-bs-toggle="modal" 
            data-bs-target="#comingSoonModal"
            data-module-title="{{ $title }}"
        @else
            onclick="window.location.href='{{ $route }}'"
        @endif
        class="card portal-card h-100 p-4 rounded-4 cursor-pointer shadow-sm border-translucent bg-body text-body position-relative"
        style="min-height: 230px; cursor: pointer; transition: transform 200ms ease, box-shadow 200ms ease;"
    >
        <!-- Top Row: Icon Wrapper & Badges -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="portal-icon-wrapper rounded-3 p-3 d-flex align-items-center justify-content-center {{ $style['bg'] }} {{ $style['text'] }}" style="width: 52px; height: 52px; transition: transform 200ms ease;">
                {!! $icon !!}
            </div>

            <div class="d-flex align-items-center gap-2">
                <!-- Action Required Notification Badge (Hidden when 0) -->
                @if($actionCount > 0)
                <span class="badge bg-danger text-white rounded-pill px-2 py-1 shadow-sm font-monospace" style="font-size: 0.7rem;">
                    {{ $actionCount }}
                </span>
                @endif

                <!-- Standard Category Badge -->
                <span class="badge border rounded-pill px-3 py-2 fw-semibold small {{ $style['badge'] }}">
                    {{ $badge }}
                </span>
            </div>
        </div>

        <!-- Module Title -->
        <h4 class="fw-bold mb-2 text-body">{{ $title }}</h4>

        <!-- Description (Clean, concise) -->
        <p class="small text-muted mb-0" style="line-height: 1.5;">
            {{ $description }}
        </p>

        <!-- Standardized Launch Station Link Footer -->
        <div class="mt-auto pt-3 d-flex align-items-center gap-1 text-primary fw-semibold small">
            <span>Launch Station</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/>
            </svg>
        </div>
    </div>
</div>

<style>
.portal-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.07) !important;
}
.portal-card:hover .portal-icon-wrapper {
    transform: scale(1.05);
}
</style>
