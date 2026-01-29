@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-boxes text-primary mr-2"></i>Inventory Categories</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <div class="btn-group mr-2 shadow-sm">
                        <a href="{{ route('inventory-categories.index') }}" class="btn btn-white {{ !request('type') ? 'active font-weight-bold' : '' }}">All</a>
                        <a href="{{ route('inventory-categories.index', ['type' => 'consumable']) }}" class="btn btn-white {{ request('type') == 'consumable' ? 'active font-weight-bold' : '' }}">Consumables</a>
                        <a href="{{ route('inventory-categories.index', ['type' => 'asset']) }}" class="btn btn-white {{ request('type') == 'asset' ? 'active font-weight-bold' : '' }}">Assets</a>
                    </div>
                    <a class="btn btn-primary shadow-sm" href="{{ route('inventory-categories.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add Category
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            @forelse($inventoryCategories as $inventoryCategory)
                @php
                    $totalValue = $inventoryCategory->inventoryItems->sum(function($item) {
                        return (float) ($item->quantity * $item->cost_per_unit);
                    });
                    $lowStockCount = $inventoryCategory->inventoryItems->filter(fn($i) => $i->quantity <= ($i->minimum_quantity ?? 0) && $i->quantity > 0)->count();
                    $outOfStockCount = $inventoryCategory->inventoryItems->filter(fn($i) => $i->quantity <= 0)->count();
                @endphp
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="card card-outline {{ $inventoryCategory->category_type == 'asset' ? 'card-warning' : 'card-success' }} h-100 shadow-sm border-0 transition-transform">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="icon-shape shadow-sm rounded-lg d-flex align-items-center justify-content-center {{ $inventoryCategory->category_type == 'asset' ? 'bg-warning-light text-warning' : 'bg-success-light text-success' }}" style="width: 45px; height: 45px;">
                                    <i class="fas {{ $inventoryCategory->icon ?? ($inventoryCategory->category_type == 'asset' ? 'fa-laptop' : 'fa-pencil-alt') }} fa-lg"></i>
                                </div>
                                <span class="badge {{ $inventoryCategory->category_type == 'asset' ? 'badge-warning' : 'badge-success' }} text-uppercase px-2 py-1">
                                    {{ $inventoryCategory->category_type }}
                                </span>
                            </div>
                            
                            <h5 class="font-weight-bold mb-1">{{ $inventoryCategory->name }}</h5>
                            <p class="text-muted small mb-3" style="height: 40px; overflow: hidden;">{{ $inventoryCategory->description ?: 'No description provided.' }}</p>
                            
                            <div class="row text-center bg-light rounded p-2 mb-3 mx-0">
                                <div class="col-6 border-right py-1">
                                    <div class="font-weight-bold text-dark h5 mb-0">{{ $inventoryCategory->inventory_items_count }}</div>
                                    <div class="small text-muted">Items</div>
                                </div>
                                <div class="col-6 py-1">
                                    <div class="font-weight-bold text-dark h5 mb-0">KES {{ number_format($totalValue, 0) }}</div>
                                    <div class="small text-muted">Value</div>
                                </div>
                            </div>

                            <div class="d-flex flex-column mb-1">
                                @if($outOfStockCount > 0)
                                    <div class="small text-danger mb-1"><i class="fas fa-times-circle mr-1"></i> {{ $outOfStockCount }} out of stock</div>
                                @endif
                                @if($lowStockCount > 0)
                                    <div class="small text-warning mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> {{ $lowStockCount }} running low</div>
                                @endif
                                @if($lowStockCount == 0 && $outOfStockCount == 0 && $inventoryCategory->inventory_items_count > 0)
                                    <div class="small text-success mb-1"><i class="fas fa-check-circle mr-1"></i> Stock levels optimal</div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-white d-flex justify-content-between align-items-center p-3">
                            <a href="{{ route('inventory-categories.show', [$inventoryCategory->category_id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                <i class="fas fa-chart-line mr-1"></i> Dashboard
                            </a>
                            <div class="btn-group">
                                <a href="{{ route('inventory-categories.edit', [$inventoryCategory->category_id]) }}" class="btn btn-link text-muted p-1 mr-2" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                {!! Form::open(['route' => ['inventory-categories.destroy', $inventoryCategory->category_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-link text-danger p-1', 'onclick' => "return confirm('Are you sure?')", 'title' => 'Delete']) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-folder-open fa-4x mb-3 text-light"></i>
                        <h4 class="font-weight-light">No Categories Yet</h4>
                        <p>Organize your inventory by creating categories like Stationery, Lab Equipment, etc.</p>
                        <a href="{{ route('inventory-categories.create') }}" class="btn btn-primary mt-3">Create First Category</a>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $inventoryCategories->appends(request()->query())->links() }}
        </div>
    </div>

    <style>
        .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
        .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
        .transition-transform { transition: transform 0.2s; }
        .transition-transform:hover { transform: translateY(-5px); }
        .btn-white { background: white; border: 1px solid #dee2e6; }
        .btn-white.active { background: #f8f9fa; border-color: #adb5bd; }
    </style>

@endsection
