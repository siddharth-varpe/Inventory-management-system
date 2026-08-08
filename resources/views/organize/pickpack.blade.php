@extends('layouts.app')

@section('title', 'Pick & Pack Station - Organize Stock')

@section('header', 'Pick & Pack Station')
@section('subheader', 'Verify order items against picking checklist, reduce inventory balances, and issue dispatch tasks for transport.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('organize.dashboard') }}" class="text-decoration-none">Organize Stock</a></li>
    <li class="breadcrumb-item active" aria-current="page">Pick & Pack</li>
@endsection

@section('content')
<div class="row g-4">
    <!-- Left Column: Sidebar -->
    <div class="col-12 col-lg-3">
        <x-organize-sidebar />
    </div>

    <!-- Right Column: Master-Detail Workspace -->
    <div class="col-12 col-lg-9">
        <x-master-detail-layout queueTitle="Picking Queue" queueSubtitle="Sorted by Priority -> FIFO">
            <x-slot:queueContent>
                @if($tasks->isEmpty())
                    <div class="text-center text-muted py-4">No pending picking tasks in queue.</div>
                @else
                    @foreach($tasks as $t)
                        <x-task-card 
                            :title="'Order ' . $t->order_reference"
                            :subtitle="($t->customer_name ?: 'Customer') . ' | Items: ' . $t->items->count()"
                            :priority="$t->priority"
                            :status="$t->status"
                            :active="($selectedTask->id ?? 0) === $t->id"
                            :url="route('organize.pickpack.index', ['task_id' => $t->id])"
                        />
                    @endforeach
                @endif
            </x-slot:queueContent>

            <x-slot:canvasContent>
                @if($selectedTask)
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                        <div>
                            <span class="badge bg-success-subtle text-success mb-1">Picking Task #{{ $selectedTask->task_number }}</span>
                            <h4 class="fw-bold text-body mb-0">Order Reference: {{ $selectedTask->order_reference }}</h4>
                            <div class="text-muted small">Customer: <strong>{{ $selectedTask->customer_name ?? 'General Customer' }}</strong></div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <x-priority-badge :priority="$selectedTask->priority" />
                            @if($selectedTask->is_fragile)
                                <span class="badge bg-warning text-dark">⚠️ Fragile</span>
                            @endif
                            @if($selectedTask->is_cold_chain)
                                <span class="badge bg-info text-white">❄️ Cold Chain</span>
                            @endif
                        </div>
                    </div>

                    <!-- Verification Checklist -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-body mb-2">Item Verification Checklist</h6>
                        <p class="text-muted small mb-3">Pickers must verify every item line in the order before dispatch is enabled.</p>
                        
                        <x-checklist :items="$selectedTask->items" :taskId="$selectedTask->id" />
                    </div>

                    <!-- Dispatch Completion Button -->
                    <div class="card p-3 rounded-4 bg-body-tertiary border">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-body">Dispatch Verification Status</div>
                                <div class="text-muted small">
                                    @if($selectedTask->is_all_verified)
                                        <span class="text-success fw-bold">✔ All checklist items verified. Ready for dispatch!</span>
                                    @else
                                        <span class="text-danger fw-semibold">✖ Verification incomplete. Verify remaining items above.</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                @if($selectedTask->is_all_verified)
                                    <form action="{{ route('organize.pickpack.complete', $selectedTask->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success rounded-3 px-4 py-2 fw-bold d-flex align-items-center gap-2">
                                            <span>Complete Dispatch & Forward to Transport Portal</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-right" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8"/></svg>
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-secondary rounded-3 px-4 py-2 fw-bold" disabled>Verify All Items to Complete</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-5">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-card-checklist text-muted mb-2" viewBox="0 0 16 16"><path d="M14.5 3a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5zm-13-1A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2z"/><path d="M7 5.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 1 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0M7 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m-1.496-.854a.5.5 0 0 1 0 .708l-1.5 1.5a.5.5 0 0 1-.708 0l-.5-.5a.5.5 0 0 1 .708-.708l.146.147 1.146-1.147a.5.5 0 0 1 .708 0"/></svg>
                        <p class="mb-0">Select an order task from the left picking queue panel to start checklist verification.</p>
                    </div>
                @endif
            </x-slot:canvasContent>
        </x-master-detail-layout>
    </div>
</div>
@endsection
