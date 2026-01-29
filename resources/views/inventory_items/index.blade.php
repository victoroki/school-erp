@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-boxes text-info mr-2"></i>Smart Inventory View</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary shadow-sm" href="{{ route('inventory-items.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Item
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-primary-light text-primary rounded-circle mr-3">
                                <i class="fas fa-coins fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small uppercase font-weight-bold">Total Inventory Value</h6>
                                <h4 class="font-weight-bold mb-0">KES {{ number_format($stats['total_value'], 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm border-0 bg-white">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-shape bg-success-light text-success rounded-circle mr-3">
                                <i class="fas fa-cubes fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0 small uppercase font-weight-bold">Items In Stock</h6>
                                <h4 class="font-weight-bold mb-0">{{ number_format($stats['items_count'], 0) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('inventory-items.index', ['status' => 'low_stock']) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 {{ $stats['low_stock'] > 0 ? 'bg-warning' : 'bg-white' }}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-shape {{ $stats['low_stock'] > 0 ? 'bg-white text-warning' : 'bg-warning-light text-warning' }} rounded-circle mr-3">
                                    <i class="fas fa-exclamation-triangle fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="{{ $stats['low_stock'] > 0 ? 'text-white' : 'text-muted' }} mb-0 small uppercase font-weight-bold">Low Stock Alerts</h6>
                                    <h4 class="font-weight-bold mb-0 {{ $stats['low_stock'] > 0 ? 'text-white' : '' }}">{{ $stats['low_stock'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-3 col-sm-6">
                <a href="{{ route('inventory-items.index', ['status' => 'out_of_stock']) }}" class="text-decoration-none">
                    <div class="card shadow-sm border-0 {{ $stats['out_of_stock'] > 0 ? 'bg-danger' : 'bg-white' }}">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center">
                                <div class="icon-shape {{ $stats['out_of_stock'] > 0 ? 'bg-white text-danger' : 'bg-danger-light text-danger' }} rounded-circle mr-3">
                                    <i class="fas fa-times-circle fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="{{ $stats['out_of_stock'] > 0 ? 'text-white' : 'text-muted' }} mb-0 small uppercase font-weight-bold">Out of Stock</h6>
                                    <h4 class="font-weight-bold mb-0 {{ $stats['out_of_stock'] > 0 ? 'text-white' : '' }}">{{ $stats['out_of_stock'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Advanced Search and Tabs -->
        <div class="card shadow-sm border-0 border-top-primary">
            <div class="card-header bg-white pt-3 px-3">
                <div class="d-flex justify-content-between align-items-end flex-wrap">
                    <ul class="nav nav-tabs border-0 mt-0" id="inventoryTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('type') && !request('status') ? 'active font-weight-bold' : 'text-muted' }} border-0 px-4" href="{{ route('inventory-items.index') }}">All Items</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('type') == 'consumable' ? 'active font-weight-bold' : 'text-muted' }} border-0 px-4" href="{{ route('inventory-items.index', ['type' => 'consumable']) }}">Consumables Only</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('type') == 'asset' ? 'active font-weight-bold' : 'text-muted' }} border-0 px-4" href="{{ route('inventory-items.index', ['type' => 'asset']) }}">Assets Only</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request('status') == 'low_stock' ? 'active font-weight-bold' : 'text-muted' }} border-0 px-4" href="{{ route('inventory-items.index', ['status' => 'low_stock']) }}">Low Stock</a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card-body p-3">
                <!-- Filters -->
                <form action="{{ route('inventory-items.index') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="search" class="form-control border-left-0" placeholder="Search by name, code, or asset tag..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            {!! Form::select('category_id', ['' => 'All Categories'] + $categories->toArray(), request('category_id'), ['class' => 'form-control select2']) !!}
                        </div>
                        <div class="col-md-3">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary px-4">Search</button>
                                <a href="{{ route('inventory-items.index') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Inventory List -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0">
                        <thead>
                            <tr class="text-muted small uppercase">
                                <th class="border-top-0 px-3">Item Info</th>
                                <th class="border-top-0">Category</th>
                                <th class="border-top-0">Stock Level</th>
                                <th class="border-top-0">Value / Cost</th>
                                <th class="border-top-0">Location</th>
                                <th class="border-top-0 text-right pr-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventoryItems as $item)
                                <tr>
                                    <td class="px-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 mr-3 text-center" style="width: 45px; height: 45px;">
                                                @if($item->photo)
                                                    <img src="{{ Storage::url($item->photo) }}" class="img-fluid rounded" alt="item">
                                                @else
                                                    <i class="fas {{ $item->category_type == 'asset' ? 'fa-desktop text-warning' : 'fa-box text-success' }} fa-lg mt-2"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-weight-bold text-dark">{{ $item->name }}</div>
                                                <div class="small text-muted">
                                                    Code: <span class="bg-light px-1 rounded">{{ $item->item_code }}</span>
                                                    @if($item->asset_tag)
                                                        | Tag: <span class="bg-light px-1 rounded">{{ $item->asset_tag }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light border text-muted px-2 py-1">
                                            {{ $item->category ? $item->category->name : 'Uncategorized' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="mb-1 d-flex justify-content-between pr-4">
                                            <span class="small font-weight-bold">{{ $item->quantity }} {{ $item->unit }}</span>
                                            @if($item->isOutOfStock())
                                                <span class="badge badge-danger">Out of Stock</span>
                                            @elseif($item->isLowStock())
                                                <span class="badge badge-warning">Low Stock</span>
                                            @else
                                                <span class="badge badge-success">In Stock</span>
                                            @endif
                                        </div>
                                        <div class="progress progress-xxs mr-4" style="height: 4px;">
                                            @php
                                                $stockPct = min(($item->quantity / max(1, ($item->minimum_quantity * 2))) * 100, 100);
                                                $color = $item->isOutOfStock() ? 'bg-danger' : ($item->isLowStock() ? 'bg-warning' : 'bg-success');
                                            @endphp
                                            <div class="progress-bar {{ $color }}" style="width: {{ $stockPct }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small"><b>Unit:</b> KES {{ number_format($item->cost_per_unit, 2) }}</div>
                                        <div class="small text-primary font-weight-bold"><b>Total:</b> KES {{ number_format($item->quantity * $item->cost_per_unit, 0) }}</div>
                                    </td>
                                    <td>
                                        <div class="small"><i class="fas fa-map-marker-alt text-muted mr-1"></i> {{ $item->location ?: 'N/A' }}</div>
                                    </td>
                                    <td class="text-right pr-3">
                                        <div class="btn-group">
                                            <a href="{{ route('inventory-items.show', $item->item_id) }}" class="btn btn-default btn-sm shadow-sm p-2" title="View Details">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            <a href="{{ route('inventory-items.edit', $item->item_id) }}" class="btn btn-default btn-sm shadow-sm p-2" title="Edit Item">
                                                <i class="fas fa-edit text-info"></i>
                                            </a>
                                            {!! Form::open(['route' => ['inventory-items.destroy', $item->item_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                            {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-default btn-sm shadow-sm text-danger p-2', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-search fa-3x mb-3 text-light"></i>
                                            <h5>No items found</h5>
                                            <p>Try adjusting your search filters or add a new item.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-center mt-3">
                    {{ $inventoryItems->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    <style>
        .icon-shape { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
        .bg-primary-light { background-color: rgba(0, 123, 255, 0.1); }
        .bg-success-light { background-color: rgba(40, 167, 69, 0.1); }
        .bg-warning-light { background-color: rgba(255, 193, 7, 0.1); }
        .bg-danger-light { background-color: rgba(220, 53, 69, 0.1); }
        .nav-tabs .nav-link.active { border-bottom: 2px solid #007bff !important; background: transparent; }
        .uppercase { text-transform: uppercase; letter-spacing: 0.5px; }
        .border-top-primary { border-top: 3px solid #007bff !important; }
        .table align-middle td { vertical-align: middle; }
    </style>
@endsection
