@extends('layouts.app')

@section('title', 'Audit Log - StockManager ERP')

@section('header', 'Database Audit Logs')
@section('subheader', 'Detailed audit trail of model creations, updates, and deletions with structural diffs.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Audit Logs</li>
@endsection

@section('content')
<div class="card p-3 mb-4">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3">
        <x-search-bar placeholder="Search table name or model..." />
    </div>

    @if($auditLogs->isEmpty())
        <x-empty-state title="No Audit Logs" message="Database modifications will be captured automatically here." />
    @else
        <x-data-table :headers="['Timestamp', 'User', 'Action', 'Table', 'Record ID', 'Diff Viewer']">
            @foreach($auditLogs as $log)
            <tr>
                <td class="text-muted small">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                <td class="fw-semibold text-body">{{ $log->user->name ?? 'System' }}</td>
                <td>
                    <span class="badge {{ $log->action == 'created' ? 'bg-success-subtle text-success' : ($log->action == 'updated' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }} border rounded-pill">
                        {{ strtoupper($log->action) }}
                    </span>
                </td>
                <td><code>{{ $log->table_name }}</code></td>
                <td><code>#{{ $log->record_id }}</code></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#diffModal{{ $log->id }}">
                        View Diff
                    </button>
                </td>
            </tr>

            <!-- Diff Viewer Modal -->
            <div class="modal fade" id="diffModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-translucent shadow">
                        <div class="modal-header border-bottom border-translucent">
                            <h5 class="modal-title fw-bold text-body">Audit Diff Log #{{ $log->id }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted mb-2">Old Values</h6>
                                    <pre class="bg-body-tertiary p-3 rounded border text-danger small" style="max-height: 250px; overflow: auto;">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) ?: 'N/A' }}</pre>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted mb-2">New Values</h6>
                                    <pre class="bg-body-tertiary p-3 rounded border text-success small" style="max-height: 250px; overflow: auto;">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) ?: 'N/A' }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </x-data-table>

        <div class="mt-3">
            {{ $auditLogs->links() }}
        </div>
    @endif
</div>
@endsection
