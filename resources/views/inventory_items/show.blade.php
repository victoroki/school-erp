@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas {{ $inventoryItem->asset_tag ? 'fa-desktop' : 'fa-box' }} text-primary mr-2"></i>{{ $inventoryItem->name }}</h1>
                    <small class="text-muted ml-5">{{ $inventoryItem->item_code }}</small>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('inventory-items.index') }}">
                        <i class="fas fa-chevron-left mr-1"></i> Back to Inventory
                    </a>
                    <a class="btn btn-info float-right mr-2" href="{{ route('inventory-items.edit', $inventoryItem->item_id) }}">
                        <i class="fas fa-edit mr-1"></i> Edit Item
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <!-- Left Column: Details & Specifics -->
            <div class="col-md-4">
                <!-- Item Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            @if($inventoryItem->photo)
                                <img src="{{ Storage::url($inventoryItem->photo) }}" class="img-fluid rounded shadow-sm" style="max-height: 150px;" alt="photo">
                            @else
                                <div class="bg-light rounded d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 120px; height: 120px;">
                                    <i class="fas {{ $inventoryItem->asset_tag ? 'fa-desktop' : 'fa-box' }} fa-4x text-light"></i>
                                </div>
                            @endif
                        </div>
                        <h5 class="font-weight-bold mb-0">{{ $inventoryItem->name }}</h5>
                        <p class="text-muted small mb-3">{{ $inventoryItem->category ? $inventoryItem->category->name : 'Uncategorized' }}</p>
                        
                        <div class="d-flex justify-content-center mb-3">
                            <span class="badge {{ $inventoryItem->asset_tag ? 'badge-warning' : 'badge-success' }} text-uppercase px-3 py-2">
                                {{ $inventoryItem->asset_tag ? 'Asset' : 'Consumable' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-light p-0">
                        <div class="row m-0">
                            <div class="col-6 border-right py-3 text-center">
                                <div class="font-weight-bold h5 mb-0 text-primary">KES {{ number_format($inventoryItem->cost_per_unit, 2) }}</div>
                                <div class="small text-muted">Unit Price</div>
                            </div>
                            <div class="col-6 py-3 text-center">
                                <div class="font-weight-bold h5 mb-0 text-dark">KES {{ number_format($inventoryItem->quantity * $inventoryItem->cost_per_unit, 0) }}</div>
                                <div class="small text-muted">Total Value</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Asset Assignment Info (If Asset) -->
                @if($inventoryItem->asset_tag)
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="font-weight-bold mb-0"><i class="fas fa-user-tag mr-2 text-warning"></i>Current Assignment</h6>
                        </div>
                        <div class="card-body py-2">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th class="text-muted font-weight-normal small">Condition:</th>
                                    <td><span class="badge badge-outline-{{ $inventoryItem->current_condition == 'Excellent' ? 'success' : 'info' }}">{{ $inventoryItem->current_condition }}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted font-weight-normal small">Location:</th>
                                    <td class="small">{{ $inventoryItem->location ?: 'Main Store' }}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted font-weight-normal small">Assigned To:</th>
                                    <td class="small font-weight-bold">{{ $inventoryItem->assigned_to ?: 'Unassigned' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="card-footer bg-white text-center">
                            <button class="btn btn-outline-warning btn-block btn-sm rounded-pill"><i class="fas fa-exchange-alt mr-1"></i> Transfer / Assign</button>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="font-weight-bold mb-0"><i class="fas fa-shield-alt mr-2 text-info"></i>Warranty & Maintenance</h6>
                        </div>
                        <div class="card-body small">
                            <div class="mb-2"><b>Warranty:</b> {{ $inventoryItem->warranty_period }} Months</div>
                            <div class="mb-2"><b>Expires:</b> {{ $inventoryItem->warranty_expiry ? $inventoryItem->warranty_expiry->format('d M Y') : 'N/A' }}</div>
                            <div class="mb-0"><b>Next Maintenance:</b> {{ $inventoryItem->next_maintenance_due ? $inventoryItem->next_maintenance_due->format('d M Y') : 'None scheduled' }}</div>
                        </div>
                    </div>
                @else
                    <!-- Consumable Info -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h6 class="font-weight-bold mb-0"><i class="fas fa-truck mr-2 text-success"></i>Supplier Details</h6>
                        </div>
                        <div class="card-body py-3">
                            @if($inventoryItem->supplier)
                                <h6 class="font-weight-bold mb-1">{{ $inventoryItem->supplier->name }}</h6>
                                <p class="small text-muted mb-2"><i class="fas fa-phone mr-1"></i> {{ $inventoryItem->supplier->phone ?: 'No phone' }}</p>
                                <a href="{{ route('suppliers.show', $inventoryItem->supplier_id) }}" class="btn btn-xs btn-outline-success rounded-pill px-3">View Supplier</a>
                            @else
                                <p class="text-muted small mb-0 font-italic">No supplier assigned.</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right Column: Dynamics -->
            <div class="col-md-8">
                <!-- Stock Level Dashboard (For Consumables) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-chart-bar mr-2 text-primary"></i>Stock Level Overview</h6>
                        <div class="card-tools">
                            @if(!$inventoryItem->asset_tag)
                                <a href="{{ route('inventory.add-stock.form', ['item_id' => $inventoryItem->item_id]) }}" class="btn btn-sm btn-success rounded-pill px-3"><i class="fas fa-plus mr-1"></i> Add Stock</a>
                                <a href="{{ route('inventory.issue-stock.form', ['item_id' => $inventoryItem->item_id]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3"><i class="fas fa-paper-plane mr-1"></i> Issue</a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <div class="text-center py-3">
                                    <h1 class="display-4 font-weight-bold mb-0 {{ $inventoryItem->quantity <= $inventoryItem->minimum_quantity ? 'text-danger' : 'text-success' }}">
                                        {{ $inventoryItem->quantity }}
                                    </h1>
                                    <p class="text-muted text-uppercase small font-weight-bold">{{ $inventoryItem->unit ?: 'Items' }} In Stock</p>
                                </div>
                            </div>
                            <div class="col-md-6 pl-md-4 py-2">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span class="text-muted">Min Alert Level:</span>
                                        <span class="font-weight-bold">{{ $inventoryItem->minimum_quantity ?: 0 }}</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        @php
                                            $totalCapacity = max($inventoryItem->quantity, ($inventoryItem->minimum_quantity ?? 0) * 2);
                                            $minPct = $totalCapacity > 0 ? (($inventoryItem->minimum_quantity ?? 0) / $totalCapacity) * 100 : 0;
                                            $stockPct = $totalCapacity > 0 ? ($inventoryItem->quantity / $totalCapacity) * 100 : 0;
                                            $barColor = $inventoryItem->quantity <= $inventoryItem->minimum_quantity ? 'bg-danger' : 'bg-success';
                                        @endphp
                                        <div class="progress-bar {{ $barColor }}" style="width: {{ $stockPct }}%"></div>
                                    </div>
                                </div>
                                <div class="bg-light rounded p-3 small">
                                    <i class="fas fa-info-circle text-info mr-2"></i>
                                    @if($inventoryItem->isOutOfStock())
                                        This item is <b class="text-danger">Out of Stock</b>. Purchase order recommended.
                                    @elseif($inventoryItem->isLowStock())
                                        Stock is <b class="text-warning">Running Low</b> (below min level of {{ $inventoryItem->minimum_quantity }}).
                                    @else
                                        Stock level is <b class="text-success">Good</b>. Next reorder expected at {{ $inventoryItem->minimum_quantity ?: 0 }} units.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Movement History -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-history mr-2 text-secondary"></i>Recent Movement History</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="bg-light small uppercase">
                                    <tr>
                                        <th class="pl-3">Date</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>From/To</th>
                                        <th>Balance</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inventoryItem->transactions as $trans)
                                        <tr class="small">
                                            <td class="pl-3">{{ $trans->transaction_date->format('d M, Y') }}</td>
                                            <td>
                                                <span class="badge badge-pill {{ $trans->transaction_type == 'purchase' ? 'badge-success' : ($trans->transaction_type == 'issue' ? 'badge-primary' : 'badge-secondary') }}">
                                                    {{ ucfirst($trans->transaction_type) }}
                                                </span>
                                            </td>
                                            <td class="font-weight-bold">{{ $trans->transaction_type == 'issue' ? '-' : '+' }}{{ $trans->quantity }}</td>
                                            <td>{{ $trans->remarks ?: '-' }}</td>
                                            <td class="text-muted">{{ $trans->balance_after }}</td>
                                            <td>{{ $trans->user ? $trans->user->name : '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No movement history recorded yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
