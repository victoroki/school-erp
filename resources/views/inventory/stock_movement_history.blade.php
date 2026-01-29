@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Stock Movement History</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('inventory.dashboard') }}">Inventory</a></li>
                        <li class="breadcrumb-item active">Movement History</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">All Stock Movements</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('inventory.dashboard') }}" class="btn btn-default">Back to Dashboard</a>
                    </div>
                </div>
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
                                <th>From/To</th>
                                <th>Balance</th>
                                <th>Done By</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                                    <td>{{ $transaction->item->name ?? 'N/A' }}</td>
                                    <td>{!! $transaction->typeBadge !!}</td>
                                    <td>{{ $transaction->quantity }}</td>
                                    <td>
                                        @if(in_array($transaction->transaction_type, ['purchase', 'return', 'adjustment']))
                                            <span class="text-success">+{{ $transaction->quantity }}</span>
                                        @else
                                            <span class="text-danger">-{{ $transaction->quantity }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $transaction->item->quantity ?? 0 }}</td>
                                    <td>{{ $transaction->handledBy->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($transaction->remarks, 50) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No stock movement records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection