@props(['model'])

@php
    $service = app(App\Services\Timeline\UniversalTimelineService::class);
    $events = $service->getTimelineEvents($model);
@endphp

<div class="universal-timeline-container p-2">
    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
        <h6 class="fw-bold text-body mb-0">Universal Workflow Timeline</h6>
        <span class="badge bg-secondary font-monospace" style="font-size: 0.7rem;">{{ count($events) }} Events Recorded</span>
    </div>

    @if(empty($events))
        <div class="text-center py-4 text-muted small">No workflow timeline events recorded.</div>
    @else
        <div class="position-relative ps-4" style="border-left: 2px solid #e2e8f0; margin-left: 10px;">
            @foreach($events as $evt)
                <div class="mb-4 position-relative">
                    <!-- Icon Dot Marker -->
                    <div class="position-absolute d-flex align-items-center justify-content-center rounded-circle shadow-sm"
                         style="left: -33px; top: 0; width: 26px; height: 26px; background: #ffffff; border: 2px solid #cbd5e1; font-size: 0.75rem;">
                        {{ $evt['icon'] }}
                    </div>

                    <!-- Event Card -->
                    <div class="card p-3 rounded-3 border-translucent shadow-xs bg-body ms-2">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge {{ $evt['badge_color'] }} font-monospace" style="font-size: 0.65rem;">{{ strtoupper($evt['status']) }}</span>
                                <span class="fw-bold text-body small">{{ $evt['title'] }}</span>
                            </div>
                            <span class="text-muted font-monospace" style="font-size: 0.7rem;">{{ $evt['date'] }} {{ $evt['time'] }}</span>
                        </div>
                        <p class="small text-muted mb-1">{{ $evt['description'] }}</p>
                        <div class="d-flex align-items-center justify-content-between pt-1 border-top border-translucent" style="font-size: 0.7rem;">
                            <span class="text-secondary">Executed by: <strong>{{ $evt['user_name'] }}</strong></span>
                            <span class="badge bg-light text-dark border">{{ ucfirst($evt['event_type']) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
