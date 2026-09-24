@extends('layouts.app')

@section('content')
<style>
    .fa-rc {
        /* Design tokens — match the fee module */
        --fa-indigo-600: oklch(0.511 0.230 272);
        --fa-indigo-500: oklch(0.555 0.210 272);
        --fa-indigo-100: oklch(0.930 0.034 272);
        --fa-indigo-50:  oklch(0.962 0.018 272);
        --fa-slate-900:  oklch(0.206 0.010 264);
        --fa-slate-700:  oklch(0.372 0.016 264);
        --fa-slate-500:  oklch(0.554 0.018 264);
        --fa-slate-400:  oklch(0.704 0.015 264);
        --fa-slate-200:  oklch(0.928 0.008 264);
        --fa-slate-100:  oklch(0.967 0.005 264);
        --fa-slate-50:   oklch(0.984 0.003 264);
        --fa-surface:    oklch(0.995 0.003 264);
        --fa-emerald-600: oklch(0.596 0.145 163);
        --fa-emerald-500: oklch(0.696 0.170 162);
        --fa-emerald-50:  oklch(0.979 0.021 166);
        --fa-rose-600:   oklch(0.575 0.210 22);
        --fa-rose-500:   oklch(0.643 0.220 22);
        --fa-rose-50:    oklch(0.969 0.015 12);
        --fa-amber-600:  oklch(0.666 0.179 58);
        --fa-amber-50:   oklch(0.980 0.022 95);
        --fa-ease-out:   cubic-bezier(0.23, 1, 0.32, 1);
        --fa-mono: ui-monospace, "SFMono-Regular", "Cascadia Code", Menlo, Consolas, monospace;
    }

    /* ── Page header ── */
    .fa-head { padding: 0.25rem 0 1.25rem; }
    .fa-head h1 {
        font-size: 1.35rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        color: var(--fa-slate-900);
        margin: 0;
    }
    .fa-head-sub { color: var(--fa-slate-500); font-size: 0.813rem; margin-top: 0.125rem; }

    /* ── Metric cards ── */
    .fa-metric {
        background: var(--fa-surface);
        border: 1px solid var(--fa-slate-200);
        border-radius: 14px;
        box-shadow: 0 1px 2px oklch(0 0 0 / 0.04);
        padding: 1rem 1.125rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        height: 100%;
    }
    .fa-metric-label {
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--fa-slate-500);
    }
    .fa-metric-value {
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.02em;
        line-height: 1.1;
        margin-top: 0.25rem;
        font-variant-numeric: tabular-nums;
        color: var(--fa-slate-900);
    }
    .fa-metric-value--mono { font-family: var(--fa-mono); }
    .fa-metric-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        flex-shrink: 0;
    }
    .fa-metric--emerald .fa-metric-icon { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }
    .fa-metric--emerald .fa-metric-value{ color: var(--fa-emerald-600); }
    .fa-metric--indigo .fa-metric-icon  { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); }
    .fa-metric--indigo .fa-metric-value { color: var(--fa-indigo-600); }
    .fa-metric--amber .fa-metric-icon   { background: var(--fa-amber-50);  color: var(--fa-amber-600); }
    .fa-metric--amber .fa-metric-value  { color: var(--fa-amber-600); }
    .fa-metric--rose .fa-metric-icon    { background: var(--fa-rose-50);   color: var(--fa-rose-600); }
    .fa-metric--rose .fa-metric-value   { color: var(--fa-rose-600); }
    .fa-sub-line { font-size: 0.75rem; color: var(--fa-slate-500); }

    /* ── Card shell ── */
    .fa-panel .card-header { padding: 1.125rem 1.375rem; }
    .fa-panel-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--fa-slate-900);
        display: flex;
        align-items: center;
        gap: 0.625rem;
        margin: 0;
    }
    .fa-panel-title .fa-panel-mark {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: var(--fa-indigo-50);
        color: var(--fa-indigo-600);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    /* ── Filters ── */
    .fa-filters { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .fa-filters select.form-control,
    .fa-filters input.form-control {
        height: 38px !important;
        padding: 0 2rem 0 0.75rem !important;
        font-size: 0.8125rem !important;
        line-height: 1.4 !important;
        background-color: #fff;
    }
    .fa-filters select.form-control {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.5 6l3.5 4 3.5-4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.55rem center;
        background-size: 14px 14px;
        cursor: pointer;
    }
    .fa-filters input[type="date"].form-control,
    .fa-filters input[type="text"].form-control { padding-right: 0.75rem !important; }
    .fa-search { position: relative; }
    .fa-search > i {
        position: absolute;
        left: 0.8rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.8rem;
        color: var(--fa-slate-400);
        pointer-events: none;
        z-index: 2;
    }
    .fa-search input.form-control,
    .fa-filters .fa-search input.form-control {
        padding-left: 2.15rem !important;
    }
    .fa-filter-date { width: 150px; }
    .fa-filter-select { width: 160px; }

    /* ── Detail banner ── */
    .fa-detailbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        background: var(--fa-indigo-50);
        border-bottom: 1px solid var(--fa-indigo-100);
        padding: 0.75rem 1.375rem;
    }
    .fa-detailbar-text { font-size: 0.8125rem; color: var(--fa-indigo-600); font-weight: 600; }

    /* ── Table ── */
    .fa-table { margin: 0; }
    .fa-table thead th {
        background: var(--fa-slate-50);
        border-bottom: 1px solid var(--fa-slate-200);
        padding: 0.75rem 1rem;
        font-size: 0.6875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--fa-slate-500);
        white-space: nowrap;
        vertical-align: middle;
    }
    .fa-table thead th:first-child { padding-left: 1.375rem; }
    .fa-table tbody td {
        padding: 0.8125rem 1rem;
        border-color: var(--fa-slate-100);
        vertical-align: middle;
    }
    .fa-table tbody td:first-child { padding-left: 1.375rem; }
    .fa-table tbody tr { transition: background-color 0.15s var(--fa-ease-out); }
    @media (hover: hover) and (pointer: fine) {
        .fa-table tbody tr:hover { background: oklch(0.970 0.003 264); }
    }
    .fa-table-amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-family: var(--fa-mono); }
    .amount-negative { color: var(--fa-rose-600); }
    .value-muted { color: var(--fa-slate-400); }
    .fa-main-line { color: var(--fa-slate-900); font-weight: 600; font-size: 0.875rem; }
    .fa-strike { text-decoration: line-through; }

    .fa-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 600;
        background: var(--fa-indigo-50);
        color: var(--fa-indigo-500);
        white-space: nowrap;
    }
    .fa-chip--emerald { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }

    .fa-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.28rem 0.625rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .fa-pill--ok::before {
        content: '';
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--fa-emerald-500);
    }
    .fa-pill--ok     { background: var(--fa-emerald-50); border: 1px solid oklch(0.889 0.048 163); color: var(--fa-emerald-600); }
    .fa-pill--void   { background: var(--fa-rose-50);   border: 1px solid oklch(0.897 0.030 12 / 0.6); color: var(--fa-rose-600); }
    .fa-pill--void::before {
        content: '';
        width: 6px; height: 6px; border-radius: 50%;
        background: var(--fa-rose-500);
    }

    .fa-drill { color: var(--fa-slate-400); flex: 0 0 auto; text-align: center; }
    .fa-actions { display: inline-flex; gap: 0.375rem; justify-content: flex-end; }
    .fa-action {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: none;
        background: var(--fa-slate-100);
        color: oklch(0.446 0.018 264);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        cursor: pointer;
        text-decoration: none;
        transition: transform 0.16s var(--fa-ease-out), background-color 0.16s var(--fa-ease-out), color 0.16s var(--fa-ease-out);
    }
    .fa-action:active { transform: scale(0.96); }
    @media (hover: hover) and (pointer: fine) {
        .fa-action--view:hover { background: var(--fa-slate-200); color: var(--fa-slate-900); }
    }

    .fa-empty { padding: 4rem 1rem; text-align: center; color: var(--fa-slate-500); }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-filters { width: 100%; }
        .fa-filter-date, .fa-filter-select, .fa-search { width: 100%; }
        .fa-table thead th:nth-child(n+5),
        .fa-table tbody td:nth-child(n+5) { display: none; }
        .fa-table thead th:first-child, .fa-table tbody td:first-child { padding-left: 0.875rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fa-action { transition: none; }
    }
