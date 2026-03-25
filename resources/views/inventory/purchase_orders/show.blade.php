@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-invoice-dollar text-primary mr-2"></i>PO: {{ $purchaseOrder->po_number }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-default shadow-sm border mr-2">
                        <i class="fas fa-chevron-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Order Summary -->
            <div class="col-md-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="font-weight-bold mb-0">Order Details</h6>
                            <span>{!! $purchaseOrder->status_badge !!}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small uppercase text-muted">
                                    <tr>
                                        <th class="pl-4">Item Name</th>
                                        <th class="text-center">Qty Ordered</th>
                                        <th class="text-center">Unit Price</th>
                                        <th class="text-right pr-4">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchaseOrder->items as $item)
                                        <tr>
                                            <td class="pl-4">
                                                <div class="font-weight-bold text-dark">{{ $item->item->name }}</div>
                                                <div class="small text-muted">{{ $item->item->item_code }}</div>
                                            </td>
                                            <td class="text-center">{{ $item->quantity }} {{ $item->item->unit }}</td>
                                            <td class="text-center">KES {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-right pr-4 font-weight-bold text-dark">KES {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Footer Stats -->
                <div class="row justify-content-end">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <table class="table mb-0">
                                    <tr>
                                        <td class="border-top-0 text-muted">Subtotal:</td>
                                        <td class="border-top-0 text-right font-weight-bold">KES {{ number_format($purchaseOrder->sub_total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Tax (16% VAT):</td>
                                        <td class="text-right font-weight-bold text-info">KES {{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <td class="font-weight-bold h6">Grand Total:</td>
                                        <td class="text-right font-weight-bold h5 text-primary">KES {{ number_format($purchaseOrder->grand_total, 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier & Receiving -->
            <div class="col-md-3">
                <!-- Supplier Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="font-weight-bold mb-0"><i class="fas fa-truck mr-2 text-muted"></i>Supplier Information</h6>
                    </div>
                    <div class="card-body py-3">
                        <h6 class="font-weight-bold text-dark mb-1">{{ $purchaseOrder->supplier->name }}</h6>
                        <div class="small text-muted mb-2">{{ $purchaseOrder->supplier->code }}</div>
                        <div class="small text-muted mb-1"><i class="fas fa-phone mr-1"></i> {{ $purchaseOrder->supplier->phone }}</div>
                        <div class="small text-muted mb-1"><i class="fas fa-envelope mr-1"></i> {{ $purchaseOrder->supplier->email }}</div>
                    </div>
                </div>

                <!-- Dates Info -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body py-1">
                        <ul class="list-group list-group-unbordered mb-0">
                            <li class="list-group-item d-flex justify-content-between border-top-0 px-0">
                                <b class="text-muted small">Order Date</b>
                                <span class="small">{{ $purchaseOrder->order_date->format('d M Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between border-bottom-0 px-0">
                                <b class="text-muted small">Delivery Expected</b>
                                <span class="small font-weight-bold text-info">{{ $purchaseOrder->expected_delivery_date->format('d M Y') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Receive Action -->
                @if($purchaseOrder->status == 'Approved' || $purchaseOrder->status == 'Sent' || $purchaseOrder->status == 'Pending_Approval')
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-header bg-light border-bottom-0 pt-3">
                            <h6 class="font-weight-bold mb-0">Quick Action</h6>
                        </div>
                        <div class="card-body">
                            @if($purchaseOrder->status == 'Pending_Approval')
                                <p class="small text-muted italic">This order is awaiting approval from management.</p>
                                <button class="btn btn-outline-primary btn-block shadow-sm btn-sm" disabled>Awaiting Approval</button>
                            @else
                                <p class="small text-muted italic">Once the shipment arrives, use the button below to update inventory.</p>
                                <form action="{{ route('inventory.purchase-orders.receive', $purchaseOrder->po_id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-block shadow-sm" onclick="return confirm('Confirm all items have been received at the warehouse?')">
                                        <i class="fas fa-check-double mr-2"></i> Receive Stock
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @elseif($purchaseOrder->status == 'Fully_Received')
                    <div class="card border-0 shadow-sm border-left border-success bg-white">
                        <div class="card-body py-3">
                            <div class="d-flex align-items-center mb-2">
                                <i class="fas fa-check-circle text-success fa-lg mr-2"></i>
                                <span class="font-weight-bold">Stock Received</span>
                            </div>
                            <div class="small text-muted mb-1">Processed by:</div>
                            <div class="font-weight-bold mb-1">{{ $purchaseOrder->receivedBy->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $purchaseOrder->received_date ? $purchaseOrder->received_date->format('d M Y H:i') : '' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .list-group-item { padding: 0.75rem 0; border-left: 0; border-right: 0; }
    </style>
@endsection
