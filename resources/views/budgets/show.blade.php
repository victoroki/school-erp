@extends('layouts.app')

@section('content')
    @php
        $typeBadge = $budget->category_type === 'income'
            ? ['label' => 'Income', 'bg' => '#ecfdf5', 'fg' => '#059669']
            : ['label' => 'Expense', 'bg' => '#fff1f2', 'fg' => '#e11d48'];
        $threshold = (int) round((float) $budget->alert_threshold);
    @endphp

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <div class="detail-heading">
                        <div class="detail-heading-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div>
                            <h1 class="detail-heading-title">Budget Details</h1>
                            <p class="detail-heading-sub">{{ $budget->financialYear->name }} &middot; {{ ucfirst($budget->category_type) }} Budget</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 detail-heading-actions">
                    <a href="{{ route('budgets.edit', $budget->id) }}" class="btn-detail btn-detail--primary">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('budgets.index') }}" class="btn-detail btn-detail--ghost">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-lg-4">
                <div class="detail-card detail-card--summary mb-3">
                    <div class="detail-label">Budget Amount</div>
                    <div class="detail-amount">{{ \App\Support\Money::format($budget->amount ?? 0) }}</div>
                    <div class="detail-summary-foot">
                        <span class="detail-badge" style="background: {{ $typeBadge['bg'] }}; color: {{ $typeBadge['fg'] }}">{{ $typeBadge['label'] }}</span>
                        <span class="detail-date">{{ $budget->financialYear->name }}</span>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-head">
                        <i class="fas fa-bell mr-2"></i> Alert Threshold
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-threshold-value">{{ $threshold }}%</div>
                        <div class="detail-threshold-track">
                            <div class="detail-threshold-fill" style="width: {{ min($threshold, 100) }}%"></div>
                        </div>
                        <p class="detail-description">Alerts when spending reaches this share of the budget.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @include('budgets.show_fields')
            </div>
        </div>
    </div>

    <style>
        .detail-heading { display: flex; align-items: center; gap: 14px; }
        .detail-heading-icon {
            width: 44px; height: 44px; border-radius: 10px;
            background: #eef2ff; color: #4338ca;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0;
        }
        .detail-heading-title { font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
        .detail-heading-sub { color: #64748b; font-size: 0.85rem; font-weight: 500; margin: 2px 0 0; }
        .detail-heading-actions { text-align: right; }
        @media (max-width: 767px) { .detail-heading-actions { text-align: left; margin-top: 0.75rem; } }

        .btn-detail {
            display: inline-block; padding: 8px 18px; border-radius: 8px;
            font-size: 0.85rem; font-weight: 600; line-height: 1.4; text-decoration: none;
            transition: transform 160ms cubic-bezier(0.23, 1, 0.32, 1), box-shadow 200ms;
        }
        .btn-detail:active { transform: scale(0.97); }
        .btn-detail--primary { background: #4338ca; border: 1px solid #4338ca; color: #fff; }
        .btn-detail--primary:hover { background: #3730a3; border-color: #3730a3; color: #fff; }
        .btn-detail--ghost { background: #fff; border: 1px solid #e2e8f0; color: #334155; }
        .btn-detail--ghost:hover { background: #f8fafc; color: #0f172a; }
        @media (max-width: 575px) { .btn-detail { width: 100%; text-align: center; margin-bottom: 0.4rem; } }

        .detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06); }
        .detail-card--summary { padding: 1.5rem; }
        .detail-card-head {
            display: flex; align-items: center;
            font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;
            color: #334155; padding: 0.9rem 1.25rem; border-bottom: 1px solid #f1f5f9;
        }
        .detail-card-body { padding: 1.25rem; }
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

        .detail-threshold-value {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 1.5rem; font-weight: 900; color: #0f172a;
        }
        .detail-threshold-track { height: 8px; background: #f1f5f9; border-radius: 999px; overflow: hidden; margin: 0.75rem 0; }
        .detail-threshold-fill { height: 100%; background: #4338ca; border-radius: 999px; transition: width 320ms cubic-bezier(0.23, 1, 0.32, 1); }

        .detail-row {
            display: flex; align-items: center; justify-content: space-between; gap: 1rem;
            padding: 0.8rem 1.25rem; border-bottom: 1px solid #f8fafc;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0; }
        .detail-row-value { font-size: 0.88rem; font-weight: 600; color: #1e293b; text-align: right; }

        .detail-description { color: #64748b; font-size: 0.82rem; line-height: 1.5; margin: 0.25rem 0 0; }
    </style>
@endsection