</style>

@php
    $qs = array_diff_key(request()->query(), array_flip(['page', 'detail', 'receipt_number']));
@endphp

<div class="fa-rc">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Receipt Register</h1>
                    <p class="fa-head-sub">Every issued receipt grouped per collection day — click a day to see its receipts</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('fees.dashboard') }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.8rem;"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-0">
        @include('flash::message')

        {{-- Metric cards — whole filtered set, not just the visible page --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--emerald">
                    <div>
                        <div class="fa-metric-label">Collected</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_collected ?? 0) }}</div>
                        <div class="fa-sub-line">{{ number_format($stats->valid_count ?? 0) }} valid receipts</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Receipts</div>
                        <div class="fa-metric-value">{{ number_format($stats->valid_count ?? 0) }}</div>
                        <div class="fa-sub-line">across {{ number_format($stats->collectors_count ?? 0) }} collector{{ ($stats->collectors_count ?? 0) === 1 ? '' : 's' }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-receipt"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--amber">
                    <div>
                        <div class="fa-metric-label">Voided</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->voided_amount ?? 0) }}</div>
                        <div class="fa-sub-line">{{ number_format($stats->void_count ?? 0) }} voided receipt{{ ($stats->void_count ?? 0) === 1 ? '' : 's' }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-ban"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--rose">
                    <div>
                        <div class="fa-metric-label">Average Receipt</div>
                        <div class="fa-metric-value fa-metric-value--mono">
                            @php $validCount = (int) ($stats->valid_count ?? 0); @endphp
                            {{ \App\Support\Money::format($validCount > 0 ? $stats->total_collected / $validCount : 0) }}
                        </div>
                        <div class="fa-sub-line">valid receipts only</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
        </div>

        {{-- Main table card --}}
        <div class="card fa-panel">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark"><i class="fas {{ $detailMode ? 'fa-list-ul' : 'fa-calendar-day' }}"></i></span>
                        {{ $detailMode ? 'Receipt Lines' : 'Daily Collections' }}
                        @if(!$detailMode)
                            <span style="font-weight: 500; color: var(--fa-slate-400); font-size: 0.8125rem;">
                                · {{ number_format($rollups->total()) }} collection day{{ $rollups->total() === 1 ? '' : 's' }}
                            </span>
                        @endif
                    </h3>
                    <form action="{{ route('fees.reports.receipt-register') }}" method="GET" class="fa-filters">
                        @if($detailMode)
                            <input type="hidden" name="detail" value="1">
                            @foreach(['date', 'from', 'to', 'payment_method', 'collected_by'] as $keep)
                                @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
                            @endforeach
                        @endif
                        <div class="fa-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input type="text" name="receipt_number" class="form-control form-control-sm" style="width: 180px;"
                                   placeholder="Receipt no..." value="{{ request('receipt_number') }}" aria-label="Search by receipt number">
                        </div>
                        <input type="date" name="date" class="form-control form-control-sm fa-filter-date" value="{{ request('date') }}" aria-label="Specific date">
                        <input type="date" name="from" class="form-control form-control-sm fa-filter-date" value="{{ request('from') }}" aria-label="From date">
                        <input type="date" name="to" class="form-control form-control-sm fa-filter-date" value="{{ request('to') }}" aria-label="To date">
                        <select name="payment_method" class="form-control form-control-sm fa-filter-select" aria-label="Filter by payment method">
                            <option value="">All Methods</option>
                            @foreach($methods as $code => $label)
                                <option value="{{ $code }}" {{ request('payment_method') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <select name="collected_by" class="form-control form-control-sm fa-filter-select" aria-label="Filter by collector">
                            <option value="">All Collectors</option>
                            @foreach($collectors as $id => $name)
                                <option value="{{ $id }}" {{ request('collected_by') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-filter mr-1" style="font-size: 0.75rem;"></i> Filter
                        </button>
                        @if(collect(['date', 'from', 'to', 'receipt_number', 'payment_method', 'collected_by'])->contains(fn($k) => request()->filled($k)))
                            <a href="{{ route('fees.reports.receipt-register') }}" class="btn btn-sm btn-link" style="color: var(--fa-slate-500); text-decoration: none; font-weight: 600;">
                                Reset
                            </a>
                        @endif

                        {{-- Print / Export: carries the active filters through, so the
                             PDF shows exactly the receipts on this screen. --}}
                        <a href="{{ route('fees.reports.export.receipt-register.pdf', request()->query()) }}"
                           class="btn btn-sm"
                           style="height: 38px; display: inline-flex; align-items: center; border-radius: 8px; font-weight: 600; background: #fff; color: var(--fa-indigo-600); border: 1px solid var(--fa-indigo-100);">
                            <i class="fas fa-file-pdf mr-1" style="font-size: 0.75rem;"></i> Print / Export PDF
                        </a>
                    </form>
                </div>
            </div>

            @if($detailMode)
                {{-- ── Detail mode: individual receipts for one day or a search ── --}}
                <div class="fa-detailbar">
                    <div class="fa-detailbar-text">
                        <i class="fas fa-receipt mr-1"></i> {{ $detailLabel }}
                    </div>
                    <a href="{{ route('fees.reports.receipt-register', $qs) }}"
                       class="btn btn-sm" style="border-radius: 8px; font-weight: 600; background: #fff; color: var(--fa-indigo-600); border: 1px solid var(--fa-indigo-100);">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.7rem;"></i> Back to summary
                    </a>
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    @if(!$detailMode)
                    {{-- ── Roll-up view: one row per collection day ── --}}
                    <table class="table fa-table mb-0" style="min-width: 820px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Receipts</th>
                                <th class="fa-table-amount">Collected</th>
                                <th class="fa-table-amount">Voided</th>
                                <th>Methods</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rollups as $row)
                                @php
                                    $methodChips = array_slice(array_filter(array_map('trim', explode(',', (string) $row->methods))), 0, 3);
                                    $detailUrl = route('fees.reports.receipt-register', array_merge($qs, [
                                        'detail' => 1,
                                        'date' => $row->day,
                                    ]));
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location='{{ $detailUrl }}'">
                                    <td>
                                        <div class="fa-main-line">{{ \Carbon\Carbon::parse($row->day)->format('D, d M Y') }}</div>
                                        <div class="fa-sub-line">{{ \Carbon\Carbon::parse($row->day)->isToday() ? 'Today' : \Carbon\Carbon::parse($row->day)->translatedFormat('l') }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span style="font-weight: 700; color: var(--fa-slate-900); font-variant-numeric: tabular-nums;">{{ number_format($row->valid_count) }}</span>
                                        @if((int) $row->void_count > 0)
                                            <div class="fa-sub-line">{{ number_format($row->void_count) }} voided</div>
                                        @endif
                                    </td>
                                    <td class="fa-table-amount" style="color: var(--fa-emerald-600); font-weight: 600;">{{ \App\Support\Money::format($row->total_collected) }}</td>
                                    <td class="fa-table-amount">
                                        @if((float) $row->voided_amount > 0)
                                            <span class="amount-negative">{{ \App\Support\Money::format($row->voided_amount) }}</span>
                                        @else
                                            <span class="value-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @forelse($methodChips as $m)
                                            <span class="fa-chip">{{ ucwords(str_replace('_', ' ', $m)) }}</span>
                                        @empty
                                            <span class="value-muted">—</span>
                                        @endforelse
                                        @if(str_contains((string) $row->methods, ',') && count($methodChips) >= 3)
                                            <span class="value-muted">+{{ substr_count((string) $row->methods, ',') + 1 - 3 }}</span>
                                        @endif
                                    </td>
                                    <td class="fa-drill" onclick="event.stopPropagation();">
                                        <div class="fa-actions">
                                            <a href="{{ $detailUrl }}" class="fa-action fa-action--view" title="View receipts for this day" aria-label="View receipts for {{ $row->day }}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-receipt" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No receipts found</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Adjust your date range or filters to see collections.</p>
                                            <a href="{{ route('fees.reports.receipt-register') }}" class="btn btn-primary">
                                                <i class="fas fa-times mr-1"></i> Clear Filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    {{-- ── Detail view: individual receipts ── --}}
                    <table class="table fa-table mb-0" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th>Receipt No</th>
                                <th>Student</th>
                                <th>Charge</th>
                                <th>Method</th>
                                <th>Collected By</th>
                                <th class="fa-table-amount">Amount</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($receipts as $p)
                                @php $voided = $p->isReversed(); @endphp
                                <tr class="{{ $voided ? 'opacity-50' : '' }}">
                                    <td>
                                        <div class="fa-main-line" style="font-family: var(--fa-mono);">{{ $p->receipt_number ?? 'RCP-'.$p->payment_id }}</div>
                                        <div class="fa-sub-line">{{ $p->payment_date ? $p->payment_date->format('d M Y') : '—' }}</div>
                                    </td>
                                    <td>
                                        <div class="fa-main-line">{{ $p->studentFeeAssignment->student->full_name ?? 'N/A' }}</div>
                                        <div class="fa-sub-line">{{ $p->studentFeeAssignment->student->admission_no ?? '' }}</div>
                                    </td>
                                    <td class="value-muted">{{ $p->studentFeeAssignment->feeStructure->category->name ?? '—' }}</td>
                                    <td>
                                        <span class="fa-chip">{{ $p->payment_method ? ucwords(str_replace('_', ' ', $p->payment_method)) : 'Unspecified' }}</span>
                                    </td>
                                    <td class="value-muted">{{ $p->collectedBy->full_name ?? '—' }}</td>
                                    <td class="fa-table-amount {{ $voided ? 'fa-strike value-muted' : 'amount-net' }}">{{ \App\Support\Money::format($p->amount) }}</td>
                                    <td class="text-center">
                                        @if($voided)
                                            <span class="fa-pill fa-pill--void">VOID</span>
                                        @else
                                            <span class="fa-pill fa-pill--ok">Valid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-receipt" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No receipts in this view</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Try widening the date range, or go back to the summary.</p>
                                            <a href="{{ route('fees.reports.receipt-register') }}" class="btn btn-primary">
                                                <i class="fas fa-arrow-left mr-1"></i> Back to summary
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

            @php $paginator = $detailMode ? $receipts : $rollups; @endphp
            @if($paginator->hasPages())
                <div class="card-footer bg-white" style="border-top: 1px solid var(--fa-slate-100); padding: 0.875rem 1.375rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div style="color: var(--fa-slate-500); font-size: 0.8125rem;">
                            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ number_format($paginator->total()) }}
                            {{ $detailMode ? 'receipts' : 'collection days' }}
                        </div>
                        <div>
                            {{ $paginator->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if(!$detailMode && ($voidedReceipts ?? collect())->isNotEmpty())
            {{-- Voided receipts are retained for audit and must never simply
                 disappear. The roll-up groups by day, so a voided receipt would
                 otherwise only be visible after drilling into its day — this
                 panel keeps them on the register's main screen, clearly marked
                 and explicitly excluded from the collected totals. --}}
            <div class="card fa-panel mt-4">
                <div class="card-header">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark" style="background: var(--fa-rose-50); color: var(--fa-rose-600);">
                            <i class="fas fa-ban"></i>
                        </span>
                        Voided receipts
                        <span style="font-weight: 500; color: var(--fa-slate-400); font-size: 0.8125rem;">
                            · {{ number_format($voidedReceipts->count()) }} receipt{{ $voidedReceipts->count() === 1 ? '' : 's' }} retained for audit — excluded from every collected total
                        </span>
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table fa-table mb-0" style="min-width: 820px;">
                            <thead>
                                <tr>
                                    <th>Receipt No</th>
                                    <th>Date</th>
                                    <th>Student</th>
                                    <th class="fa-table-amount">Amount</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($voidedReceipts as $v)
                                    <tr>
                                        <td style="font-family: var(--fa-mono);">{{ $v->receipt_number ?? 'RCP-'.$v->payment_id }}</td>
                                        <td class="value-muted">{{ $v->payment_date ? $v->payment_date->format('d M Y') : '—' }}</td>
                                        <td>
                                            <div class="fa-main-line">{{ $v->studentFeeAssignment->student->full_name ?? 'N/A' }}</div>
                                            <div class="fa-sub-line">{{ $v->studentFeeAssignment->student->admission_no ?? '' }}</div>
                                        </td>
                                        <td class="fa-table-amount"><span class="amount-negative">{{ \App\Support\Money::format($v->amount) }}</span></td>
                                        <td><span class="fa-pill fa-pill--void">VOID</span></td>
                                        <td class="value-muted">{{ $v->reversal_reason ?: '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="font-weight: 700; border-top: 2px solid var(--fa-slate-200);">Total voided (not collected)</td>
                                    <td class="fa-table-amount" style="font-weight: 700; border-top: 2px solid var(--fa-slate-200);">{{ \App\Support\Money::format($voidedReceipts->sum('amount')) }}</td>
                                    <td colspan="2" style="border-top: 2px solid var(--fa-slate-200);"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
(function () {
    // Date inputs and selects submit on change for quicker filtering.
    var form = document.querySelector('.fa-filters');
    if (form) {
        form.querySelectorAll('input[type="date"], select').forEach(function (el) {
            el.addEventListener('change', function () { form.submit(); });
        });
    }
})();
</script>
@endsection
