@extends('layouts.app')

@include('partials.minimal-ui')

@section('content')
@php
    $money = fn ($v) => \App\Support\Money::format($v);
@endphp

<div class="ba">

    <header class="ba-head">
        <div>
            <h1 class="ba-title">Bank accounts</h1>
            <p class="ba-sub">Balances across every account the school holds.</p>
        </div>
        <a href="{{ route('bankAccounts.create') }}" class="ba-btn">Add account</a>
    </header>

    @include('flash::message')

    {{-- One flat summary strip, cells divided by hairlines. --}}
    <dl class="ba-summary">
        <div class="ba-stat ba-stat-main">
            <dt>Total cash position</dt>
            <dd class="ba-num">{{ $money($totals['balance']) }}</dd>
        </div>
        <div class="ba-stat">
            <dt>Active</dt>
            <dd class="ba-num">{{ $totals['active'] }}</dd>
        </div>
        <div class="ba-stat">
            <dt>Inactive</dt>
            <dd class="ba-num">{{ $totals['inactive'] }}</dd>
        </div>
        <div class="ba-stat">
            <dt>Below minimum</dt>
            <dd class="ba-num {{ $totals['below_minimum'] > 0 ? 'is-red' : '' }}">{{ $totals['below_minimum'] }}</dd>
        </div>
    </dl>

    @if($bankAccounts->isEmpty())
        <div class="ba-empty">
            <p class="ba-empty-title">No bank accounts yet</p>
            <p class="ba-empty-text">Add the school's accounts to track balances and match bank statements.</p>
            <a href="{{ route('bankAccounts.create') }}" class="ba-btn">Add account</a>
        </div>
    @else
        <div class="ba-list" role="table" aria-label="Bank accounts">
            <div class="ba-row ba-row-head" role="row">
                <span role="columnheader">Account</span>
                <span role="columnheader">Type</span>
                <span role="columnheader">Minimum balance</span>
                <span role="columnheader" class="ba-right">Balance</span>
                <span role="columnheader"><span class="sr-only">Actions</span></span>
            </div>

            @foreach($bankAccounts as $account)
                @php
                    $isActive = ($account->status ?? 'active') === 'active';
                    $min = (float) $account->minimum_balance;
                    $balance = (float) $account->current_balance;
                    $belowMin = $min > 0 && $balance < $min;
                    $ratio = $min > 0 ? $balance / $min : null;
                    $state = $belowMin ? 'red' : (($ratio !== null && $ratio < 1.5) ? 'yellow' : 'green');
                    $fill = $min > 0 ? max(2, min(100, round($ratio * 100 / 3))) : 0; // 3x minimum reads as full
                @endphp
                <div class="ba-row {{ $isActive ? '' : 'is-inactive' }}" role="row">
                    <div class="ba-account" role="cell">
                        <a href="{{ route('bankAccounts.show', [$account->account_id]) }}" class="ba-name">{{ $account->account_name }}</a>
                        <span class="ba-meta">{{ $account->bank_name }} <span class="ba-mono">{{ $account->account_number }}</span></span>
                    </div>

                    <div role="cell" class="ba-tags">
                        @if($account->account_type)
                            <span class="ba-tag">{{ $account->account_type }}</span>
                        @endif
                        @unless($isActive)
                            <span class="ba-tag ba-tag-muted">Inactive</span>
                        @endunless
                    </div>

                    <div role="cell" class="ba-min">
                        @if($min > 0)
                            <div class="ba-min-line">
                                <span class="ba-mono">{{ $money($min) }}</span>
                                <span class="ba-state is-{{ $state }}">
                                    {{ $belowMin ? 'Below' : ($state === 'yellow' ? 'Close' : 'Clear') }}
                                </span>
                            </div>
                            <div class="ba-track" aria-hidden="true"><span class="ba-fill is-{{ $state }}" style="width: {{ $fill }}%"></span></div>
                        @else
                            <span class="ba-none">None set</span>
                        @endif
                    </div>

                    <div role="cell" class="ba-right">
                        <span class="ba-balance">{{ $money($balance) }}</span>
                    </div>

                    <div role="cell" class="ba-actions">
                        <a href="{{ route('bank-transactions.index', ['account_id' => $account->account_id]) }}" class="ba-link">Transactions</a>
                        <div class="dropdown">
                            <button class="ba-more" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="More actions for {{ $account->account_name }}">
                                <svg viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><circle cx="3" cy="8" r="1.5"/><circle cx="8" cy="8" r="1.5"/><circle cx="13" cy="8" r="1.5"/></svg>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right ba-menu">
                                <a class="dropdown-item" href="{{ route('bankAccounts.show', [$account->account_id]) }}">Details</a>
                                <a class="dropdown-item" href="{{ route('bankAccounts.edit', [$account->account_id]) }}">Edit</a>
                                <div class="dropdown-divider"></div>
                                {!! Form::open(['route' => ['bankAccounts.destroy', $account->account_id], 'method' => 'delete']) !!}
                                {!! Form::button('Delete', ['type' => 'submit', 'class' => 'dropdown-item ba-danger', 'onclick' => "return confirm('Delete this bank account? This cannot be undone.')"]) !!}
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($bankAccounts->hasPages())
            <div class="ba-pager">
                <p>Showing {{ $bankAccounts->firstItem() }}&ndash;{{ $bankAccounts->lastItem() }} of {{ $bankAccounts->total() }}</p>
                {{ $bankAccounts->appends(request()->query())->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
