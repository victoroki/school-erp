@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas {{ $inventoryCategory->icon ?: 'fa-folder' }} text-primary mr-2"></i>{{ $inventoryCategory->name }} Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('inventory-categories.index') }}">
                        <i class="fas fa-chevron-left mr-1"></i> Back to Categories
                    </a>
                    <a class="btn btn-primary float-right mr-2" href="{{ route('inventory-items.create', ['category_id' => $inventoryCategory->category_id]) }}">
                        <i class="fas fa-plus mr-1"></i> Add Item
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Summary Stats -->
            @php
                $items = $inventoryCategory->inventoryItems;
                $totalStock = $items->sum('quantity');
                $totalValue = $items->sum(fn($i) => $i->quantity * $i->cost_per_unit);
                $lowStockItems = $items->filter(fn($i) => $i->quantity <= ($i->minimum_quantity ?? 0) && $i->quantity > 0);
                $outOfStockItems = $items->filter(fn($i) => $i->quantity <= 0);
            @endphp

            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow-sm">
                    <div class="inner">
                        <h3>{{ $items->count() }}</h3>
                        <p>Total Item Types</p>
                    </div>
                    <div class="icon"><i class="fas fa-tag"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow-sm">
                    <div class="inner">
                        <h3>KES {{ number_format($totalValue, 0) }}</h3>
                        <p>Total Category Value</p>
                    </div>
                    <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning shadow-sm">
                    <div class="inner">
                        <h3>{{ $lowStockItems->count() }}</h3>
                        <p>Low Stock Alerts</p>
                    </div>
                    <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger shadow-sm">
                    <div class="inner">
                        <h3>{{ $outOfStockItems->count() }}</h3>
                        <p>Out of Stock</p>
                    </div>
                    <div class="icon"><i class="fas fa-times-circle"></i></div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Category Info -->
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow-sm border-0">
                    <div class="card-header">
                        <h3 class="card-title">Category Information</h3>
                        <div class="card-tools">
                            <a href="{{ route('inventory-categories.edit', $inventoryCategory->category_id) }}" class="btn btn-tool text-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm">
                            <tr>
                                <th class="pl-3 py-2">Code:</th>
                                <td class="py-2"><span class="badge badge-light border">{{ $inventoryCategory->code ?: 'N/A' }}</span></td>
                            </tr>
                            <tr>
                                <th class="pl-3 py-2">Type:</th>
                                <td class="py-2"><span class="badge {{ $inventoryCategory->category_type == 'asset' ? 'badge-warning' : 'badge-success' }}">{{ ucfirst($inventoryCategory->category_type) }}</span></td>
                            </tr>
                            <tr>
                                <th class="pl-3 py-2">Location:</th>
                                <td class="py-2 text-muted">{{ $inventoryCategory->default_location ?: 'Not specified' }}</td>
                            </tr>
                            <tr>
                                <th class="pl-3 py-2">Trackable:</th>
                                <td class="py-2">
                                    <i class="fas {{ $inventoryCategory->trackable ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' }} mr-1"></i>
                                    {{ $inventoryCategory->trackable ? 'Yes' : 'No' }}
                                </td>
                            </tr>
                        </table>
                        <div class="p-3">
                            <label class="small text-uppercase text-muted d-block">Description</label>
                            <p class="mb-0 small text-dark">{{ $inventoryCategory->description ?: 'No description available for this category.' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="col-md-8">
                <div class="card card-white shadow-sm border-0">
                    <div class="card-header border-bottom-0">
                        <h3 class="card-title">Items in this Category</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0">Item Name</th>
                                        <th class="border-0">Stock Level</th>
                                        <th class="border-0">Values</th>
                                        <th class="border-0 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $item)
                                        @php
                                            $stockPercentage = $item->minimum_quantity > 0 ? ($item->quantity / ($item->minimum_quantity * 2)) * 100 : 100;
                                            $stockPercentage = min($stockPercentage, 100);
                                            $progressClass = 'bg-success';
                                            if ($item->quantity <= 0) { $progressClass = 'bg-danger'; }
                                            elseif ($item->quantity <= ($item->minimum_quantity ?? 0)) { $progressClass = 'bg-warning'; }
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="bg-light rounded p-2 mr-3" style="width: 40px; text-center">
                                                        <i class="fas fa-cube text-muted"></i>
                                                    </div>
                                                    <div>
                                                        <span class="d-block font-weight-bold">{{ $item->name }}</span>
                                                        <small class="text-muted">{{ $item->item_code }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td style="width: 200px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress progress-xs w-100 mr-2" style="height: 6px;">
                                                        <div class="progress-bar {{ $progressClass }}" style="width: {{ $stockPercentage }}%"></div>
                                                    </div>
                                                    <span class="small font-weight-bold">{{ $item->quantity }} {{ $item->unit }}</span>
                                                </div>
                                                <small class="text-muted">Min: {{ $item->minimum_quantity ?: 0 }}</small>
                                            </td>
                                            <td>
                                                <span class="d-block small"><b>Unit:</b> KES {{ number_format($item->cost_per_unit, 2) }}</span>
                                                <span class="d-block small text-primary"><b>Total:</b> KES {{ number_format($item->quantity * $item->cost_per_unit, 2) }}</span>
                                            </td>
                                            <td class="text-right">
                                                <div class="btn-group">
                                                    <a href="{{ route('inventory-items.show', $item->item_id) }}" class="btn btn-sm btn-default" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($inventoryCategory->category_type == 'consumable')
                                                        <a href="{{ route('inventory.add-stock.form', ['item_id' => $item->item_id]) }}" class="btn btn-sm btn-success" title="Add Stock">
                                                            <i class="fas fa-plus"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if($items->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No items found in this category.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection