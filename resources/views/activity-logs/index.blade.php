@extends('layouts.app')

@section('title', 'Activity Log - StockManager ERP')

@section('header', 'System Activity Logs')
@section('subheader', 'Audit trail of user logins, logouts, exports, profile edits, and system actions.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search activity event or module..." />
    </div>

    @if($activityLogs->isEmpty())
        <x-empty-state title="No Activity Logs" message="User interactions and system events will be recorded here." />
    @else
        <x-data-table :headers="['Timestamp', 'User', 'Event', 'Module', 'Description', 'IP Address']">
            @foreach($activityLogs as $log)
            <tr>
                <td class="text-muted small">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                <td class="fw-semibold text-body">{{ $log->user->name ?? 'Guest/System' }}</td>
                <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ strtoupper($log->event) }}</span></td>
                <td><code>{{ $log->module }}</code></td>
                <td class="text-muted small">{{ $log->description ?: 'N/A' }}</td>
                <td class="text-muted small"><code>{{ $log->ip_address }}</code></td>
            </tr>
            @endforeach
        </x-data-table>

        <div class="mt-3">
            {{ $activityLogs->links() }}
        </div>
    @endif
</div>
@endsection
