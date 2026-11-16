@extends('layouts.app')

@section('content')
<style>
    .fa-assign {
        /* Design tokens — match sidebar-fixed-final.css / DESIGN.md */
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
    .fa-head-sub {
        color: var(--fa-slate-500);
        font-size: 0.813rem;
        margin-top: 0.125rem;
    }

    /* ── Metric cards — clean, full border, indigo flyout (DESIGN.md) ── */
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
        opacity: 0;
        transform: translateY(8px);
        animation: faMetricIn 0.32s var(--fa-ease-out) forwards;
    }
    @keyframes faMetricIn {
        to { opacity: 1; transform: translateY(0); }
    }
    .fa-metric:nth-child(1) { animation-delay: 0ms; }
    .fa-metric:nth-child(2) { animation-delay: 45ms; }
    .fa-metric:nth-child(3) { animation-delay: 90ms; }
    .fa-metric:nth-child(4) { animation-delay: 135ms; }
    .fa-metric:nth-child(5) { animation-delay: 180ms; }

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
        color: var(--fa-slate-900);
        line-height: 1.1;
        margin-top: 0.25rem;
        font-variant-numeric: tabular-nums;
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
    .fa-metric-icon i { color: inherit; }
    .fa-metric--indigo .fa-metric-icon       { background: var(--fa-indigo-50);  color: var(--fa-indigo-600); }
    .fa-metric--indigo .fa-metric-value      { color: var(--fa-indigo-600); }
    .fa-metric--emerald .fa-metric-icon      { background: var(--fa-emerald-50); color: var(--fa-emerald-600); }
    .fa-metric--emerald .fa-metric-value     { color: var(--fa-emerald-600); }
    .fa-metric--amber .fa-metric-icon        { background: oklch(0.950 0.045 95); color: var(--fa-amber-600); }
    .fa-metric--amber .fa-metric-value       { color: var(--fa-amber-600); }
    .fa-metric--violet .fa-metric-icon       { background: oklch(0.960 0.022 302); color: oklch(0.606 0.180 278); }
    .fa-metric--violet .fa-metric-value      { color: oklch(0.606 0.180 278); }
    .fa-metric--rose .fa-metric-icon         { background: var(--fa-rose-50); color: var(--fa-rose-600); }
    .fa-metric--rose .fa-metric-value        { color: var(--fa-rose-600); }
    .fa-metric:active { transform: scale(0.99); transition: transform 0.12s var(--fa-ease-out); }

    /* ── Card shell (let global .card tokens win) ── */
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

    /* ── Filters ──
       The global stylesheet forces select { padding: .5625rem .875rem !important }
       while form-control-sm caps height at calc(1.5em + .5rem + 2px) ≈ 31px —
       the text overflowed and looked half-clipped. Give the filter controls a
       real height budget and stop inheriting the forced padding. */
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
    .fa-filter-select { width: 170px; }
    .fa-filter-select--sm { width: 140px; }

    /* ── Mode banner (detail view) ── */
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
    .fa-detailbar-text .fa-detailbar-sub { font-weight: 500; color: var(--fa-slate-500); }

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
    .fa-table thead th:first-child { border-top-left-radius: 0; padding-left: 1.375rem; }
    .fa-table tbody td {
        padding: 0.8125rem 1rem;
        border-color: var(--fa-slate-100);
        vertical-align: middle;
    }
    .fa-table tbody td:first-child { padding-left: 1.375rem; }
    .fa-table tbody tr {
        transition: background-color 0.15s var(--fa-ease-out);
    }
    @media (hover: hover) and (pointer: fine) {
        .fa-table tbody tr:hover { background: oklch(0.970 0.003 264); }
    }
    .fa-table .amount,
    .fa-table-amount { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; font-family: var(--fa-mono); }
    .min-width-0 { min-width: 0; }
    .fa-table .amount-negative { color: var(--fa-rose-600); }
    .fa-table .amount-net { font-weight: 700; color: var(--fa-emerald-600); }
    .fa-table .value-muted { color: var(--fa-slate-400); }

    /* Group row main/sub lines */
    .fa-main-line { color: var(--fa-slate-900); font-weight: 600; font-size: 0.875rem; }
    .fa-sub-line { font-size: 0.75rem; color: var(--fa-slate-500); }

    /* Student cell (detail mode) */
    .fa-avatar {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: var(--fa-indigo-100);
        color: var(--fa-indigo-600);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        flex-shrink: 0;
        user-select: none;
    }
    .fa-student-name { font-weight: 600; color: var(--fa-slate-900); font-size: 0.9rem; line-height: 1.3; }
    .fa-student-adm { font-size: 0.75rem; color: var(--fa-slate-500); }

    /* Class chip + category */
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

    /* Status pills */
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
    .fa-pill--active {
        background: var(--fa-emerald-50);
        border: 1px solid oklch(0.889 0.048 163);
        color: var(--fa-emerald-600);
    }
    .fa-pill--active::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--fa-emerald-500);
    }
    .fa-pill--inactive {
        background: var(--fa-slate-100);
        border: 1px solid var(--fa-slate-200);
        color: var(--fa-slate-500);
    }

    /* Collection progress on rollup rows */
    .fa-progress {
        width: 120px;
        height: 5px;
        border-radius: 999px;
        background: var(--fa-slate-200);
        overflow: hidden;
        margin-top: 0.375rem;
    }
    .fa-progress > span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: var(--fa-emerald-500);
    }
    .fa-progress-label { font-size: 0.72rem; color: var(--fa-slate-500); font-weight: 600; font-variant-numeric: tabular-nums; }

    /* ── Row actions — ghost buttons, tint on hover ── */
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
        transition: transform 0.16s var(--fa-ease-out),
                    background-color 0.16s var(--fa-ease-out),
                    color 0.16s var(--fa-ease-out);
    }
    .fa-action:active { transform: scale(0.96); }
    .fa-action:focus-visible {
        outline: 2px solid var(--fa-indigo-600);
        outline-offset: 1px;
    }
    @media (hover: hover) and (pointer: fine) {
        .fa-action--view:hover { background: var(--fa-slate-200); color: var(--fa-slate-900); }
        .fa-action--delete:hover { background: var(--fa-rose-50); color: var(--fa-rose-600); }
    }
    .fa-drill {
        color: var(--fa-slate-400);
        flex: 0 0 auto;
        text-align: center;
    }

    /* ── Empty state ── */
    .fa-empty { padding: 4rem 1rem; text-align: center; color: var(--fa-slate-500); }

    /* ── Confirm modal ── */
    .fa-confirm-modal .modal-content {
        border: 1px solid var(--fa-slate-200);
        border-radius: 14px;
        box-shadow: 0 24px 48px oklch(0 0 0 / 0.18);
    }
    .fa-confirm-modal .modal-header { border-bottom: none; padding: 1.375rem 1.375rem 0; }
    .fa-confirm-modal .modal-body { padding: 0.5rem 1.375rem 1.25rem; }
    .fa-confirm-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: var(--fa-rose-50);
        color: var(--fa-rose-600);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
    }
    .fa-confirm-title { font-weight: 800; font-size: 1.05rem; color: var(--fa-slate-900); }
    .fa-confirm-desc { font-size: 0.8125rem; color: var(--fa-slate-500); margin: 0; line-height: 1.5; }
    .fa-confirm-modal .modal-footer {
        border-top: 1px solid var(--fa-slate-100);
        padding: 0.875rem 1.375rem;
    }
    .fa-btn-danger {
        background: var(--fa-rose-600);
        border: none;
        border-radius: 8px;
        color: #fff;
        font-weight: 600;
        padding: 0.4375rem 1rem;
        transition: transform 0.16s var(--fa-ease-out),
                    background-color 0.16s var(--fa-ease-out);
    }
    .fa-btn-danger:active { transform: scale(0.97); }
    @media (hover: hover) and (pointer: fine) {
        .fa-btn-danger:hover { background: oklch(0.505 0.192 22); }
    }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .fa-head .d-flex { flex-direction: column; align-items: stretch !important; gap: 0.625rem; }
        .fa-head .btn { justify-content: center; }
        .fa-filters { width: 100%; }
        .fa-filter-select, .fa-search { width: 100%; }
        .fa-table thead th:nth-child(n+4),
        .fa-table tbody td:nth-child(n+4) { display: none; }
        .fa-table thead th:first-child, .fa-table tbody td:first-child { padding-left: 0.875rem; }
    }

    @media (prefers-reduced-motion: reduce) {
        .fa-metric {
            animation: none;
            opacity: 1;
            transform: none;
        }
        .fa-action,
        .fa-btn-danger,
        .fa-metric:active { transition: none; }
    }
