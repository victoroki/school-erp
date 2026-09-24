@extends('layouts.app')

@section('content')
<style>
    .fa-er {
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
        --fa-rose-50:    oklch(0.969 0.015 12);
        --fa-amber-600:  oklch(0.666 0.179 58);
        --fa-amber-50:   oklch(0.980 0.022 95);
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
    .fa-metric--amber .fa-metric-icon   { background: var(--fa-amber-50);  color: var(--fa-amber-600); }
    .fa-metric--amber .fa-metric-value  { color: var(--fa-amber-600); }
    .fa-metric--rose .fa-metric-icon    { background: var(--fa-rose-50);   color: var(--fa-rose-600); }
    .fa-metric--rose .fa-metric-value   { color: var(--fa-rose-600); }
    .fa-sub-line { font-size: 0.75rem; color: var(--fa-slate-500); }

    .fa-panel .card-header { padding: 1.125rem 1.375rem; }
    .fa-panel-title { font-weight: 700; font-size: 0.95rem; color: var(--fa-slate-900); display: flex; align-items: center; gap: 0.625rem; margin: 0; }
    .fa-panel-title .fa-panel-mark {
        width: 30px; height: 30px; border-radius: 8px; background: var(--fa-indigo-50); color: var(--fa-indigo-600);
        display: inline-flex; align-items: center; justify-content: center; font-size: 0.8rem; flex-shrink: 0;
    }

    .fa-filters { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
    .fa-filters select.form-control {
        height: 38px !important; padding: 0 2rem 0 0.75rem !important; font-size: 0.8125rem !important;
        line-height: 1.4 !important; background-color: #fff;
        appearance: none; -webkit-appearance: none; -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%2364748b'%3E%3Cpath d='M4.5 6l3.5 4 3.5-4z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 0.55rem center; background-size: 14px 14px; cursor: pointer;
    }
    .fa-filter-select { width: 190px; }

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
    .amount-negative { color: var(--fa-rose-600); }
    .value-muted { color: var(--fa-slate-400); }
    .fa-main-line { color: var(--fa-slate-900); font-weight: 600; font-size: 0.875rem; }

    .fa-chip { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.72rem; font-weight: 600; background: var(--fa-indigo-50); color: var(--fa-indigo-500); white-space: nowrap; }

    .fa-pill { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.28rem 0.625rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
    .fa-pill--paid { background: var(--fa-emerald-50); border: 1px solid oklch(0.889 0.048 163); color: var(--fa-emerald-600); }
    .fa-pill--paid::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--fa-emerald-500); }
    .fa-pill--partial { background: var(--fa-amber-50); border: 1px solid oklch(0.900 0.065 70 / 0.6); color: var(--fa-amber-600); }
    .fa-pill--partial::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--fa-amber-600); }
    .fa-pill--unpaid { background: var(--fa-rose-50); border: 1px solid oklch(0.897 0.030 12 / 0.6); color: var(--fa-rose-600); }
    .fa-pill--unpaid::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--fa-rose-500); }

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

    /* Status bar */
    .fa-statusbar { display: flex; border-radius: 10px; overflow: hidden; height: 38px; box-shadow: 0 1px 2px oklch(0 0 0 / 0.06); }
    .fa-statusseg { display: flex; align-items: center; justify-content: center; gap: 4px; white-space: nowrap; overflow: hidden; }
    .fa-statusseg--paid { background: var(--fa-emerald-500); color: #fff; }
    .fa-statusseg--partial { background: var(--fa-amber-600); color: #fff; }
    .fa-statusseg--unpaid { background: var(--fa-rose-500); color: #fff; }
    .fa-statusseg .fa-statuscount { font-size: 0.8rem; font-weight: 800; }
    .fa-statusseg .fa-statuslabel { font-size: 0.68rem; font-weight: 600; opacity: 0.9; }

    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-filters { width: 100%; }
        .fa-filter-select { width: 100%; }
        .fa-table thead th:nth-child(n+4), .fa-table tbody td:nth-child(n+4) { display: none; }
        .fa-table thead th:first-child, .fa-table tbody td:first-child { padding-left: 0.875rem; }
        .fa-statusseg .fa-statuslabel { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fa-action { transition: none; }
    }
</style>

@php
    $qs = array_diff_key(request()->query(), array_flip(['page', 'detail']));
    $totalAssignments = ($stats->paid_count ?? 0) + ($stats->partial_count ?? 0) + ($stats->unpaid_count ?? 0);
@endphp

<div class="fa-er">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Expected Revenue</h1>
                    <p class="fa-head-sub">Billed vs collected, grouped per class — click a class to see its students</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('fees.dashboard') }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.8rem;"></i> Dashboard
                    </a>
                    <a class="btn btn-primary" href="{{ route('fees.reports.export.expected-revenue.pdf', request()->query()) }}">
                        <i class="fas fa-file-pdf mr-1"></i> Export PDF
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
                        <div class="fa-metric-label">Net Expected (KES)</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_expected ?? 0) }}</div>
                        <div class="fa-sub-line">{{ \App\Support\Money::format($stats->total_original ?? 0) }} gross · {{ \App\Support\Money::format($stats->total_discounts ?? 0) }} discounts</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--emerald">
                    <div>
                        <div class="fa-metric-label">Collected (KES)</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_collected ?? 0) }}</div>
                        <div class="fa-sub-line">{{ $collectionRate }}% collection rate</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--rose">
                    <div>
                        <div class="fa-metric-label">Outstanding (KES)</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_pending ?? 0) }}</div>
                        <div class="fa-sub-line">{{ \App\Support\Money::whole($stats->students_billed ?? 0) }} students billed</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-balance-scale"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--amber">
                    <div>
                        <div class="fa-metric-label">Payment Status</div>
                        <div class="fa-metric-value">{{ \App\Support\Money::whole($stats->paid_count ?? 0) }} <span style="font-size: 0.9rem; font-weight: 600; color: var(--fa-slate-400);">paid</span></div>
                        <div class="fa-sub-line">{{ \App\Support\Money::whole($stats->partial_count ?? 0) }} partial · {{ \App\Support\Money::whole($stats->unpaid_count ?? 0) }} unpaid</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-chart-bar"></i></div>
                </div>
            </div>
        </div>

        {{-- Payment status bar --}}
        @if($totalAssignments > 0)
        <div class="fa-statusbar mb-4" role="img" aria-label="Payment status: {{ $stats->paid_count }} paid, {{ $stats->partial_count }} partial, {{ $stats->unpaid_count }} unpaid">
            <div class="fa-statusseg fa-statusseg--paid" style="width: {{ ($stats->paid_count / $totalAssignments) * 100 }}%;">
                <span class="fa-statuscount">{{ \App\Support\Money::whole($stats->paid_count) }}</span><span class="fa-statuslabel">Paid</span>
            </div>
            <div class="fa-statusseg fa-statusseg--partial" style="width: {{ ($stats->partial_count / $totalAssignments) * 100 }}%;">
                <span class="fa-statuscount">{{ \App\Support\Money::whole($stats->partial_count) }}</span><span class="fa-statuslabel">Partial</span>
            </div>
            <div class="fa-statusseg fa-statusseg--unpaid" style="width: {{ ($stats->unpaid_count / $totalAssignments) * 100 }}%;">
                <span class="fa-statuscount">{{ \App\Support\Money::whole($stats->unpaid_count) }}</span><span class="fa-statuslabel">Unpaid</span>
            </div>
        </div>
        @endif

        {{-- Main table card --}}
        <div class="card fa-panel">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark"><i class="fas {{ $detailMode ? 'fa-list-ul' : 'fa-school' }}"></i></span>
                        {{ $detailMode ? 'Assignment Lines' : 'Revenue by Class' }}
                        @if(!$detailMode)
                            <span style="font-weight: 500; color: var(--fa-slate-400); font-size: 0.8125rem;">
                                · {{ \App\Support\Money::whole($rollups->total()) }} class{{ $rollups->total() === 1 ? '' : 'es' }}
                            </span>
                        @endif
                    </h3>
                    <form action="{{ route('fees.reports.expected-revenue') }}" method="GET" class="fa-filters">
                        @if($detailMode)
                            <input type="hidden" name="detail" value="1">
                            @if(request()->filled('class_id'))<input type="hidden" name="class_id" value="{{ request('class_id') }}">@endif
                        @endif
                        <select name="class_id" class="form-control form-control-sm fa-filter-select" aria-label="Filter by class">
                            <option value="">All Classes</option>
                            @foreach($classes as $id => $name)
                                <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <select name="academic_year_id" class="form-control form-control-sm fa-filter-select" aria-label="Filter by academic year">
                            @foreach($academicYears as $id => $name)
                                <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-filter mr-1" style="font-size: 0.75rem;"></i> Filter
                        </button>
                        @if(request()->filled('class_id'))
                            <a href="{{ route('fees.reports.expected-revenue', ['academic_year_id' => $yearId]) }}" class="btn btn-sm btn-link" style="color: var(--fa-slate-500); text-decoration: none; font-weight: 600;">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($detailMode)
                <div class="fa-detailbar">
                    <div class="fa-detailbar-text">
                        Students billed in <strong>{{ $groupClass->name ?? 'All Classes' }}</strong>
                        <span style="font-weight: 500; color: var(--fa-slate-500);">— {{ \App\Models\AcademicYear::find($yearId)->name ?? 'selected year' }}</span>
                    </div>
                    <a href="{{ route('fees.reports.expected-revenue', array_diff_key($qs, ['class_id' => ''])) }}"
                       class="btn btn-sm" style="border-radius: 8px; font-weight: 600; background: #fff; color: var(--fa-indigo-600); border: 1px solid var(--fa-indigo-100);">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.7rem;"></i> Back to summary
                    </a>
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    @if(!$detailMode)
                    {{-- ── Roll-up: one row per class ── --}}
                    <table class="table fa-table mb-0" style="min-width: 760px;">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th class="text-center">Students</th>
                                <th class="fa-table-amount">Expected</th>
                                <th class="fa-table-amount">Collected</th>
                                <th class="fa-table-amount">Balance</th>
                                <th>Collection</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rollups as $row)
                                @php
                                    $collectionPct = $row->total_expected > 0 ? min(100, round(($row->total_collected / $row->total_expected) * 100)) : 0;
                                    $detailUrl = route('fees.reports.expected-revenue', array_merge($qs, [
                                        'detail' => 1,
                                        'class_id' => $row->class_id,
                                    ]));
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location='{{ $detailUrl }}'">
                                    <td><div class="fa-main-line">{{ $row->class_name }}</div></td>
                                    <td class="text-center">
                                         <span style="font-weight: 700; color: var(--fa-slate-900); font-variant-numeric: tabular-nums;">{{ \App\Support\Money::whole($row->student_count) }}</span>
                                         <div class="fa-sub-line">{{ \App\Support\Money::whole($row->fully_paid_students) }} fully paid</div>
                                     </td>
                                     <td class="fa-table-amount">{{ \App\Support\Money::format($row->total_expected) }}</td>
                                     <td class="fa-table-amount" style="color: var(--fa-emerald-600); font-weight: 600;">{{ \App\Support\Money::format($row->total_collected) }}</td>
                                     <td class="fa-table-amount">
                                         @if($row->total_balance > 0)
                                             <span class="amount-negative">{{ \App\Support\Money::format($row->total_balance) }}</span>
                                        @else
                                            <span class="value-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fa-progress-label">{{ $collectionPct }}%</div>
                                        <div class="fa-progress"><span style="width: {{ $collectionPct }}%;"></span></div>
                                    </td>
                                    <td class="fa-drill" onclick="event.stopPropagation();">
                                        <div class="fa-actions">
                                            <a href="{{ $detailUrl }}" class="fa-action fa-action--view" title="View students" aria-label="View students for {{ $row->class_name }}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-chart-bar" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No billed assignments found</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Try a different academic year.</p>
                                            <a href="{{ route('fees.reports.expected-revenue') }}" class="btn btn-primary">
                                                <i class="fas fa-times mr-1"></i> Reset Filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    {{-- ── Detail: per-student lines for one class ── --}}
                    <table class="table fa-table mb-0" style="min-width: 820px;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Fee</th>
                                <th>Term / Year</th>
                                <th class="fa-table-amount">Amount</th>
                                <th class="fa-table-amount">Paid</th>
                                <th class="fa-table-amount">Balance</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $a)
                                @php
                                    $balance = (float) $a->final_amount - (float) $a->paid_amount;
                                    $classInfo = $a->student->studentClassEnrollments->first();
                                    $className = $classInfo ? ($classInfo->classSection->schoolClass->name ?? '-') : '-';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fa-main-line">{{ $a->student->first_name ?? '' }} {{ $a->student->last_name ?? '' }}</div>
                                        <div class="fa-sub-line">{{ $a->student->admission_no ?? '' }} · {{ $className }}</div>
                                    </td>
                                    <td class="value-muted">{{ $a->feeStructure->category->name ?? '-' }}</td>
                                    <td>
                                        <div class="fa-main-line">{{ $a->termModel->name ?? $a->term }}</div>
                                        <div class="fa-sub-line">{{ $a->academicYear->name ?? '-' }}</div>
                                    </td>
                                     <td class="fa-table-amount">{{ \App\Support\Money::format($a->amount) }}</td>
                                     <td class="fa-table-amount">
                                         @if($a->paid_amount > 0)
                                             <span style="color: var(--fa-emerald-600); font-weight: 600;">{{ \App\Support\Money::format($a->paid_amount) }}</span>
                                         @else
                                             <span class="value-muted">0.00</span>
                                         @endif
                                     </td>
                                     <td class="fa-table-amount">
                                         @if($balance > 0)
                                             <span class="amount-negative">{{ \App\Support\Money::format($balance) }}</span>
                                        @else
                                            <span class="value-muted">0.00</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($a->payment_status === 'paid')
                                            <span class="fa-pill fa-pill--paid">Paid</span>
                                        @elseif($a->payment_status === 'partial')
                                            <span class="fa-pill fa-pill--partial">Partial</span>
                                        @else
                                            <span class="fa-pill fa-pill--unpaid">Unpaid</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-folder-open" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No assignments in this view</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Try widening the filters, or go back to the summary.</p>
                                            <a href="{{ route('fees.reports.expected-revenue', array_diff_key($qs, ['class_id' => ''])) }}" class="btn btn-primary">
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

            @php $paginator = $detailMode ? $assignments : $rollups; @endphp
            @if($paginator->hasPages())
                <div class="card-footer bg-white" style="border-top: 1px solid var(--fa-slate-100); padding: 0.875rem 1.375rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div style="color: var(--fa-slate-500); font-size: 0.8125rem;">
                            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ \App\Support\Money::whole($paginator->total()) }}
                            {{ $detailMode ? 'assignments' : 'classes' }}
                        </div>
                        <div>
                            {{ $paginator->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Revenue by fee category (secondary panel, summary mode only) --}}
        @if(!$detailMode && $categories->count())
        <div class="card fa-panel mt-4">
            <div class="card-header">
                <h3 class="fa-panel-title">
                    <span class="fa-panel-mark"><i class="fas fa-tags"></i></span>
                    Revenue by Fee Category
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table fa-table mb-0" style="min-width: 560px;">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Type</th>
                                <th class="text-center">Assignments</th>
                                <th class="fa-table-amount">Expected</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categories as $row)
                                <tr>
                                    <td><div class="fa-main-line">{{ $row->category_name }}</div></td>
                                    <td><span class="fa-chip">{{ ucfirst($row->category_type) }}</span></td>
                                        <td class="text-center" style="font-variant-numeric: tabular-nums;">{{ \App\Support\Money::whole($row->assignment_count) }}</td>
                                        <td class="fa-table-amount">{{ \App\Support\Money::format($row->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
(function () {
    var form = document.querySelector('.fa-filters');
    if (form) {
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { form.submit(); });
        });
    }
})();
</script>
@endsection
