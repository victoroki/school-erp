@extends('layouts.app')

@section('content')
    @php
        $rawType = strtolower(trim($bankAccount->account_type ?? ''));
        $typeMeta = [
            'current' => ['label' => 'Current Account', 'icon' => 'fa-university', 'bg' => '#eef2ff', 'fg' => '#4338ca'],
            'savings' => ['label' => 'Savings Account', 'icon' => 'fa-piggy-bank', 'bg' => '#ecfdf5', 'fg' => '#059669'],
            'fixed' => ['label' => 'Fixed Deposit', 'icon' => 'fa-coins', 'bg' => '#fffbeb', 'fg' => '#d97706'],
        ];
        $type = $typeMeta[$rawType] ?? ['label' => 'Bank Account', 'icon' => 'fa-wallet', 'bg' => '#f1f5f9', 'fg' => '#475569'];

        $isActive = ($bankAccount->status ?? 'active') === 'active';
        $min = (float) $bankAccount->minimum_balance;
        $balance = (float) $bankAccount->current_balance;
        $belowMin = $min > 0 && $balance < $min;
        $healthPct = $min > 0 ? min(100, round(($balance / $min) * 100)) : 100;
        $healthColor = $belowMin ? '#e11d48' : ($healthPct < 150 ? '#d97706' : '#059669');
    @endphp

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <div class="detail-heading">
                        <div class="detail-heading-icon" style="background: {{ $type['bg'] }}; color: {{ $type['fg'] }};">
                            <i class="fas {{ $type['icon'] }}"></i>
                        </div>
                        <div>
                            <h1 class="detail-heading-title">Bank Account Details</h1>
                            <p class="detail-heading-sub">{{ $bankAccount->bank_name }} &middot; Account {{ $bankAccount->account_number }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 detail-heading-actions">
                    <a href="{{ route('bankAccounts.edit', [$bankAccount->account_id]) }}" class="btn-detail btn-detail--primary">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('bankAccounts.index') }}" class="btn-detail btn-detail--ghost">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Summary Column -->
            <div class="col-lg-4">
                <div class="detail-card detail-card--summary mb-3">
                    <div class="detail-label">Available Balance</div>
                    <div class="detail-amount">{{ \App\Support\Money::format($balance) }}</div>
                    <div class="detail-summary-foot">
                        <span class="detail-badge" style="background: {{ $isActive ? '#ecfdf5' : '#f1f5f9' }}; color: {{ $isActive ? '#059669' : '#64748b' }};">
                            {{ $isActive ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="detail-date">{{ ucwords($type['label']) }}</span>
                    </div>
                </div>

                @if($min > 0)
                    <div class="detail-card mb-3">
                        <div class="detail-card-head">
                            <i class="fas fa-shield-alt mr-2"></i> Minimum Balance Health
                        </div>
                        <div class="detail-card-body">
                            <div class="detail-row">
                                <span class="detail-row-label">Minimum Balance</span>
                                <span class="detail-row-value">{{ \App\Support\Money::format($min) }}</span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-row-label">Health</span>
                                <span class="detail-row-value" style="color: {{ $healthColor }};">{{ round($healthPct) }}% funded</span>
                            </div>
                            <div class="detail-card-body--pad">
                                <div class="util-track">
                                    <div class="util-fill" style="width: {{ $healthPct }}%; background: {{ $healthColor }};"></div>
                                </div>
                                <p class="detail-note mt-2 {{ $belowMin ? 'rose' : ($healthPct < 150 ? 'amber' : 'emerald') }}">
                                    <i class="fas {{ $belowMin ? 'fa-exclamation-circle' : ($healthPct < 150 ? 'fa-exclamation-triangle' : 'fa-check-circle') }} mr-1"></i>
                                    {{ $belowMin ? 'Below minimum — action needed' : ($healthPct < 150 ? 'Nearing minimum balance' : 'Above minimum balance') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Details Column -->
            <div class="col-lg-8">
                @include('bank_accounts.show_fields')
            </div>
        </div>
    </div>

    <style>
        .detail-heading { display: flex; align-items: center; gap: 14px; }
        .detail-heading-icon {
            width: 44px; height: 44px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.15rem;
        }
        .detail-heading-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .detail-heading-sub { color: #64748b; font-size: 0.85rem; font-weight: 500; margin: 2px 0 0; }
        .detail-heading-actions { text-align: right; }
        @media (max-width: 767px) { .detail-heading-actions { text-align: left; margin-top: 0.75rem; } }

        .btn-detail {
            display: inline-block; padding: 8px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 600; line-height: 1.4; text-decoration: none;
            transition: transform 160ms cubic-bezier(0.23, 1, 0.32, 1);
        }
        .btn-detail:active { transform: scale(0.97); }
        .btn-detail--primary { background: #4338ca; border: 1px solid #4338ca; color: #fff; }
        .btn-detail--primary:hover { background: #3730a3; border-color: #3730a3; color: #fff; }
        .btn-detail--ghost { background: #fff; border: 1px solid #e2e8f0; color: #334155; }
        .btn-detail--ghost:hover { background: #f8fafc; color: #0f172a; }

        .detail-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }
        .detail-card--summary { padding: 1.5rem; }
        .detail-card-head {
            display: flex; align-items: center;
            font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
            color: #334155; padding: 0.9rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        }
        .detail-card-body { padding: 1.25rem; }
        .detail-card-body--pad { padding: 0 1.25rem 1.25rem; }
        .detail-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 700; }
        .detail-amount {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.75rem; font-weight: 900; color: #4338ca; margin-top: 4px;
        }
        .detail-summary-foot {
            display: flex; align-items: center; justify-content: space-between;
            margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #f1f5f9;
        }
        .detail-badge { font-size: 0.72rem; font-weight: 700; padding: 5px 12px; border-radius: 999px; }
        .detail-date { color: #64748b; font-size: 0.8rem; font-weight: 500; }

        .detail-row {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 0.8rem 1.25rem; border-bottom: 1px solid #f8fafc;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; flex-shrink: 0; }
        .detail-row-value { font-size: 0.88rem; font-weight: 600; color: #1e293b; text-align: right; }

        .util-track {
            position: relative; height: 8px; background: #f1f5f9;
            border-radius: 999px; overflow: hidden; margin-top: 6px;
        }
        .util-fill { position: absolute; left: 0; top: 0; height: 100%; border-radius: 999px; }
        .detail-note { font-size: 0.78rem; font-weight: 700; margin-bottom: 0; }

        .emerald { color: #059669 !important; }
        .amber { color: #d97706 !important; }
        .rose { color: #e11d48 !important; }
    </style>
@endsection
