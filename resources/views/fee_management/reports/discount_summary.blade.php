@extends('layouts.app')

@section('content')
<style>
    .fa-ds {
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
        --fa-rose-600:   oklch(0.575 0.210 22);
        --fa-rose-500:   oklch(0.643 0.220 22);
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
    .fa-metric--amber .fa-metric-icon   { background: var(--fa-amber-50);  color: var(--fa-amber-600); }
    .fa-metric--amber .fa-metric-value  { color: var(--fa-amber-600); }
    .fa-metric--indigo .fa-metric-icon  { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); }
    .fa-metric--emerald .fa-metric-icon { background: oklch(0.979 0.021 166); color: var(--fa-emerald-600); }
    .fa-metric--rose .fa-metric-icon    { background: var(--fa-rose-50);   color: var(--fa-rose-600); }
    .fa-metric--rose .fa-metric-value   { color: var(--fa-rose-600); }
    .fa-sub-line { font-size: 0.75rem; color: var(--fa-slate-500); }

    .fa-panel .card-header { padding: 1.125rem 1.375rem; }
    .fa-panel-title { font-weight: 700; font-size: 0.95rem; color: var(--fa-slate-900); display: flex; align-items: center; gap: 0.625rem; margin: 0; }
    .fa-panel-title .fa-panel-mark {
        width: 30px; height: 30px; border-radius: 8px; background: var(--fa-amber-50); color: var(--fa-amber-600);
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
    .fa-search { position: relative; }
    .fa-search > i {
        position: absolute; left: 0.8rem; top: 50%; transform: translateY(-50%);
        font-size: 0.8rem; color: var(--fa-slate-400); pointer-events: none; z-index: 2;
    }
    .fa-search input.form-control,
    .fa-filters .fa-search input.form-control {
        padding-left: 2.15rem !important;
    }
    .fa-filter-select { width: 190px; }

    .fa-detailbar {
        display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;
        background: var(--fa-amber-50); border-bottom: 1px solid oklch(0.900 0.065 70 / 0.5); padding: 0.75rem 1.375rem;
    }
    .fa-detailbar-text { font-size: 0.8125rem; color: var(--fa-amber-600); font-weight: 600; }

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

    .fa-chip { display: inline-flex; align-items: center; padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.72rem; font-weight: 600; background: var(--fa-amber-50); color: var(--fa-amber-600); white-space: nowrap; }
    .fa-chip--indigo { background: var(--fa-indigo-50); color: var(--fa-indigo-500); }

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
        .fa-filter-select, .fa-search { width: 100%; }
        .fa-table thead th:nth-child(n+5), .fa-table tbody td:nth-child(n+5) { display: none; }
        .fa-table thead th:first-child, .fa-table tbody td:first-child { padding-left: 0.875rem; }
    }
    @media (prefers-reduced-motion: reduce) {
        .fa-action { transition: none; }
    }
</style>

@php
    $qs = array_diff_key(request()->query(), array_flip(['page', 'detail', 'student_name']));
    $discountRate = ($stats->total_original ?? 0) > 0 ? round((($stats->total_discounts ?? 0) / $stats->total_original) * 100, 1) : 0;
@endphp

