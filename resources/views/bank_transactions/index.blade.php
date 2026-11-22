@extends('layouts.app')

@include('partials.minimal-ui')

@section('content')
@php
    $money = fn ($v) => \App\Support\Money::format($v);
    $netFlow = (float) $summary->total_in - (float) $summary->total_out;
    $hasFilters = request('account_id') || request('transaction_type');
    $scope = $hasFilters ? 'Filtered' : 'All time';
@endphp

<div class="ba">

    <header class="ba-head">
        <div>
            <h1 class="ba-title">Bank ledger</h1>
            <p class="ba-sub">Every deposit, withdrawal and transfer across the school's accounts.</p>
        </div>
        <a href="{{ route('bank-transactions.create') }}" class="ba-btn">New transaction</a>
    </header>

    @include('flash::message')

    <dl class="ba-summary">
        <div class="ba-stat ba-stat-main">
            <dt>Cash position, active accounts</dt>
            <dd class="ba-num">{{ $money($totalBalance) }}</dd>
        </div>
        <div class="ba-stat">
            <dt>Money in <span class="sr-only">({{ $scope }})</span></dt>
            <dd class="ba-num is-green">{{ $money($summary->total_in) }}</dd>
        </div>
        <div class="ba-stat">
            <dt>Money out <span class="sr-only">({{ $scope }})</span></dt>
            <dd class="ba-num">{{ $money($summary->total_out) }}</dd>
        </div>
        <div class="ba-stat">
            <dt>Net movement</dt>
            <dd class="ba-num {{ $netFlow < 0 ? 'is-red' : '' }}">{{ $money($netFlow) }}</dd>
            <p class="ba-stat-note">
                {{ \App\Support\Money::whole($summary->tx_count) }} transaction{{ $summary->tx_count == 1 ? '' : 's' }}{{ $hasFilters ? ', filtered' : '' }}
            </p>
        </div>
    </dl>

    <form action="{{ route('bank-transactions.index') }}" method="GET" class="ba-filters">
        <div class="ba-field">
            <label for="f-account">Account</label>
            {!! Form::select('account_id', ['' => 'All accounts'] + $bankAccounts->toArray(), request('account_id'), ['class' => 'ba-select', 'id' => 'f-account']) !!}
        </div>
        <div class="ba-field">
            <label for="f-type">Type</label>
            {!! Form::select('transaction_type', ['' => 'All types', 'deposit' => 'Deposit', 'withdrawal' => 'Withdrawal', 'transfer' => 'Transfer'], request('transaction_type'), ['class' => 'ba-select', 'id' => 'f-type']) !!}
        </div>
        <button type="submit" class="ba-btn-ghost">Apply</button>
        @if($hasFilters)
            <a href="{{ route('bank-transactions.index') }}" class="ba-link">Clear filters</a>
        @endif
    </form>

    <div class="ba-list" role="table" aria-label="Bank transactions">
        <div class="ba-row ba-row-ledger ba-row-head" role="row">
            <span role="columnheader">Date</span>
            <span role="columnheader">Account</span>
            <span role="columnheader">Type</span>
            <span role="columnheader">Description</span>
            <span role="columnheader">Reconciliation</span>
            <span role="columnheader" class="ba-right">Amount</span>
        </div>

        @forelse($transactions as $trx)
            @php
                // A transfer writes a withdrawal leg on the source and a deposit
                // leg on the target, both carrying the pair of account links.
                // Legacy rows are typed 'transfer' outright.
                $isTransfer = $trx->transaction_type === 'transfer'
                    || (!empty($trx->source_account_id) && !empty($trx->target_account_id));
                $typeLabel = $isTransfer ? 'Transfer' : ucfirst($trx->transaction_type);
                $reconciled = $trx->status === 'reconciled';
            @endphp
            <div class="ba-row ba-row-ledger" role="row">
                <span class="ba-date" role="cell">{{ $trx->transaction_date->format('d M Y') }}</span>

                <div class="ba-cell" role="cell">
                    <span class="ba-strong">{{ $trx->bankAccount ? $trx->bankAccount->account_name : 'Deleted account' }}</span>
                    @if($isTransfer)
                        <span class="ba-small">to {{ $trx->targetAccount ? $trx->targetAccount->account_name : 'deleted account' }}</span>
                    @endif
                </div>

                <div role="cell"><span class="ba-tag">{{ $typeLabel }}</span></div>

                <div class="ba-cell" role="cell">
                    <span class="ba-small" style="color: var(--ba-ink-2)">{{ $trx->description ?: 'No description' }}</span>
                    <span class="ba-small">
                        @if($trx->reference_number)
                            <span class="ba-mono">{{ $trx->reference_number }}</span>
                        @else
                            No reference
                        @endif
                    </span>
                </div>

                <div role="cell">
                    <span class="ba-tag {{ $reconciled ? 'ba-tag-green' : 'ba-tag-yellow' }}">{{ $reconciled ? 'Reconciled' : 'Unreconciled' }}</span>
                </div>

                <div class="ba-right" role="cell">
                    @if($trx->transaction_type == 'deposit')
                        <span class="ba-amount is-in">+{{ $money($trx->amount) }}</span>
                    @elseif($trx->transaction_type == 'withdrawal')
                        <span class="ba-amount">&minus;{{ $money($trx->amount) }}</span>
                    @else
                        <span class="ba-amount is-transfer">{{ $money($trx->amount) }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="ba-empty-row">
                {{ $hasFilters ? 'No transactions match these filters.' : 'No bank transactions recorded yet.' }}
            </div>
        @endforelse
    </div>

    @if($transactions->hasPages())
        <div class="ba-pager">
            <p>Showing {{ $transactions->firstItem() }}&ndash;{{ $transactions->lastItem() }} of {{ $transactions->total() }}</p>
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
