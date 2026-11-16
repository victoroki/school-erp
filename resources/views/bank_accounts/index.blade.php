@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-university text-primary mr-2"></i>Bank Accounts</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('bankAccounts.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Add Account
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- KPI Summary -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                <div class="kpi-card">
                    <div class="kpi-label">Total Cash Position</div>
                    <div class="kpi-value slate">{{ \App\Support\Money::format($totals['balance']) }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                <div class="kpi-card">
                    <div class="kpi-label">Active Accounts</div>
                    <div class="kpi-value emerald">{{ $totals['active'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-3 mb-lg-0">
                <div class="kpi-card">
                    <div class="kpi-label">Inactive Accounts</div>
                    <div class="kpi-value {{ $totals['inactive'] > 0 ? 'amber' : 'slate' }}">{{ $totals['inactive'] }}</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12">
                <div class="kpi-card">
                    <div class="kpi-label">Below Minimum Balance</div>
                    <div class="kpi-value {{ $totals['below_minimum'] > 0 ? 'rose' : 'emerald' }}">{{ $totals['below_minimum'] }}</div>
                    <p class="kpi-note">{{ $totals['below_minimum'] > 0 ? 'Action needed on these accounts' : 'All accounts above minimum' }}</p>
                </div>
            </div>
        </div>

        <!-- Account Cards -->
        <div class="row">
            @foreach($bankAccounts as $account)
                @php
                    $typeKey = strtolower(trim($account->account_type ?? ''));
                    $typeMeta = [
                        'current' => ['icon' => 'fa-university', 'bg' => '#eef2ff', 'fg' => '#4338ca'],
                        'savings' => ['icon' => 'fa-piggy-bank', 'bg' => '#ecfdf5', 'fg' => '#059669'],
                        'fixed' => ['icon' => 'fa-coins', 'bg' => '#fffbeb', 'fg' => '#d97706'],
                    ];
                    $type = $typeMeta[$typeKey] ?? ['icon' => 'fa-wallet', 'bg' => '#f1f5f9', 'fg' => '#475569'];

                    $isActive = ($account->status ?? 'active') === 'active';
                    $min = (float) $account->minimum_balance;
                    $balance = (float) $account->current_balance;
                    $belowMin = $min > 0 && $balance < $min;
                    $healthPct = $min > 0 ? min(100, round(($balance / $min) * 100)) : 100;
                    $healthColor = $belowMin ? '#e11d48' : ($healthPct < 150 ? '#d97706' : '#059669');
                @endphp
                <div class="col-lg-4 col-md-6 col-12 mb-4">
                    <div class="account-card">
                        <div class="account-card-top">
                            <div class="account-type-icon" style="background: {{ $type['bg'] }}; color: {{ $type['fg'] }};">
                                <i class="fas {{ $type['icon'] }}"></i>
                            </div>
                            <span class="badge px-3 py-2 rounded-pill font-weight-bold {{ $isActive ? 'badge-success-light text-success' : 'badge-slate-light text-slate' }}">
                                {{ $isActive ? 'Active' : 'Inactive' }}
                            </span>
                            <div class="dropdown ml-auto">
                                <button class="btn ml-2 account-more" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow border-0">
                                    <a class="dropdown-item" href="{{ route('bankAccounts.show', [$account->account_id]) }}"><i class="fas fa-eye mr-2 text-primary"></i> Details</a>
                                    <a class="dropdown-item" href="{{ route('bankAccounts.edit', [$account->account_id]) }}"><i class="fas fa-edit mr-2 text-primary"></i> Edit</a>
                                    <div class="dropdown-divider"></div>
                                    {!! Form::open(['route' => ['bankAccounts.destroy', $account->account_id], 'method' => 'delete']) !!}
                                    {!! Form::button('<i class="fas fa-trash mr-2 text-danger"></i> Delete', ['type' => 'submit', 'class' => 'dropdown-item text-danger', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                    {!! Form::close() !!}
                                </div>
                            </div>
                        </div>

                        <h5 class="account-name">{{ $account->account_name }}</h5>
                        <p class="account-meta">{{ $account->bank_name }} &middot; {{ $account->account_number }}</p>

                        <div class="account-balance-block">
                            <div class="kpi-label">Available Balance</div>
                            <div class="account-balance">{{ \App\Support\Money::format($balance) }}</div>
                        </div>

                        @if($min > 0)
                            <div class="account-health">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="account-health-label">Minimum balance</span>
                                    <span class="account-health-label">{{ \App\Support\Money::format($min) }}</span>
                                </div>
                                <div class="util-track">
                                    <div class="util-fill" style="width: {{ $healthPct }}%; background: {{ $healthColor }};"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-1">
                                    <span class="account-health-note {{ $belowMin ? 'rose' : 'emerald' }}">
                                        <i class="fas {{ $belowMin ? 'fa-exclamation-circle' : 'fa-check-circle' }} mr-1"></i>
                                        {{ $belowMin ? 'Below minimum' : ($healthPct < 150 ? 'Nearing minimum' : 'Above minimum') }}
                                    </span>
                                    <span class="account-health-note">{{ $healthPct }}% funded</span>
                                </div>
                            </div>
                        @endif

                        <div class="account-card-foot">
                            <a href="{{ route('bank-transactions.index', ['account_id' => $account->account_id]) }}" class="account-link">
                                VIEW TRANSACTIONS <i class="fas fa-chevron-right ml-1 small"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($bankAccounts->isEmpty())
            <div class="card border-0 shadow-sm rounded-lg text-center py-5">
                <i class="fas fa-university fa-4x text-muted opacity-20 mb-3"></i>
                <h4 class="text-muted font-weight-bold">No bank accounts found</h4>
                <p class="text-muted mb-4">Add a bank account to track balances and transactions.</p>
                <a href="{{ route('bankAccounts.create') }}" class="btn btn-primary rounded-pill px-4 d-inline-block">Add your first account</a>
            </div>
        @endif

        @if($bankAccounts->hasPages())
            <div class="card border-0 shadow-sm rounded-lg my-0 py-3">
                <div class="d-flex justify-content-between align-items-center px-3">
                    <p class="mb-0 text-muted small">Showing {{ $bankAccounts->firstItem() }} to {{ $bankAccounts->lastItem() }} of {{ $bankAccounts->total() }} accounts</p>
                    {{ $bankAccounts->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

    <style>
        .kpi-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            padding: 1.25rem 1.5rem; height: 100%;
        }
        .kpi-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; }
        .kpi-value {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.4rem; font-weight: 900; color: #0f172a; margin-top: 4px; line-height: 1.15;
        }
        .kpi-note { font-size: 0.8rem; color: #64748b; font-weight: 500; margin: 4px 0 0; }

        .emerald { color: #059669 !important; }
        .amber { color: #d97706 !important; }
        .rose { color: #e11d48 !important; }
        .slate { color: #0f172a !important; }

        .account-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
            padding: 1.5rem; height: 100%; display: flex; flex-direction: column;
            transition: box-shadow 200ms, transform 200ms cubic-bezier(0.23, 1, 0.32, 1);
        }
        .account-card:hover { box-shadow: 0 8px 24px rgba(15, 23, 42, 0.12); transform: translateY(-3px); }
        .account-card-top { display: flex; align-items: center; gap: 10px; margin-bottom: 1rem; }
        .account-type-icon {
            width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.05rem;
        }
        .account-more {
            width: 30px; height: 30px; border-radius: 999px; color: #94a3b8;
            display: inline-flex; align-items: center; justify-content: center; padding: 0;
        }
        .account-more:hover { background: #f1f5f9; color: #334155; }
        .account-name { font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; }
        .account-meta { color: #64748b; font-size: 0.8rem; font-weight: 500; margin: 2px 0 0; }
        .account-balance-block { margin-top: 1.25rem; }
        .account-balance {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.45rem; font-weight: 900; color: #4338ca; margin-top: 4px;
        }
        .account-health { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f8fafc; }
        .account-health-label { font-size: 0.72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.03em; }
        .account-health-note { font-size: 0.78rem; font-weight: 600; }
        .account-card-foot { margin-top: auto; padding-top: 1.1rem; }
        .account-link {
            font-size: 0.78rem; font-weight: 800; letter-spacing: 0.04em;
            color: #4338ca; text-decoration: none; text-transform: uppercase;
        }
        .account-link:hover { text-decoration: none; color: #3730a3; }

        .util-track { position: relative; height: 8px; background: #f1f5f9; border-radius: 999px; overflow: hidden; }
        .util-fill { position: absolute; left: 0; top: 0; height: 100%; border-radius: 999px; }

        .badge-success-light { background-color: #d1fae5; }
        .badge-slate-light { background-color: #f1f5f9; }
        .bg-light { background-color: #f8fafc !important; }
        .rounded-pill { border-radius: 50rem !important; }
        .opacity-20 { opacity: 0.2; }
        .bg-white { background: #fff; }
    </style>
@endsection