<div class="fa-ds">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Discount Summary</h1>
                    <p class="fa-head-sub">Discounts granted per scheme — click a scheme to see its students</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('fees.dashboard') }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.8rem;"></i> Dashboard
                    </a>
                    <a class="btn btn-primary" href="{{ route('fees.reports.export.discount-summary.pdf', request()->query()) }}">
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
                <div class="fa-metric fa-metric--amber">
                    <div>
                        <div class="fa-metric-label">Total Discounts</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_discounts ?? 0) }}</div>
                        <div class="fa-sub-line">{{ $discountRate }}% of original billed</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Students with Discounts</div>
                        <div class="fa-metric-value">{{ \App\Support\Money::whole($stats->students_count ?? 0) }}</div>
                        @if(!$detailMode)
                        <div class="fa-sub-line">across {{ \App\Support\Money::whole($schemes->count()) }} scheme{{ $schemes->count() === 1 ? '' : 's' }}</div>
                        @endif
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-user-graduate"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--rose">
                    <div>
                        <div class="fa-metric-label">Original Billed</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_original ?? 0) }}</div>
                        <div class="fa-sub-line">for discounted assignments</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Net After Discounts</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_final ?? 0) }}</div>
                        <div class="fa-sub-line">what students actually owe</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-check-double"></i></div>
                </div>
            </div>
        </div>

        {{-- Main table card --}}
        <div class="card fa-panel">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark"><i class="fas {{ $detailMode ? 'fa-list-ul' : 'fa-chart-pie' }}"></i></span>
                        {{ $detailMode ? 'Discounted Assignments' : 'Discounts by Scheme' }}
                    </h3>
                    <form action="{{ route('fees.reports.discount-summary') }}" method="GET" class="fa-filters">
                        @if($detailMode)
                            <input type="hidden" name="detail" value="1">
                            @foreach(['scheme_id'] as $keep)
                                @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
                            @endforeach
                        @endif
                        <select name="academic_year_id" class="form-control form-control-sm fa-filter-select" aria-label="Filter by academic year">
                            @foreach($academicYears as $id => $name)
                                <option value="{{ $id }}" {{ $yearId == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="fa-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input type="text" name="student_name" class="form-control form-control-sm" style="width: 190px;"
                                   placeholder="Search student..." value="{{ request('student_name') }}" aria-label="Search by student name">
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-filter mr-1" style="font-size: 0.75rem;"></i> Filter
                        </button>
                        @if(request()->filled('student_name'))
                            <a href="{{ route('fees.reports.discount-summary', ['academic_year_id' => $yearId]) }}" class="btn btn-sm btn-link" style="color: var(--fa-slate-500); text-decoration: none; font-weight: 600;">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($detailMode)
                <div class="fa-detailbar">
                    <div class="fa-detailbar-text">
                        @if(!empty($groupScheme))
                            Students on <strong>{{ $groupScheme->name }}</strong>
                        @elseif(request()->filled('student_name'))
                            <i class="fas fa-search mr-1"></i>Student search results
                        @else
                            All discounted assignments
                        @endif
                    </div>
                    <a href="{{ route('fees.reports.discount-summary', array_diff_key($qs, ['scheme_id' => ''])) }}"
                       class="btn btn-sm" style="border-radius: 8px; font-weight: 600; background: #fff; color: var(--fa-amber-600); border: 1px solid oklch(0.900 0.065 70 / 0.6);">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.7rem;"></i> Back to summary
                    </a>
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    @if(!$detailMode)
                    {{-- ── Roll-up: one row per discount scheme ── --}}
                    <table class="table fa-table mb-0" style="min-width: 780px;">
                        <thead>
                            <tr>
                                <th>Scheme</th>
                                <th>Eligibility</th>
                                <th class="text-center">Students</th>
                                <th class="fa-table-amount">Original</th>
                                <th class="fa-table-amount">Discounted</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($schemes as $row)
                                @php
                                    $detailUrl = $row->scheme_id
                                        ? route('fees.reports.discount-summary', array_merge($qs, ['detail' => 1, 'scheme_id' => $row->scheme_id]))
                                        : route('fees.reports.discount-summary', array_merge($qs, ['detail' => 1]));
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location='{{ $detailUrl }}'">
                                    <td><div class="fa-main-line">{{ $row->scheme_name }}</div></td>
                                    <td>
                                        @if($row->criteria)
                                            <span class="fa-chip">{{ ucfirst(str_replace('_', ' ', $row->criteria)) }}</span>
                                        @else
                                            <span class="value-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center" style="font-weight: 700; color: var(--fa-slate-900); font-variant-numeric: tabular-nums;">{{ \App\Support\Money::whole($row->student_count) }}</td>
                                    <td class="fa-table-amount">{{ \App\Support\Money::format($row->total_original) }}</td>
                                    <td class="fa-table-amount" style="color: var(--fa-rose-600); font-weight: 600;">{{ \App\Support\Money::format(-$row->total_discount) }}</td>
                                    <td class="fa-drill" onclick="event.stopPropagation();">
                                        <div class="fa-actions">
                                            <a href="{{ $detailUrl }}" class="fa-action fa-action--view" title="View students" aria-label="View students on {{ $row->scheme_name }}">
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
                                                <i class="fas fa-percent" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No discounts recorded</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">No fee assignments carry a discount for this academic year.</p>
                                            <a href="{{ route('fees.reports.discount-summary') }}" class="btn btn-primary">
                                                <i class="fas fa-times mr-1"></i> Reset Filters
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    {{-- ── Detail: per-student lines for one scheme / search ── --}}
                    <table class="table fa-table mb-0" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Fee</th>
                                <th>Scheme</th>
                                <th class="fa-table-amount">Original</th>
                                <th class="fa-table-amount">Discount</th>
                                <th class="fa-table-amount">Net Payable</th>
                                <th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($discounts as $d)
                                @php
                                    $classInfo = $d->student->studentClassEnrollments->first();
                                    $className = $classInfo ? ($classInfo->classSection->schoolClass->name ?? '-') : '-';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fa-main-line">{{ $d->student->first_name ?? '' }} {{ $d->student->last_name ?? '' }}</div>
                                        <div class="fa-sub-line">{{ $d->student->admission_no ?? '' }} · {{ $className }}</div>
                                    </td>
                                    <td class="value-muted">{{ $d->feeStructure->category->name ?? '—' }}</td>
                                    <td>
                                        <span class="fa-chip {{ $d->discount ? '' : 'fa-chip--indigo' }}">{{ $d->discount->name ?? 'Manual' }}</span>
                                    </td>
                                    <td class="fa-table-amount">{{ \App\Support\Money::format($d->amount) }}</td>
                                    <td class="fa-table-amount amount-negative">{{ \App\Support\Money::format(-$d->discount_amount) }}</td>
                                    <td class="fa-table-amount" style="font-weight: 600; color: var(--fa-slate-900);">{{ \App\Support\Money::format($d->final_amount) }}</td>
                                    <td class="value-muted">{{ $d->assigned_date?->format('d M Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-folder-open" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No discounts in this view</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Try widening the filters, or go back to the summary.</p>
                                            <a href="{{ route('fees.reports.discount-summary', array_diff_key($qs, ['scheme_id' => ''])) }}" class="btn btn-primary">
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

            @php $paginator = $detailMode ? $discounts : null; @endphp
            @if($paginator && $paginator->hasPages())
                <div class="card-footer bg-white" style="border-top: 1px solid var(--fa-slate-100); padding: 0.875rem 1.375rem;">
                    <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                        <div style="color: var(--fa-slate-500); font-size: 0.8125rem;">
                            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ \App\Support\Money::whole($paginator->total()) }} discounted assignments
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
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { form.submit(); });
        });
    }
})();
</script>
@endsection