</style>

@php
    $qs = array_diff_key(request()->query(), array_flip(['page', 'detail']));
@endphp

<div class="fa-assign">
    <section class="content-header">
        <div class="container-fluid px-0">
            <div class="fa-head d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h1>Fee Assignments</h1>
                    <p class="fa-head-sub">Billing roll-up per fee, class and term — click a row to see its students</p>
                </div>
                <div class="d-flex gap-2">
                    <a class="btn btn-outline-secondary" href="{{ route('fees.assignments.unassigned') }}" style="border-radius: 8px; font-weight: 600;">
                        <i class="fas fa-user-slash mr-1" style="font-size: 0.8rem;"></i> Unassigned
                    </a>
                    <a class="btn btn-primary" href="{{ route('fees.assignments.create') }}">
                        <i class="fas fa-plus mr-1"></i> Assign Fees
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-0">
        @include('flash::message')

        {{-- Metric cards — computed across the whole filtered set --}}
        <div class="row mb-4">
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--indigo">
                    <div>
                        <div class="fa-metric-label">Students Billed</div>
                        <div class="fa-metric-value">{{ number_format($stats->students_billed ?? 0) }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-user-graduate"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--violet">
                    <div>
                        <div class="fa-metric-label">Billed</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_net ?? 0) }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--emerald">
                    <div>
                        <div class="fa-metric-label">Collected</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format($stats->total_collected ?? 0) }}</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-hand-holding-usd"></i></div>
                </div>
            </div>
            <div class="col-lg-3 col-6 mb-3">
                <div class="fa-metric fa-metric--rose">
                    <div>
                        <div class="fa-metric-label">Outstanding</div>
                        <div class="fa-metric-value fa-metric-value--mono">{{ \App\Support\Money::format(($stats->total_net ?? 0) - ($stats->total_collected ?? 0)) }}</div>
                        <div class="fa-sub-line">{{ number_format($stats->students_owing ?? 0) }} students owing</div>
                    </div>
                    <div class="fa-metric-icon"><i class="fas fa-balance-scale"></i></div>
                </div>
            </div>
        </div>

        {{-- Main table card --}}
        <div class="card fa-panel">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.75rem;">
                    <h3 class="fa-panel-title">
                        <span class="fa-panel-mark"><i class="fas {{ $detailMode ? 'fa-list-ul' : 'fa-layer-group' }}"></i></span>
                        {{ $detailMode ? 'Assignment Lines' : 'Fee Assignment Summary' }}
                        @if(!$detailMode)
                            <span style="font-weight: 500; color: var(--fa-slate-400); font-size: 0.8125rem;">
                                · {{ number_format($rollups->total()) }} group{{ $rollups->total() === 1 ? '' : 's' }}
                            </span>
                        @endif
                    </h3>
                    <form action="{{ route('fees.assignments.index') }}" method="GET" class="fa-filters">
                        @if($detailMode)
                            <input type="hidden" name="detail" value="1">
                            @foreach(['fee_structure_id', 'academic_year_id', 'term', 'student_name'] as $keep)
                                @if(request()->filled($keep))<input type="hidden" name="{{ $keep }}" value="{{ request($keep) }}">@endif
                            @endforeach
                        @endif
                        <select name="class_id" class="form-control form-control-sm fa-filter-select" aria-label="Filter by class">
                            <option value="">All Classes</option>
                            @foreach($classes as $id => $name)
                                <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <select name="academic_year_id" id="faYearFilter" class="form-control form-control-sm fa-filter-select" aria-label="Filter by academic year">
                            <option value="">All Years</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->academic_year_id }}" {{ request('academic_year_id') == $year->academic_year_id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                        <select name="term" id="faTermFilter" class="form-control form-control-sm fa-filter-select--sm fa-filter-select" aria-label="Filter by term">
                            <option value="">All Terms</option>
                            @foreach($terms as $termOption)
                                <option value="{{ $termOption->code }}" data-year="{{ $termOption->academic_year_id }}" {{ request('term') === $termOption->code ? 'selected' : '' }}>{{ $termOption->name }}</option>
                            @endforeach
                        </select>
                        @if($detailMode)
                            <select name="status" class="form-control form-control-sm fa-filter-select--sm fa-filter-select" aria-label="Filter by status">
                                <option value="all" {{ !request('status') || request('status') === 'all' ? 'selected' : '' }}>All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        @endif
                        <div class="fa-search">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <input type="text" name="student_name" class="form-control form-control-sm" style="width: 200px;"
                                   placeholder="Search student / adm no..." value="{{ request('student_name') }}" aria-label="Search by student name">
                        </div>
                        <button class="btn btn-primary btn-sm" type="submit" style="height: 38px; border-radius: 8px; font-weight: 600;">
                            <i class="fas fa-filter mr-1" style="font-size: 0.75rem;"></i> Filter
                        </button>
                        @if(collect(['class_id', 'academic_year_id', 'term', 'student_name', 'status', 'fee_structure_id'])->contains(fn($k) => request()->filled($k)))
                            <a href="{{ route('fees.assignments.index') }}" class="btn btn-sm btn-link" style="color: var(--fa-slate-500); text-decoration: none; font-weight: 600;">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            @if($detailMode)
                {{-- ── Detail mode: per-student rows for one group, or a name search ── --}}
                <div class="fa-detailbar">
                    <div class="fa-detailbar-text">
                        @if(!empty($groupFee))
                            Students billed under <strong>{{ $groupFee->category->name ?? 'fee' }}</strong>
                            <span class="fa-detailbar-sub">— {{ $groupFee->schoolClass->name ?? 'All Classes' }}</span>
                        @else
                            <i class="fas fa-search mr-1"></i>Student search results
                        @endif
                    </div>
                    <a href="{{ route('fees.assignments.index', array_diff_key($qs, ['student_name' => '', 'fee_structure_id' => ''])) }}"
                       class="btn btn-sm" style="border-radius: 8px; font-weight: 600; background: #fff; color: var(--fa-indigo-600); border: 1px solid var(--fa-indigo-100);">
                        <i class="fas fa-arrow-left mr-1" style="font-size: 0.7rem;"></i> Back to summary
                    </a>
                </div>
            @endif

            <div class="card-body p-0">
                <div class="table-responsive">
                    @if(!$detailMode)
                    {{-- ── Roll-up view: one row per fee + class + term + year ── --}}
                    <table class="table fa-table mb-0" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th>Fee</th>
                                <th>Class</th>
                                <th>Term / Year</th>
                                <th class="text-center">Students</th>
                                <th class="fa-table-amount">Billed</th>
                                <th class="fa-table-amount">Collected</th>
                                <th class="fa-table-amount">Balance</th>
                                <th>Collection</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rollups as $row)
                                @php
                                    $collectionPct = $row->total_net > 0 ? min(100, round(($row->total_collected / $row->total_net) * 100)) : 0;
                                    $detailUrl = route('fees.assignments.index', array_merge($qs, [
                                        'detail' => 1,
                                        'fee_structure_id' => $row->fee_structure_id,
                                        'academic_year_id' => $row->academic_year_id,
                                        'term' => $row->term,
                                        'class_id' => $row->class_id ?? request('class_id'),
                                    ]));
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location='{{ $detailUrl }}'">
                                    <td>
                                        <div class="fa-main-line">{{ $row->category_name }}</div>
                                        <div class="fa-sub-line">{{ ucfirst(str_replace('_', ' ', $row->payment_frequency ?? '')) }}</div>
                                    </td>
                                    <td>
                                        <span class="fa-chip">{{ $row->class_name }}</span>
                                    </td>
                                    <td>
                                        <div class="fa-main-line">{{ $row->term_name }}</div>
                                        <div class="fa-sub-line">{{ $row->year_name }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span style="font-weight: 700; color: var(--fa-slate-900); font-variant-numeric: tabular-nums;">{{ number_format($row->student_count) }}</span>
                                        <div class="fa-sub-line">{{ number_format($row->fully_paid_students) }} fully paid</div>
                                    </td>
                                    <td class="amount">{{ \App\Support\Money::format($row->total_net) }}</td>
                                    <td class="amount" style="color: var(--fa-emerald-600); font-weight: 600;">{{ \App\Support\Money::format($row->total_collected) }}</td>
                                    <td class="amount">
                                        @if($row->total_balance > 0)
                                            <span class="amount-negative">{{ \App\Support\Money::format($row->total_balance) }}</span>
                                        @else
                                            <span class="value-muted">{{ \App\Support\Money::format(0) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fa-progress-label">{{ $collectionPct }}%</div>
                                        <div class="fa-progress"><span style="width: {{ $collectionPct }}%;"></span></div>
                                    </td>
                                    <td class="fa-drill" onclick="event.stopPropagation();">
                                        <div class="fa-actions">
                                            <a href="{{ $detailUrl }}" class="fa-action fa-action--view" title="View assigned students" aria-label="View assigned students for {{ $row->category_name }}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-folder-open" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No fee assignments found</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Adjust your filters or create a new assignment.</p>
                                            <a href="{{ route('fees.assignments.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus mr-1"></i> Create Your First Assignment
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @else
                    {{-- ── Detail view: the individual assignment lines ── --}}
                    <table class="table fa-table mb-0" style="min-width: 860px;">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Category</th>
                                <th>Term / Year</th>
                                <th class="fa-table-amount">Amount</th>
                                <th class="fa-table-amount">Paid</th>
                                <th class="fa-table-amount">Net</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center" style="gap: 0.75rem;">
                                            <div class="fa-avatar">
                                                {{ strtoupper(substr($assignment->student->first_name ?? 'U', 0, 1) . substr($assignment->student->last_name ?? '', 0, 1)) }}
                                            </div>
                                            <div class="min-width-0">
                                                <div class="fa-student-name">{{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}</div>
                                                <div class="fa-student-adm">{{ $assignment->student->admission_no ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fa-chip">
                                            {{ $assignment->student->current_enrollment->classSection->schoolClass->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="value-muted">{{ $assignment->feeStructure->category->name ?? '-' }}</td>
                                    <td>
                                        <div class="fa-main-line">{{ $assignment->termModel->name ?? $assignment->term }}</div>
                                        <div class="fa-sub-line">{{ $assignment->academicYear->name ?? '-' }}</div>
                                    </td>
                                    <td class="amount">{{ number_format($assignment->amount, 2) }}</td>
                                    <td class="amount">
                                        @if($assignment->paid_amount > 0)
                                            <span style="color: var(--fa-emerald-600); font-weight: 600;">{{ number_format($assignment->paid_amount, 2) }}</span>
                                        @else
                                            <span class="value-muted">0.00</span>
                                        @endif
                                    </td>
                                    <td class="amount amount-net">{{ number_format($assignment->final_amount, 2) }}</td>
                                    <td>
                                        @if($assignment->status == 'active')
                                            <span class="fa-pill fa-pill--active">Active</span>
                                        @else
                                            <span class="fa-pill fa-pill--inactive">{{ ucfirst($assignment->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="fa-actions">
                                            <a href="{{ route('fees.assignments.student-summary', $assignment->student_id) }}"
                                               class="fa-action fa-action--view"
                                               title="View Summary" aria-label="View summary for {{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}">
                                                <i class="far fa-eye"></i>
                                            </a>
                                            <button type="button"
                                                    class="fa-action fa-action--delete"
                                                    data-delete-url="{{ route('fees.assignments.destroy', $assignment->id) }}"
                                                    data-student-name="{{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}"
                                                    data-amount="{{ number_format($assignment->final_amount, 2) }}"
                                                    title="Remove" aria-label="Remove assignment for {{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9">
                                        <div class="fa-empty">
                                            <div style="width: 64px; height: 64px; background: var(--fa-slate-100); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                                <i class="fas fa-folder-open" style="font-size: 26px; color: var(--fa-slate-400);"></i>
                                            </div>
                                            <p style="font-size: 0.9375rem; font-weight: 600; color: var(--fa-slate-700); margin-bottom: 0.25rem;">No assignments in this group</p>
                                            <p style="font-size: 0.8125rem; margin-bottom: 1rem;">Try widening the filters, or go back to the summary.</p>
                                            <a href="{{ route('fees.assignments.index') }}" class="btn btn-primary">
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
                            Showing {{ $paginator->firstItem() ?? 0 }} to {{ $paginator->lastItem() ?? 0 }} of {{ number_format($paginator->total()) }}
                            {{ $detailMode ? 'assignments' : 'fee groups' }}
                        </div>
                        <div>
                            {{ $paginator->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Delete confirmation (detail mode) --}}
    <div class="modal fade fa-confirm-modal" id="faDeleteModal" tabindex="-1" role="dialog" aria-labelledby="faDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <form id="faDeleteForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <div class="modal-header">
                        <div class="fa-confirm-icon"><i class="far fa-trash-alt"></i></div>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h5 class="fa-confirm-title">Remove fee assignment?</h5>
                        <p class="fa-confirm-desc">
                            This removes <span id="faDeleteStudent" style="font-weight: 600; color: var(--fa-slate-700);"></span> for
                            <span id="faDeleteAmount" class="fa-metric-value--mono" style="font-weight: 700; color: var(--fa-rose-600);"></span>.
                            This action cannot be undone.
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal" style="border-radius: 8px; font-weight: 600; color: var(--fa-slate-700);">Cancel</button>
                        <button type="submit" class="fa-btn-danger"><i class="fas fa-trash-alt mr-1" style="font-size: 0.8rem;"></i> Remove</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    // Filter selects are static form controls; submit the filter form the
    // moment one changes so there is no dead "Filter" click needed.
    var form = document.querySelector('.fa-filters');
    if (form) {
        form.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { form.submit(); });
        });
    }

    // Narrow the term list to the selected academic year (terms belong to a year).
    var yearSel = document.getElementById('faYearFilter');
    var termSel = document.getElementById('faTermFilter');
    function syncTerms() {
        if (!yearSel || !termSel) return;
        var year = yearSel.value;
        var keep = termSel.value;
        Array.prototype.forEach.call(termSel.options, function (opt) {
            if (!opt.dataset.year) return;
            opt.hidden = !!year && opt.dataset.year !== year;
        });
        if (termSel.selectedOptions.length && termSel.selectedOptions[0].hidden) {
            termSel.value = '';
        }
    }
    if (yearSel) yearSel.addEventListener('change', syncTerms);
    syncTerms();

    var modal = document.getElementById('faDeleteModal');
    var deleteForm = document.getElementById('faDeleteForm');
    if (!modal || !deleteForm) return;

    var triggers = document.querySelectorAll('[data-delete-url]');
    var studentLabel = document.getElementById('faDeleteStudent');
    var amountLabel = document.getElementById('faDeleteAmount');

    triggers.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            deleteForm.action = btn.getAttribute('data-delete-url');
            if (studentLabel) studentLabel.textContent = btn.getAttribute('data-student-name') || 'this student';
            if (amountLabel) amountLabel.textContent = 'KES ' + Number(btn.getAttribute('data-amount') || 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            var $modal = window.jQuery && window.jQuery(modal);
            if ($modal && $modal.modal) {
                $modal.modal('show');
            } else {
                modal.classList.add('show');
                modal.style.display = 'block';
            }
        });
    });
})();
</script>
@endsection
