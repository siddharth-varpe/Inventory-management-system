@extends('layouts.app')

@section('title', 'Category Tree Hierarchy - StockManager ERP')

@section('header', 'Category Hierarchy Tree')
@section('subheader', 'Visual nested tree view of product categories and sub-categories.')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('stock.dashboard') }}" class="text-decoration-none">Manage Stock</a></li>
    <li class="breadcrumb-item"><a href="{{ route('categories.index') }}" class="text-decoration-none">Categories</a></li>
    <li class="breadcrumb-item active" aria-current="page">Tree View</li>
@endsection

@section('header-actions')
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">Switch to List View</a>
@endsection

@section('content')
<div class="card p-4 mb-4">
    <h5 class="fw-bold text-body mb-3">Hierarchical Category Structure</h5>

    @if($tree->isEmpty())
        <x-empty-state title="No Categories Defined" message="Create categories to view the hierarchy tree." />
    @else
        <div class="category-tree">
            <ul class="list-group list-group-flush border-0">
                @foreach($tree as $node)
                    @include('categories.partials.tree_node', ['category' => $node])
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
