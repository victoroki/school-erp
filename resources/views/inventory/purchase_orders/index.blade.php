@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-shopping-cart text-warning mr-2"></i>Purchase Orders</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary" href="{{ route('inventory.purchase-orders.create') }}">
                        <i class="fas fa-plus mr-1"></i> New Purchase Order
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light small uppercase">
                            <tr>
                                <th class="pl-4">PO #</th>
                                <th>Supplier</th>
                                <th>Order Date</th>
                                <th>Expected Delivery</th>
                                <th>Status</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseOrders as $order)
                                <tr>
                                    <td class="pl-4 font-weight-bold">{{ $order->po_number }}</td>
                                    <td>{{ $order->supplier->name }}</td>
                                    <td>{{ $order->order_date->format('d M Y') }}</td>
                                    <td>{{ $order->expected_delivery_date->format('d M Y') }}</td>
                                    <td>{!! $order->status_badge !!}</td>
                                    <td class="text-right font-weight-bold">KES {{ number_format($order->grand_total, 2) }}</td>
                                    <td class="text-right pr-4">
                                        <div class="btn-group">
                                            <a href="{{ route('inventory.purchase-orders.show', $order->po_id) }}" class="btn btn-default btn-sm shadow-sm">
                                                <i class="fas fa-eye text-primary"></i>
                                            </a>
                                            @if($order->status == 'Approved' || $order->status == 'Sent')
                                                <form action="{{ route('inventory.purchase-orders.receive', $order->po_id) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-default btn-sm shadow-sm text-success" title="Mark as Received" onclick="return confirm('Mark this PO as fully received?')">
                                                        <i class="fas fa-check-double"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No purchase orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-top-0">
                <div class="d-flex justify-content-center">
                    {{ $purchaseOrders->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
