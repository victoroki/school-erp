@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-exchange-alt text-primary mr-2"></i>Bank Transactions</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('bank-transactions.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> New Transaction
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card border-0 shadow-sm rounded-lg mb-4">
            <div class="card-body">
                <form action="{{ route('bank-transactions.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small text-uppercase font-weight-bold text-muted">Account</label>
                            {!! Form::select('account_id', ['' => 'All Accounts'] + $bankAccounts->toArray(), request('account_id'), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-4">
                            <label class="small text-uppercase font-weight-bold text-muted">Type</label>
                            {!! Form::select('transaction_type', ['' => 'All Types', 'deposit' => 'Deposit', 'withdrawal' => 'Withdrawal', 'transfer' => 'Transfer'], request('transaction_type'), ['class' => 'form-control border-0 bg-light rounded-pill']) !!}
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Filter</button>
                            <a href="{{ route('bank-transactions.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr class="bg-light text-muted small text-uppercase">
                                <th class="pl-4 border-0">Date</th>
                                <th class="border-0">Account</th>
                                <th class="border-0">Type</th>
                                <th class="border-0">Description / Ref</th>
                                <th class="border-0 text-right pr-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $trx->transaction_date->format('d M, Y') }}</td>
                                    <td class="py-3 align-middle">
                                        {{ $trx->bankAccount ? $trx->bankAccount->account_name : 'Deleted' }}
                                    </td>
                                    <td class="py-3 align-middle">
                                        @php
                                            $typeClass = [
                                                'deposit' => 'success',
                                                'withdrawal' => 'danger',
                                                'transfer' => 'primary'
                                            ][$trx->transaction_type] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $typeClass }}-light text-{{ $typeClass }} px-3 py-1 rounded-pill">
                                            {{ ucfirst($trx->transaction_type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 align-middle">
                                        <span class="d-block">{{ $trx->description }}</span>
                                        <small class="text-muted">{{ $trx->reference_number ?: 'No Ref' }}</small>
                                    </td>
                                    <td class="py-3 align-middle text-right pr-4 font-weight-bold">
                                        @if($trx->transaction_type == 'deposit')
                                            <span class="text-success">+ KES {{ number_format($trx->amount, 2) }}</span>
                                        @elseif($trx->transaction_type == 'withdrawal')
                                            <span class="text-danger">- KES {{ number_format($trx->amount, 2) }}</span>
                                        @else
                                            <span class="text-primary">KES {{ number_format($trx->amount, 2) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No transactions found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <style>
        .badge-success-light { background-color: #dcfce7; }
        .badge-danger-light { background-color: #fee2e2; }
        .badge-primary-light { background-color: #e0f2fe; }
    </style>
@endsection
