@extends('layouts.app')

@section('content')
    @php
        $statusStyles = [
            'active' => ['label' => 'Active', 'bg' => '#ecfdf5', 'fg' => '#059669'],
            'pending' => ['label' => 'Pending', 'bg' => '#fffbeb', 'fg' => '#d97706'],
            'rejected' => ['label' => 'Rejected', 'bg' => '#fff1f2', 'fg' => '#e11d48'],
        ];
        $status = $statusStyles[$income->status] ?? ['label' => ucfirst($income->status ?? 'Active'), 'bg' => '#f1f5f9', 'fg' => '#475569'];
    @endphp

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <div class="detail-heading">
                        <div class="detail-heading-icon detail-heading-icon--income">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <h1 class="detail-heading-title">Income Details</h1>
                            <p class="detail-heading-sub">Ref {{ $income->reference_number ?: '—' }} &middot; {{ $income->income_date ? $income->income_date->format('d M, Y') : '—' }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 detail-heading-actions">
                    <a href="{{ route('income.edit', $income->income_id) }}" class="btn-detail btn-detail--primary btn-detail--income">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <a href="{{ route('income.index') }}" class="btn-detail btn-detail--ghost">
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
                    <div class="detail-label">Amount</div>
                    <div class="detail-amount detail-amount--income">{{ \App\Support\Money::format($income->amount ?? 0) }}</div>
                    <div class="detail-summary-foot">
                        <span class="detail-badge" style="background: {{ $status['bg'] }}; color: {{ $status['fg'] }}">{{ $status['label'] }}</span>
                        <span class="detail-date">{{ $income->income_date ? $income->income_date->format('d M, Y') : '—' }}</span>
                    </div>
                </div>

                <div class="detail-card">
                    <div class="detail-card-head">
                        <i class="fas fa-user-check mr-2"></i> Recorded
                    </div>
                    <div class="detail-row">
                        <span class="detail-row-label">Received By</span>
                        <span class="detail-row-value">{{ $income->receivedBy->name ?? '—' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-row-label">Recorded On</span>
                        <span class="detail-row-value">{{ $income->created_at ? $income->created_at->format('d M, Y') : '—' }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                @include('income.show_fields')
            </div>
        </div>
    </div>

    <style>
        .detail-heading { display: flex; align-items: center; gap: 14px; }
        .detail-heading-icon {
            width: 44px; height: 44px; border-radius: 10px;
            background: #fff1f2; color: #e11d48;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem; flex-shrink: 0;
        }
        .detail-heading-icon--income { background: #ecfdf5; color: #059669; }
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
        .btn-detail--primary { background: #e11d48; border: 1px solid #e11d48; color: #fff; }
        .btn-detail--primary:hover { background: #be123c; border-color: #be123c; color: #fff; }
        .btn-detail--income { background: #059669; border-color: #059669; }
        .btn-detail--income:hover { background: #047857; border-color: #047857; color: #fff; }
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
            font-size: 1.75rem; font-weight: 900; color: #0f172a; margin-top: 4px;
        }
        .detail-amount--income { color: #047857; }
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
        .detail-row-label { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.04em; flex-shrink: 0; }
        .detail-row-value { font-size: 0.88rem; font-weight: 600; color: #1e293b; text-align: right; }

        .detail-description { color: #334155; font-size: 0.9rem; line-height: 1.6; margin: 0; }
    </style>
@endsection