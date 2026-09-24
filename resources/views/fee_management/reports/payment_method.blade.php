@extends('layouts.app')

@section('content')
<style>
    .fa-pm {
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
        --fa-emerald-50:  oklch(0.979 0.021 166);
        --fa-ease-out:   cubic-bezier(0.23, 1, 0.32, 1);
        --fa-mono: ui-monospace, "SFMono-Regular", "Cascadia Code", Menlo, Consolas, monospace;
    }

    .fa-head { padding: 0.25rem 0 1.25rem; }
    .fa-head h1 { font-size: 1.35rem; font-weight: 800; letter-spacing: -0.02em; color: var(--fa-slate-900); margin: 0; }
    .fa-head-sub { color: var(--fa-slate-500); font-size: 0.813rem; margin-top: 0.125rem; }

    .fa-metric {
        background: var(--fa-surface); border: 1px solid var(--fa-slate-200); border-radius: 14px;
        box-shadow: 0 1px 2px oklch(0 0 0 / 0.04); padding: 1rem 1.125rem;
        display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; height: 100%;
    }
    .fa-metric-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--fa-slate-500); }
    .fa-metric-value { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1.1; margin-top: 0.25rem; font-variant-numeric: tabular-nums; color: var(--fa-slate-900); }
    .fa-metric-value--mono { font-family: var(--fa-mono); }
    .fa-metric-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; flex-shrink: 0; }
    .fa-metric--indigo .fa-metric-icon  { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); }
    .fa-metric--indigo .fa-metric-value { color: var(--fa-indigo-600); }
    .fa-metric--emerald .fa-metric-icon { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }
    .fa-metric--emerald .fa-metric-value{ color: var(--fa-emerald-600); }
    .fa-sub-line { font-size: 0.75rem; color: var(--fa-slate-500); }

    .fa-panel .card-header { padding: 1.125rem 1.375rem; }
    .fa-panel-title { font-weight: 700; font-size: 0.95rem; color: var(--fa-slate-900); display: flex; align-items: center; gap: 0.625rem; margin: 0; }
    .fa-panel-title .fa-panel-mark {
        width: 30px; height: 30px; border-radius: 8px; background: var(--fa-indigo-50); color: var(--fa-indigo-600);
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0;
    }

    .fa-filters { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .fa-filters select.form-control,
    .fa-filters input.form-control {
        height: 38px !important; padding: 0 2rem 0 0.75rem !important; font-size: 0.8125rem !important;
        line-height: 1.4 !important; background-color: #fff;
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
    }
    .fa-filters select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.5 6l3.5 4 3.5-4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.55rem center; background-size: 14px 14px; cursor: pointer;
    }
    .fa-filters input.form-control { padding-right: 0.75rem !important; }
    .fa-filter-select { width: 170px; }
    .fa-filter-date { width: 145px; }

    .fa-detailbar {
        display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;
        background: var(--fa-indigo-50); border-bottom: 1px solid var(--fa-indigo-100); padding: 0.75rem 1.375rem;
    }
    .fa-detailbar-text { font-size: 0.8125rem; color: var(--fa-indigo-600); font-weight: 600; }

    .fa-table { margin: 0; }
    .fa-table thead th {
        background: var(--fa-slate-50); border-bottom: 1px solid var(--fa-slate-200); padding: 0.75rem 1rem;
        font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em;
        color: var(--fa-slate-500); white-space: nowrap; vertical-align: middle;
    }
    .fa-table thead th:first-child { padding-left: 1.375rem; }
    .fa-table tbody td { padding: 0.8125rem 1rem; border-color: var(--fa-slate-100); vertical-align: middle; }
    .fa-table tbody td:first-child { padding-left: 1.375rem; }
    .fa-table tbody tr { transition: background-color 0.15s var(--fa-ease-out); }
    @media (hover: hover) and (pointer: fine) {
        .fa-table tbody tr:hover { background: oklch(0.970 0.003 264); }
    }
    .fa-table-amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-family: var(--fa-mono); }
    .value-muted { color: var(--fa-slate-400); }
    .fa-main-line { color: var(--fa-slate-900); font-weight: 600; font-size: 0.875rem; }

    .fa-chip { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.72rem; font-weight: 600; background: var(--fa-indigo-50); color: var(--fa-indigo-500); white-space: nowrap; }

    .fa-progress { width: 120px; height: 5px; border-radius: 999px; background: var(--fa-slate-200); overflow: hidden; margin-top: 0.375rem; }
    .fa-progress > span { display: block; height: 100%; border-radius: 999px; background: var(--fa-emerald-500); }
    .fa-progress-label { font-size: 0.72rem; color: var(--fa-slate-500); font-weight: 600; font-variant-numeric: tabular-nums; }

    .fa-drill { color: var(--fa-slate-400); flex: 0 0 auto; text-align: center; }
    .fa-actions { display: inline-flex; gap: 0.375rem; justify-content: flex-end; }
    .fa-action {
        width: 34px; height: 34px; border-radius: 8px; border: none; background: var(--fa-slate-100);
        color: oklch(0.446 0.018 264); display: inline-flex; align-items: center; justify-content: center;
        font-size: 0.8rem; cursor: pointer; text-decoration: none;
        transition: transform 0.16s var(--fa-ease-out), background-color 0.16s var(--fa-ease-out), color 0.16s var(--fa-ease-out);
    }
    .fa-action:active { transform: scale(0.96); }
    @media (hover: hover) and (pointer: fine) {
        .fa-action--view:hover { background: var(--fa-slate-200); color: var(--fa-slate-900); }
    }

    .fa-empty { padding: 4rem 1rem; text-align: center; color: var(--fa-slate-500); }

    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-filters { width: 100%; }
        .fa-filter-select, .fa-filter-date { width: 100%; }
        .fa-table thead th:nth-child(n+4), .fa-table tbody td:nth-child(n+4) { display: none; }
        .fa-table thead th:first-child, .fa-table tbody td:first-child { padding-left: 0.875rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fa-action { transition: none; }
    }
