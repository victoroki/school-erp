@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Inventory Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Inventory</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <!-- Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>KES {{ number_format($totalValue, 2) }}</h3>
                        <p>Total Inventory Value</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <a href="{{ route('inventory-items.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $itemsInStock }}</h3>
                        <p>Items In Stock</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <a href="{{ route('inventory-items.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $lowStockItems->count() }}</h3>
                        <p>Low Stock Alerts</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('inventory-items.index') }}?filter=low_stock" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $outOfStockItems->count() }}</h3>
                        <p>Out of Stock</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="{{ route('inventory-items.index') }}?filter=out_of_stock" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('inventory.add-stock.form') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-2"></i> Add Stock
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('inventory.issue-stock.form') }}" class="btn btn-warning btn-block">
                            <i class="fas fa-minus mr-2"></i> Issue Stock
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('inventory.adjust-stock.form') }}" class="btn btn-info btn-block">
                            <i class="fas fa-sync-alt mr-2"></i> Adjust Stock
                        </a>
                    </div>
                    <div class="col-md-3 col-sm-6 col-12">
                        <a href="{{ route('inventory.stock-movement-history') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-history mr-2"></i> Movement History
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Stock Movements</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Item</th>
                                        <th>Type</th>
                                        <th>Quantity</th>
                                        <th>By</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentTransactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                            <td>{{ $transaction->item->name ?? 'N/A' }}</td>
                                            <td>{!! $transaction->typeBadge !!}</td>
                                            <td>{{ $transaction->quantity }}</td>
                                            <td>{{ $transaction->handledBy->name ?? 'N/A' }}</td>
                                            <td>{{ $transaction->item->quantity ?? 0 }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No recent transactions</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock Items -->
        @if($lowStockItems->count() > 0)
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Low Stock Items</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Item</th>
                                            <th>Category</th>
                                            <th>Current Stock</th>
                                            <th>Minimum Level</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($lowStockItems as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->category->name ?? 'N/A' }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td>{{ $item->minimum_quantity }}</td>
                                                <td>
                                                    <span class="badge badge-warning">Low Stock</span>
                                                </td>
                                                <td>
                                                    <a href="{{ route('inventory.add-stock.form') }}?item={{ $item->item_id }}" class="btn btn-sm btn-primary">Add Stock</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection