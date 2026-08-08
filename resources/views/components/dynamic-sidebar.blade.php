@props(['portal' => 'stock'])

@php
    /** @var \App\Core\Workspace\Builder\WorkspaceBuilderService $workspaceBuilder */
    $workspaceBuilder = app(\App\Core\Workspace\Builder\WorkspaceBuilderService::class);
    $config = $workspaceBuilder->buildWorkspace(auth()->user(), $portal);
@endphp

<div class="card p-3 rounded-4 shadow-sm border-translucent bg-body">
    <div class="d-flex align-items-center gap-2 mb-3 px-2 border-bottom pb-2">
        <div class="p-2 bg-primary-subtle text-primary rounded-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-person-badge" viewBox="0 0 16 16"><path d="M6.5 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1zM11 8a3 3 0 1 1-6 0 3 3 0 0 1 6 0"/><path d="M4.5 0A1.5 1.5 0 0 0 3 1.5v13A1.5 1.5 0 0 0 4.5 16h7a1.5 1.5 0 0 0 1.5-1.5v-13A1.5 1.5 0 0 0 11.5 0zM1 1.5A2.5 2.5 0 0 1 3.5 0h9A2.5 2.5 0 0 1 15 1.5v13a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 14.5z"/></svg>
        </div>
        <div>
            <h6 class="fw-bold text-body mb-0">{{ $config->profileName }}</h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size: 0.65rem;">{{ strtoupper($config->layoutType) }} LAYOUT</span>
        </div>
    </div>

    <div class="nav flex-column nav-pills gap-1">
        @foreach($config->sidebarItems as $item)
            <a href="{{ route($item['route']) }}" class="nav-link rounded-3 fw-semibold d-flex align-items-center gap-2 {{ request()->routeIs($item['active_pattern']) ? 'active' : '' }}">
                <span>{{ $item['icon'] }}</span>
                <span>{{ $item['name'] }}</span>
            </a>
        @endforeach
    </div>
</div>
