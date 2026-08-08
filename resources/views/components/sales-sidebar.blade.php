@props(['activeTab' => 'dashboard'])

@php
    $sidebarBuilder = new \App\Core\Workspace\Sidebar\SidebarBuilder();
    $navItems = $sidebarBuilder->build(auth()->user(), 'sales');
@endphp

<aside class="sidebar-desktop bg-body border-end border-translucent p-3 d-none d-lg-flex flex-column justify-content-between" style="width: 260px; min-height: calc(100vh - 75px);">
    <div>
        <!-- Portal Header -->
        <div class="px-3 py-2 mb-3 bg-warning-subtle text-warning-emphasis rounded-3 border border-warning-subtle d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-graph-up-arrow text-warning" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.707l-4.146 4.147a.5.5 0 0 1-.708 0L7 6.707l-2.646 2.647a.5.5 0 0 1-.708-.708l3-3a.5.5 0 0 1 .708 0L9.5 7.293l3.646-3.647H10.5a.5.5 0 0 1-.5-.5z"/>
                </svg>
                <span class="fw-bold text-uppercase small tracking-wider">Sales & CRM</span>
            </div>
            <span class="badge bg-warning text-dark font-monospace" style="font-size: 0.65rem;">ACTIVE</span>
        </div>

        <!-- Sidebar Navigation Menu -->
        <div class="nav flex-column nav-pills gap-1">
            @foreach($navItems as $item)
                @php
                    $isActive = request()->routeIs($item['active_pattern']);
                @endphp
                <a href="{{ route($item['route']) }}" 
                   class="nav-link d-flex align-items-center gap-3 px-3 py-2.5 rounded-3 fw-semibold transition-all {{ $isActive ? 'active bg-warning text-dark shadow-sm' : 'text-body-secondary hover-bg-light' }}">
                    <span class="fs-5">{{ $item['icon'] }}</span>
                    <span>{{ $item['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>

    <!-- Quick Return Link -->
    <div class="pt-3 border-top border-translucent">
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm w-100 rounded-3 d-flex align-items-center justify-content-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-left" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8"/>
            </svg>
            <span>Enterprise Dashboard</span>
        </a>
    </div>
</aside>
