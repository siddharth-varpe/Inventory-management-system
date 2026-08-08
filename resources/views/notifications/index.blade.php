@extends('layouts.app')

@section('title', 'Notifications Center - StockManager ERP')

@section('header', 'Notifications Center')
@section('subheader', 'View system notices, workflow triggers, and enterprise alerts.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Notifications</li>
@endsection

@section('content')
<div class="card p-3 mb-4">
    @if(empty($notifications) || $notifications->isEmpty())
        <x-empty-state title="All Caught Up!" message="You have no unread system notifications." />
    @else
        <div class="list-group list-group-flush border-0">
            @foreach($notifications as $notification)
            <div class="list-group-item d-flex align-items-center justify-content-between py-3 border-bottom border-translucent">
                <div>
                    <h6 class="fw-bold mb-1 text-body">{{ $notification->data['title'] ?? 'System Notification' }}</h6>
                    <p class="text-muted small mb-0">{{ $notification->data['message'] ?? '' }}</p>
                    <span class="text-muted small" style="font-size: 0.75rem;">{{ $notification->created_at->diffForHumans() }}</span>
                </div>
                @if(!$notification->read_at)
                    <form action="{{ route('notifications.read', $notification->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-primary">Mark Read</button>
                    </form>
                @endif
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
