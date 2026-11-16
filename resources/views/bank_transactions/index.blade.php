@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-exchange-alt text-primary mr-2"></i>Bank Ledger</h1>
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

        @php
            $netFlow = (float) $summary->total_in - (float) $summary->total_out;
            $hasFilters = request('account_id') || request('transaction_type');
        @endphp

        {{-- Summary: one card per figure, using the shared .kpi-card styles --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                <div class="kpi-card">
                    <div class="kpi-label">Cash Position (All Accounts)</div>
                    <div class="kpi-value slate">{{ \App\Support\Money::format($totalBalance) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                <div class="kpi-card">
                    <div class="kpi-label">Money In {{ $hasFilters ? '(filtered)' : '' }}</div>
                    <div class="kpi-value emerald">+ {{ \App\Support\Money::format($summary->total_in) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                <div class="kpi-card">
                    <div class="kpi-label">Money Out {{ $hasFilters ? '(filtered)' : '' }}</div>
                    <div class="kpi-value rose">- {{ \App\Support\Money::format($summary->total_out) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="kpi-card">
                    <div class="kpi-label">Net Movement {{ $hasFilters ? '(filtered)' : '' }}</div>
                    <div class="kpi-value {{ $netFlow < 0 ? 'rose' : 'emerald' }}">{{ \App\Support\Money::format($netFlow) }}</div>
                    <p class="kpi-note">{{ \App\Support\Money::whole($summary->tx_count) }} transaction{{ $summary->tx_count == 1 ? '' : 's' }} recorded</p>
                </div>
            </div>
        </div>

        {{-- Filters: inline row, pill selects --}}
        <div class="card border-0 shadow-sm rounded-lg mb-4">
            <div class="card-body py-3">
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
                        <div class="col-md-4 text-right">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm mr-1">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('bank-transactions.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Ledger table --}}
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
                                <th class="border-0 text-center">Reconciliation</th>
                                <th class="border-0 text-right pr-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                                @php
                                    // A transfer writes a withdrawal leg on the
                                    // source and a deposit leg on the target,
                                    // both carrying the pair of account links.
                                    // Legacy rows are typed 'transfer' outright.
                                    $isTransfer = $trx->transaction_type === 'transfer'
                                        || (!empty($trx->source_account_id) && !empty($trx->target_account_id));

                                    $typeClass = [
                                        'deposit' => 'success',
                                        'withdrawal' => 'danger',
                                        'transfer' => 'primary',
                                    ][$isTransfer ? 'transfer' : $trx->transaction_type] ?? 'secondary';

                                    $typeLabel = $isTransfer ? 'transfer' : $trx->transaction_type;
                                @endphp
                                <tr>
                                    <td class="pl-4 py-3 align-middle font-weight-bold text-nowrap">{{ $trx->transaction_date->format('d M, Y') }}</td>
                                    <td class="py-3 align-middle">
                                        <span class="d-block font-weight-bold">
                                            {{ $trx->bankAccount ? $trx->bankAccount->account_name : 'Deleted account' }}
                                        </span>
                                        @if($isTransfer)
                                            <small class="text-muted">
                                                <i class="fas fa-arrow-right mx-1"></i>
                                                {{ $trx->targetAccount ? $trx->targetAccount->account_name : 'Deleted account' }}
                                            </small>
                                        @endif
                                    </td>
                                    <td class="py-3 align-middle">
                                        <span class="badge badge-{{ $typeClass }}-light text-{{ $typeClass }} px-3 py-1 rounded-pill">
                                            {{ ucfirst($typeLabel) }}
                                        </span>
                                    </td>
                                    <td class="py-3 align-middle">
                                        <span class="d-block text-truncate" style="max-width: 320px;">{{ $trx->description ?: '—' }}</span>
                                        <small class="text-muted">{{ $trx->reference_number ?: 'No reference' }}</small>
                                    </td>
                                    <td class="py-3 align-middle text-center">
                                        <span class="badge px-2 py-1 rounded-pill {{ $trx->status === 'reconciled' ? 'badge-success-light text-success' : 'badge-warning-light text-warning' }}">
                                            {{ $trx->status === 'reconciled' ? 'Reconciled' : 'Unreconciled' }}
                                        </span>
                                    </td>
                                    <td class="py-3 align-middle text-right pr-4 font-weight-bold text-nowrap">
                                        @if($trx->transaction_type == 'deposit')
                                            <span class="text-success">+ {{ \App\Support\Money::format($trx->amount) }}</span>
                                        @elseif($trx->transaction_type == 'withdrawal')
                                            <span class="text-danger">- {{ \App\Support\Money::format($trx->amount) }}</span>
                                        @else
                                            <span class="text-primary">{{ \App\Support\Money::format($trx->amount) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-exchange-alt fa-3x mb-3 opacity-20"></i><br>
                                        No bank transactions found.
                                    </td>
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
        .badge-warning-light { background-color: #fef3c7; }
        .badge-success-light { background-color: #dcfce7; }
        .badge-danger-light { background-color: #fee2e2; }
        .badge-primary-light { background-color: #e0f2fe; }
    </style>
@endsection