</style>

@php
    $qs = array_diff_key(request()->query(), array_flip(['page', 'detail', 'payment_method']));
@endphp

<div class="fa-pm">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Payment Methods</h1>
                    <p class="fa-head-sub">Collections grouped by payment method — click a method to see its daily trend</p>
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

        {{-- Metric cards --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Grand Total</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->grand_total ?? 0) }}</div>
                        <div class="fa-sub-line">{{ number_format($stats->payments_count ?? 0) }} valid payments</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-coins"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--emerald">
                    <div>
                        <div class="fa-metric-label">Methods Used</div>
                        <div class="fa-metric-value">{{ number_format($stats->methods_used ?? 0) }}</div>
                        <div class="fa-sub-line">of {{ count(\App\Models\FeePayment::PAYMENT_METHODS) }} supported</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-wallet"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Average Payment</div>
                        <div class="fa-metric-value fa-metric-value--mono">
                            @php $pc = (int) ($stats->payments_count ?? 0); @endphp
                            {{ \App\Support\Money::format($pc > 0 ? $stats->grand_total / $pc : 0) }}
                        </div>
                        <div class="fa-sub-line">valid payments only</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-chart-line"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--emerald">
                    <div>
                        <div class="fa-metric-label">Paying Today</div>
                        <div class="fa-metric-value">{{ number_format($stats->today_payers ?? 0) }}</div>
                        <div class="fa-sub-line">students</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-user-check"></i></div>
                </div>
            </div>
        </div>

        {{-- Main table card --}}
        <div class="card fa-panel">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark"><i class="fas {{ $detailMode ? 'fa-calendar-day' : 'fa-credit-card' }}"></i></span>
                        {{ $detailMode ? 'Daily Trend' : 'Collection by Method' }}
                    </h3>
                    <form action="{{ route('fees.reports.payment-method') }}" method="GET" class="fa-filters">
                        @if($detailMode)
                            <input type="hidden" name="detail" value="1">
                            @if(request()->filled('payment_method'))<input type="hidden" name="payment_method" value="{{ request('payment_method') }}">@endif
                        @endif
                        <select name="academic_year_id" class="form-control form-control-sm fa-filter-select" aria-label="Filter by academic year">
                            <option value="">All Years</option>
                            @foreach($academicYears as $id => $name)
                                <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="date" name="from" class="form-control form-control-sm fa-filter-date" value="{{ request('from') }}" aria-label="From date">
                        <input type="date" name="to" class="form-control form-control-sm fa-filter-date" value="{{ request('to') }}" aria-label="To date">
                        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-filter mr-1" style="font-size: 0.75rem;"></i> Filter
                        </button>
                        @if(request()->filled('from') || request()->filled('to') || request()->filled('academic_year_id'))
                            <a href="{{ route('fees.reports.payment-method') }}" class="btn btn-sm btn-link" style="color: var(--fa-slate-500); text-decoration: none; font-weight: 600;">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($detailMode)
                <div class="fa-detailbar">
                    <div class="fa-detailbar-text">
                        Daily collections via <strong>{{ $methodLabels[$methodFilter] ?? ucwords(str_replace('_', ' ', (string) $methodFilter)) }}</strong>
                    </div>
                    <a href="{{ route('fees.reports.payment-method', array_diff_key($qs, ['payment_method' => ''])) }}"
                       class="btn btn-sm" style="border-radius: 8px; font-weight: 600; background: #fff; color: var(--fa-indigo-600); border: 1px solid var(--fa-indigo-100);">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.7rem;"></i> Back to summary
                    </a>
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    @if(!$detailMode)
                    {{-- ── Roll-up: one row per method ── --}}
                    <table class="table fa-table mb-0" style="min-width: 720px;">
                        <thead>
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-center">Payments</th>
                                <th>Share</th>
                                <th class="fa-table-amount">Total</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byMethod as $m)
                                @php
                                    $share = $grandTotal > 0 ? round(($m->total / $grandTotal) * 100, 1) : 0;
                                    $label = ($m->payment_method === '' || $m->payment_method === null)
                                        ? 'Unspecified'
                                        : ucwords(str_replace('_', ' ', $m->payment_method));
                                    $detailUrl = $m->payment_method !== '' && $m->payment_method !== null
                                        ? route('fees.reports.payment-method', array_merge($qs, ['detail' => 1, 'payment_method' => $m->payment_method]))
                                        : null;
                                @endphp
                                <tr @if($detailUrl) style="cursor: pointer;" onclick="window.location='{{ $detailUrl }}'" @endif>
                                    <td><div class="fa-main-line">{{ $label }}</div></td>
                                    <td class="text-center" style="font-weight: 700; color: var(--fa-slate-900); font-variant-numeric: tabular-nums;">{{ number_format($m->count) }}</td>
                                    <td>
                                        <div class="fa-progress-label">{{ $share }}%</div>
                                        <div class="fa-progress"><span style="width: {{ min(100, $share) }}%;"></span></div>
                                    </td>
                                    <td class="fa-table-amount" style="color: var(--fa-emerald-600); font-weight: 600;">{{ \App\Support\Money::format($m->total) }}</td>
                                    <td class="fa-drill" onclick="event.stopPropagation();">
                                        <div class="fa-actions">
                                            @if($detailUrl)
                                                <a href="{{ $detailUrl }}" class="fa-action fa-action--view" title="View daily trend" aria-label="View daily trend for {{ $label }}">
                                                    <i class="far fa-eye"></i>
                                                </a>
                                            @else
                                                <span class="value-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-credit-card" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No payments found</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Adjust your date range or academic year.</p>
                                            <a href="{{ route('fees.reports.payment-method') }}" class="btn btn-primary">
                                                <i class="fas fa-times mr-1"></i> Clear Filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($byMethod->count())
                        <tfoot>
                            <tr>
                                <td style="padding: 0.875rem 1rem 0.875rem 1.375rem; font-weight: 700; color: var(--fa-slate-900); background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200);">Total</td>
                                <td class="text-center" style="font-weight: 700; background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200); font-variant-numeric: tabular-nums;">{{ number_format($byMethod->sum('count')) }}</td>
                                <td style="background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200);">100%</td>
                                <td class="fa-table-amount" style="font-weight: 700; background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200);">{{ \App\Support\Money::format($grandTotal) }}</td>
                                <td style="background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200);"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                    @else
                    {{-- ── Detail: per-day totals for one method ── --}}
                    <table class="table fa-table mb-0" style="min-width: 560px;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th class="text-center">Payments</th>
                                <th class="fa-table-amount">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($byDay as $d)
                                <tr>
                                    <td>
                                        <div class="fa-main-line">{{ \Carbon\Carbon::parse($d->day)->format('D, d M Y') }}</div>
                                        <div class="fa-sub-line">{{ \Carbon\Carbon::parse($d->day)->isToday() ? 'Today' : \Carbon\Carbon::parse($d->day)->translatedFormat('l') }}</div>
                                    </td>
                                    <td class="text-center" style="font-weight: 700; font-variant-numeric: tabular-nums;">{{ number_format($d->count) }}</td>
                                    <td class="fa-table-amount" style="color: var(--fa-emerald-600); font-weight: 600;">{{ \App\Support\Money::format($d->total) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-calendar-times" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No payments via this method</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Try widening the date range.</p>
                                            <a href="{{ route('fees.reports.payment-method', array_diff_key($qs, ['payment_method' => ''])) }}" class="btn btn-primary">
                                                <i class="fas fa-arrow-left mr-1"></i> Back to summary
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($byDay->count())
                            {{-- Reconciliation total: the bursar should never have to
                                 add the money column by hand. This is the total for
                                 the days on THIS page (the metric card above shows the
                                 whole filtered period). --}}
                            <tfoot>
                                <tr>
                                    <td style="padding: 0.875rem 1rem 0.875rem 1.375rem; font-weight: 700; color: var(--fa-slate-900); background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200);">
                                        Page total
                                        <span style="font-weight: 500; color: var(--fa-slate-400);">· {{ number_format($byDay->count()) }} day{{ $byDay->count() === 1 ? '' : 's' }}</span>
                                    </td>
                                    <td class="text-center" style="font-weight: 700; background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200); font-variant-numeric: tabular-nums;">{{ number_format($byDay->sum('count')) }}</td>
                                    <td class="fa-table-amount" style="font-weight: 700; background: var(--fa-slate-50); border-top: 2px solid var(--fa-slate-200);">{{ \App\Support\Money::format($byDay->sum('total')) }}</td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                    @endif
                </div>
            </div>

            @php $paginator = $detailMode ? $byDay : null; @endphp
            @if($paginator && $paginator->hasPages())
                <div class="card-footer bg-white" style="border-top: 1px solid var(--fa-slate-100); padding: 0.875rem 1.375rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div style="color: var(--fa-slate-500); font-size: 0.8125rem;">
                            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ number_format($paginator->total()) }} days
                        </div>
                        <div>
                            {{ $paginator->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    var form = document.querySelector('.fa-filters');
    if (form) {
        form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
            el.addEventListener('change', function () { form.submit(); });
        });
    }
})();
</script>
@endsection
