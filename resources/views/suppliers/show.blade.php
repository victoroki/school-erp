@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-building text-primary mr-2"></i>{{ $supplier->name }} Profile</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default mr-2" href="{{ route('suppliers.index') }}">
                        <i class="fas fa-chevron-left mr-1"></i> Back to List
                    </a>
                    <a class="btn btn-info" href="{{ route('suppliers.edit', $supplier->supplier_id) }}">
                        <i class="fas fa-edit mr-1"></i> Edit Supplier
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Left Info Panel -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                                <i class="fas fa-truck-loading fa-3x text-primary shadow-sm"></i>
                            </div>
                            <h4 class="font-weight-bold mb-1">{{ $supplier->name }}</h4>
                            <div class="badge badge-light border text-muted px-3 py-2 mb-2">{{ $supplier->code }}</div>
                            <div class="mt-2 text-warning">
                                @for($i=1; $i<=5; $i++)
                                    <i class="fas fa-star {{ $i <= ($supplier->rating ?: 1) ? '' : 'text-light' }}"></i>
                                @endfor
                            </div>
                        </div>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item d-flex justify-content-between border-top-0 px-0">
                                <b class="text-muted"><i class="fas fa-user-circle mr-2"></i> Contact:</b>
                                <span>{{ $supplier->contact_person ?: 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <b class="text-muted"><i class="fas fa-phone-alt mr-2"></i> Phone:</b>
                                <span>{{ $supplier->phone ?: 'N/A' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <b class="text-muted"><i class="fas fa-envelope mr-2"></i> Email:</b>
                                <span class="text-lowercase">{{ $supplier->email ?: 'N/A' }}</span>
                            </li>
                            <li class="list-group-item px-0 border-bottom-0 pb-0">
                                <b class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt mr-2"></i> Address:</b>
                                <span class="small text-muted d-block">{{ $supplier->address ?: 'No address provided.' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-file-invoice-dollar mr-2 text-success"></i>Finance & Terms</h6>
                    </div>
                    <div class="card-body py-3">
                        <div class="mb-3 small">
                            <label class="text-muted d-block mb-0 font-weight-normal">Payment Terms:</label>
                            <span class="font-weight-bold text-dark">{{ $supplier->payment_terms }}</span>
                        </div>
                        <div class="mb-3 small">
                            <label class="text-muted d-block mb-0 font-weight-normal">Bank Information:</label>
                            <div class="bg-light p-2 rounded text-muted">
                                {!! nl2br(e($supplier->bank_details ?: 'No bank details recorded.')) !!}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-sticky-note mr-2 text-info"></i>Internal Notes</h6>
                    </div>
                    <div class="card-body py-3">
                        <p class="small text-muted italic mb-0">
                            "{{ $supplier->notes ?: 'No internal notes found for this supplier.' }}"
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Activity Panel -->
            <div class="col-md-8">
                <!-- Supplier Stats -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-primary text-white text-center p-3 mb-0 h-100">
                            <h3 class="mb-0 font-weight-bold">{{ $supplier->inventoryItems->count() }}</h3>
                            <div class="small opacity-75">Items Supplied</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-white text-center p-3 mb-0 h-100">
                            <h3 class="mb-0 font-weight-bold text-primary">{{ $supplier->purchaseOrders->count() }}</h3>
                            <div class="small text-muted">Purchase Orders</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm bg-white text-center p-3 mb-0 h-100">
                            <h3 class="mb-0 font-weight-bold text-success">KES {{ number_format($supplier->purchaseOrders->sum('grand_total'), 0) }}</h3>
                            <div class="small text-muted">Total Order Value</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white p-0">
                        <ul class="nav nav-tabs border-bottom-0" id="supplierTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold py-3 px-4 border-bottom-0 border-top-0 border-left-0" id="items-tab" data-toggle="pill" href="#items" role="tab">Supplied Items</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold py-3 px-4 border-bottom-0 border-top-0 border-left-0" id="orders-tab" data-toggle="pill" href="#orders" role="tab">Order History</a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="supplierTabsContent">
                            <!-- Items Tab -->
                            <div class="tab-pane fade show active" id="items" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light small uppercase">
                                            <tr>
                                                <th class="pl-4">Item Name</th>
                                                <th>Category</th>
                                                <th>Stock Level</th>
                                                <th>Unit Cost</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($supplier->inventoryItems as $item)
                                                <tr>
                                                    <td class="pl-4 font-weight-bold">{{ $item->name }}</td>
                                                    <td><span class="small text-muted">{{ $item->category ? $item->category->name : '-' }}</span></td>
                                                    <td>
                                                        <span class="small {{ $item->quantity <= $item->minimum_quantity ? 'text-danger' : 'text-success' }}">
                                                            {{ $item->quantity }} {{ $item->unit }}
                                                        </span>
                                                    </td>
                                                    <td>KES {{ number_format($item->cost_per_unit, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted small italic">This supplier hasn't supplied any items yet.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Orders Tab -->
                            <div class="tab-pane fade" id="orders" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0 align-middle">
                                        <thead class="bg-light small uppercase">
                                            <tr>
                                                <th class="pl-4">PO #</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th class="pr-4 text-right">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($supplier->purchaseOrders as $order)
                                                <tr>
                                                    <td class="pl-4"><a href="#" class="font-weight-bold">{{ $order->po_number }}</a></td>
                                                    <td>{{ $order->order_date->format('d M Y') }}</td>
                                                    <td>
                                                        <span class="badge {{ $order->status == 'Fully Received' ? 'badge-success' : 'badge-warning' }}">
                                                            {{ $order->status }}
                                                        </span>
                                                    </td>
                                                    <td class="pr-4 text-right font-weight-bold">KES {{ number_format($order->grand_total, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-5 text-muted small italic">No purchase orders found for this supplier.</td>
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
        </div>
    </div>

    <style>
        .list-group-item { padding: 0.75rem 0; border-left: 0; border-right: 0; }
        .nav-tabs .nav-link.active { color: #007bff !important; border-top: 2px solid #007bff !important; border-bottom: 0 !important; }
        .opacity-75 { opacity: 0.75; }
    </style>
@endsection