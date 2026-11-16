@extends('layouts.app')

@section('content')
@php
    // Derived once here so the four metric cards cannot disagree about the tone
    // of the same rate, and so the table header can say whether the figures on
    // screen are filtered.
    $rateTone = $collectionRate >= 75 ? 'emerald' : ($collectionRate >= 50 ? 'amber' : 'rose');
    $avgOutstanding = $studentsInArrears > 0 ? $totalOutstanding / $studentsInArrears : 0;
    $hasFilters = filled($classId) || filled($termId) || filled($minAmount) || filled($search) || $allYears || $sort !== 'largest';
@endphp

<div class="report-wrap">
    {{-- Header --}}
    <div class="page-header">
        <div class="page-header-main">
            <div class="icon-box bg-rose-light text-rose">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                {{-- The scope is part of the title. A student profile and a
                     statement report the learner's all-time position while this
                     page reports one year by default, so the active scope has to
                     be readable without opening the filter. --}}
                <h1 class="page-title mb-0">Arrears &mdash; {{ $scopeLabel }}</h1>
                <p class="page-subtitle mb-0">
                    Students with outstanding balances
                    @if($allYears)
                        &middot; every academic year, all-time position
                    @else
                        &middot; within {{ $scopeLabel }} only &mdash; switch to All Years for the all-time position
                    @endif
                </p>
            </div>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('fees.dashboard') }}" class="btn-ghost-custom">
                <i class="fas fa-arrow-left me-1"></i> Dashboard
            </a>
            <a href="{{ route('fees.arrears.export-csv', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-csv me-1"></i> Export CSV
            </a>
            <a href="{{ route('fees.arrears.export-pdf', request()->query()) }}" class="btn-ghost-custom">
                <i class="fas fa-file-pdf me-1"></i> Export PDF
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="filter-bar mb-4">
        <form action="{{ route('fees.arrears.index') }}" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="academic_year_id">Academic Year</label>
                <select name="academic_year_id" id="academic_year_id" class="filter-select">
                    @foreach($academicYears as $id => $name)
                        <option value="{{ $id }}" {{ !$allYears && $yearId == $id ? 'selected' : '' }}>
                            {{ $name }}@if($currentYear && $currentYear->academic_year_id == $id) (current)@endif
                        </option>
                    @endforeach
                    <option value="all" {{ $allYears ? 'selected' : '' }}>All Years (all-time position)</option>
                </select>
            </div>
            <div class="filter-field">
                <label for="class_id">Class / Form</label>
                <select name="class_id" id="class_id" class="filter-select">
                    <option value="">All Classes</option>
                    @foreach($classes as $id => $name)
                        <option value="{{ $id }}" {{ $classId == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="term_id">Term</label>
                <select name="term_id" id="term_id" class="filter-select">
                    <option value="">All Terms</option>
                    @foreach($terms as $term)
                        <option value="{{ $term->id }}" {{ $termId == $term->id ? 'selected' : '' }}>{{ $term->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label for="min_amount">Minimum Outstanding (KES)</label>
                <input type="number" name="min_amount" id="min_amount" value="{{ $minAmount }}" class="filter-select" placeholder="e.g. 500" min="0" step="1">
            </div>
            <div class="filter-field filter-search">
                <label for="search">Search Student</label>
                {{-- Icon inside the field, same wrapper the fee dashboard uses,
                     so the two filter bars read as one control. --}}
                <div class="search-input-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="search" value="{{ $search }}" class="search-input" placeholder="Name or Admission No">
                </div>
            </div>
            <div class="filter-field">
                <label for="sort">Sort</label>
                <select name="sort" id="sort" class="filter-select">
                    <option value="largest" {{ $sort == 'largest' ? 'selected' : '' }}>Largest Balance First</option>
                    <option value="smallest" {{ $sort == 'smallest' ? 'selected' : '' }}>Smallest Balance First</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn-primary-custom">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                <a href="{{ route('fees.arrears.index') }}" class="btn-ghost-custom {{ $hasFilters ? 'is-active' : '' }}">
                    {{ $hasFilters ? 'Clear' : 'Reset' }}
                </a>
            </div>
        </form>
    </div>

    {{-- Metrics Grid.
         Label and icon share the top line, the figure gets the full card width:
         a shilling total can run to eight digits, and a metric that has to be
         shortened or wrapped is a metric nobody trusts. --}}
    <div class="metrics-grid mb-4">
        <div class="metric-card">
            <div class="metric-head">
                <span class="metric-label">Total Expected</span>
                <span class="metric-icon bg-indigo-light text-indigo"><i class="fas fa-file-invoice-dollar"></i></span>
            </div>
            <span class="metric-value">{{ \App\Support\Money::format($totalExpected) }}</span>
            <span class="metric-note">All active fees in {{ $allYears ? 'all years' : $scopeLabel }}</span>
        </div>

        <div class="metric-card">
            <div class="metric-head">
                <span class="metric-label">Total Collected</span>
                <span class="metric-icon bg-emerald-light text-emerald"><i class="fas fa-check-double"></i></span>
            </div>
            <span class="metric-value text-emerald">{{ \App\Support\Money::format($totalCollected) }}</span>
            <span class="metric-note">Credited to those fees, reversals excluded</span>
        </div>

        {{-- The one figure this page exists to report, so it is the only card
             that gets a tinted border. --}}
        <div class="metric-card {{ $totalOutstanding > 0 ? 'metric-card-flagged' : '' }}">
            <div class="metric-head">
                <span class="metric-label">Outstanding</span>
                <span class="metric-icon bg-rose-light text-rose"><i class="fas fa-hand-holding-dollar"></i></span>
            </div>
            <span class="metric-value text-rose">{{ \App\Support\Money::format($totalOutstanding) }}</span>
            <span class="metric-note">
                @if($studentsInArrears > 0)
                    Avg {{ \App\Support\Money::format($avgOutstanding) }} per student
                @else
                    Expected minus collected
                @endif
            </span>
        </div>

        <div class="metric-card">
            <div class="metric-head">
                <span class="metric-label">Collection Rate</span>
                <span class="metric-icon bg-{{ $rateTone }}-light text-{{ $rateTone }}"><i class="fas fa-coins"></i></span>
            </div>
            <span class="metric-value text-{{ $rateTone }}">{{ sprintf('%.1f', $collectionRate) }}%</span>
            {{-- A bar, because a percentage alone makes the bursar do the visual
                 subtraction. Width is the rate, colour is the same three bands
                 the icon and figure use. --}}
            <div class="rate-track" role="img" aria-label="Collection rate {{ sprintf('%.1f', $collectionRate) }} percent">
                <span class="rate-fill bg-{{ $rateTone }}" style="width: {{ max(0, min(100, $collectionRate)) }}%"></span>
            </div>
            <span class="metric-note">{{ $studentsInArrears }} {{ \Illuminate\Support\Str::plural('student', $studentsInArrears) }} in arrears</span>
        </div>
    </div>

    {{-- Arrears Table --}}
    <div class="report-card">
        <div class="card-header-custom">
            <div class="card-title-group">
                <i class="fas fa-list"></i>
                <span>Students in Arrears</span>
                @if($hasFilters)
                    <span class="tag tag-filtered">Filtered</span>
                @endif
            </div>
            <span class="card-meta">
                @if($arrears->total() > 0)
                    Showing {{ $arrears->firstItem() }}&ndash;{{ $arrears->lastItem() }} of {{ $arrears->total() }}
                @else
                    No rows
                @endif
            </span>
        </div>
        <div class="table-section">
            <table class="data-table">
                {{-- The unit lives in the header instead of on all sixty amounts,
                     so the figures stay scannable down the column. --}}
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th class="text-right">Expected (KES)</th>
                        <th class="text-right">Paid (KES)</th>
                        <th class="text-right">Outstanding (KES)</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($arrears as $student)
                        @php
                            // The rows come from a derived-table query, so there is no
                            // Student model to borrow full_name from. Same idiom as
                            // Student::getFullNameAttribute(): a student without a middle
                            // name used to render as "Brian  Kariuki".
                            $name = implode(' ', array_filter([$student->first_name, $student->middle_name, $student->last_name]));
                            $className = $student->studentClass ?? 'N/A';
                            $outstanding = $student->expected_total - $student->paid_total;
                        @endphp
                        <tr>
                            <td><span class="mono-sm text-muted">{{ $student->admission_no }}</span></td>
                            <td class="font-semibold text-strong">{{ $name }}</td>
                            <td><span class="class-badge">{{ $className }}</span></td>
                            <td class="text-right mono">{{ \App\Support\Money::number($student->expected_total) }}</td>
                            <td class="text-right mono text-emerald">{{ \App\Support\Money::number($student->paid_total) }}</td>
                            <td class="text-right mono text-rose font-semibold">{{ \App\Support\Money::number($outstanding) }}</td>
                            <td class="text-center">
                                <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn-ghost-custom btn-xs">
                                    <i class="fas fa-receipt me-1"></i> Statement
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-cell">
                                @if($hasFilters)
                                    <div class="empty-state">
                                        <span class="empty-icon bg-slate-100 text-slate-400"><i class="fas fa-sliders-h"></i></span>
                                        <p class="empty-title">No students match these filters</p>
                                        <p class="empty-body">Widen the year or class, lower the minimum outstanding, or clear the filters to see everyone in arrears.</p>
                                        <a href="{{ route('fees.arrears.index') }}" class="btn-ghost-custom">Clear filters</a>
                                    </div>
                                @else
                                    <div class="empty-state">
                                        <span class="empty-icon bg-emerald-light text-emerald"><i class="fas fa-check-circle"></i></span>
                                        <p class="empty-title">Nobody is in arrears for {{ $scopeLabel }}</p>
                                        <p class="empty-body">Every active fee in this scope is settled.</p>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="table-footer">
                <span class="table-foot-note">
                    Outstanding = expected &minus; collected, counted over each student's active fees in {{ $scopeLabel }}.
                </span>
                {{-- Named view: the bare links() default is the Tailwind paginator
                     in this Laravel version, which renders unstyled inside a
                     Bootstrap page. Every other fee screen names bootstrap-4. --}}
                {{ $arrears->appends(request()->query())->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --amber: #f59e0b; --amber-600: #d97706; --amber-light: #fffbeb;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate-50: #f8fafc; --slate-100: #f1f5f9; --slate-200: #e2e8f0;
    --slate-300: #cbd5e1; --slate-400: #94a3b8; --slate-500: #64748b;
    --slate-600: #475569; --slate-700: #334155; --slate-800: #1e293b; --slate-900: #0f172a;
    --border: #e2e8f0; --ease-out: cubic-bezier(0.23, 1, 0.32, 1);
}

/* Surfaces */
.report-wrap { padding: 1.5rem 2rem; background: var(--slate-50); min-height: 100vh; }

/* Type */
.page-title { font-size: 1.25rem; font-weight: 900; color: var(--slate-900); letter-spacing: -0.02em; }
.page-subtitle { color: var(--slate-400); font-size: 0.8rem; font-weight: 500; margin-top: 0.15rem; }
.mono { font-family: 'SF Mono', 'Cascadia Code', Consolas, monospace; font-size: 0.8rem; font-variant-numeric: tabular-nums; }
.mono-sm { font-family: 'SF Mono', 'Cascadia Code', Consolas, monospace; font-size: 0.75rem; font-weight: 600; }
.font-semibold { font-weight: 700; }
.text-muted { color: var(--slate-400); }
.text-strong { color: var(--slate-800); }
.text-emerald { color: var(--emerald); }
.text-rose { color: var(--rose); }
.text-amber { color: var(--amber-600); }
.text-indigo { color: var(--indigo); }
.text-slate-400 { color: var(--slate-400); }
.text-right { text-align: right; }
.text-center { text-align: center; }

/* Header */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
.page-header-main { display: flex; align-items: center; gap: 0.875rem; min-width: 0; }
.page-header-actions { display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; }
.icon-box { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
.bg-indigo-light { background: var(--indigo-light); }
.bg-amber-light { background: var(--amber-light); }
.bg-emerald-light { background: var(--emerald-light); }
.bg-rose-light { background: var(--rose-light); }
.bg-slate-100 { background: var(--slate-100); }
.bg-emerald { background: var(--emerald); }
.bg-amber { background: var(--amber); }
.bg-rose { background: var(--rose); }

/* Buttons */
.btn-primary-custom { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.75rem; font-weight: 800; border: none; text-decoration: none !important; cursor: pointer; background: var(--emerald); color: #fff; white-space: nowrap; transition: background 160ms var(--ease-out), box-shadow 160ms var(--ease-out); }
.btn-primary-custom:hover { background: #059669; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); }
.btn-ghost-custom { display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.5rem 1.25rem; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none !important; cursor: pointer; background: #fff; border: 1px solid var(--border); color: var(--slate-700); white-space: nowrap; transition: background 160ms var(--ease-out), border-color 160ms var(--ease-out), color 160ms var(--ease-out); }
.btn-ghost-custom:hover { background: var(--slate-100); color: var(--slate-900); }
.btn-ghost-custom.is-active { border-color: var(--indigo); color: var(--indigo); }
.btn-ghost-custom:active, .btn-primary-custom:active { transform: scale(0.97); }
.btn-primary-custom:focus-visible, .btn-ghost-custom:focus-visible, .data-table a:focus-visible { outline: 2px solid var(--indigo); outline-offset: 2px; }
.btn-xs { padding: 0.3rem 0.7rem; font-size: 0.68rem; }

/* Filter bar */
.filter-bar { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem 1.25rem; }
.filter-form { display: flex; flex-wrap: wrap; gap: 0.875rem 1rem; align-items: flex-end; }
.filter-field { display: flex; flex-direction: column; gap: 0.35rem; flex: 1 1 145px; min-width: 0; }
.filter-field label { font-size: 0.68rem; font-weight: 700; color: var(--slate-500); text-transform: uppercase; letter-spacing: 0.04em; }
.filter-search { flex: 1.8 1 210px; }
.filter-select, .search-input { width: 100%; min-width: 0; padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.8rem; color: var(--slate-700); background: #fff; }
.filter-select::placeholder, .search-input::placeholder { color: var(--slate-300); }
.filter-select:focus, .search-input:focus { outline: none; border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12); }
.search-input-wrap { position: relative; }
.search-input-wrap i { position: absolute; left: 0.7rem; top: 50%; transform: translateY(-50%); color: var(--slate-400); font-size: 0.75rem; pointer-events: none; }
.search-input { padding-left: 2.1rem; }
.filter-actions { display: flex; gap: 0.5rem; align-items: flex-end; margin-left: auto; }

/* Metrics */
.metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; }
.metric-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.3rem; }
.metric-card-flagged { border-color: rgba(244, 63, 94, 0.45); }
.metric-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
.metric-icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; flex-shrink: 0; }
.metric-label { font-size: 0.7rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; }
.metric-value { font-size: 1.15rem; font-weight: 900; color: var(--slate-900); font-family: 'SF Mono', 'Cascadia Code', Consolas, monospace; font-variant-numeric: tabular-nums; letter-spacing: -0.02em; white-space: nowrap; }
.metric-note { font-size: 0.72rem; color: var(--slate-400); font-weight: 500; }
.rate-track { height: 4px; border-radius: 999px; background: var(--slate-100); overflow: hidden; margin: 0.2rem 0 0.15rem; }
.rate-fill { display: block; height: 100%; border-radius: 999px; transition: width 240ms var(--ease-out); }

/* Card + table */
.report-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; }
.card-header-custom { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.9rem 1.25rem; border-bottom: 1px solid var(--border); background: var(--slate-50); }
.card-title-group { display: flex; align-items: center; gap: 0.5rem; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slate-500); }
.card-title-group i { color: var(--rose); }
.card-meta { font-size: 0.72rem; font-weight: 600; color: var(--slate-400); white-space: nowrap; }
.tag-filtered { padding: 2px 8px; border-radius: 6px; background: var(--indigo-light); color: var(--indigo); font-size: 0.62rem; letter-spacing: 0.04em; }
.table-section { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table thead { background: var(--slate-50); }
.data-table th { padding: 0.75rem 1rem; font-size: 0.7rem; font-weight: 800; color: var(--slate-400); text-transform: uppercase; letter-spacing: 0.05em; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
.data-table td { padding: 0.7rem 1rem; border-bottom: 1px solid var(--slate-100); vertical-align: middle; font-size: 0.82rem; color: var(--slate-700); }
.data-table tbody tr { transition: background 120ms var(--ease-out); }
.data-table tbody tr:hover { background: var(--slate-50); }
.data-table tbody tr:last-child td { border-bottom: none; }
.data-table td.text-right, .data-table th.text-right { white-space: nowrap; }
.class-badge { display: inline-block; padding: 3px 10px; border-radius: 5px; background: var(--indigo-light); color: var(--indigo); font-size: 0.7rem; font-weight: 700; white-space: nowrap; }

/* Empty states teach the screen: one says the scope is clear, the other says
   the filters are what is empty, and offers the way out. */
.empty-cell { padding: 0 !important; }
.empty-state { display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding: 2.75rem 1.5rem; text-align: center; }
.empty-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.empty-title { margin: 0; font-size: 0.88rem; font-weight: 700; color: var(--slate-700); }
.empty-body { margin: 0 0 0.25rem; font-size: 0.78rem; color: var(--slate-400); max-width: 46ch; }

/* Footer: pagination plus the definition of the outstanding column, so the
   figure on screen is never a matter of interpretation. */
.table-footer { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding: 0.9rem 1.25rem; border-top: 1px solid var(--border); }
.table-foot-note { font-size: 0.72rem; color: var(--slate-400); }
.table-footer .pagination { margin: 0; gap: 0.25rem; }
.table-footer .page-link { display: flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; font-size: 0.75rem; font-weight: 600; color: var(--slate-600); background: #fff; border: 1px solid var(--border); border-radius: 8px; }
.table-footer .page-link:hover { background: var(--slate-100); color: var(--slate-900); }
.table-footer .page-item.active .page-link { background: var(--indigo); border-color: var(--indigo); color: #fff; }
.table-footer .page-item.disabled .page-link { color: var(--slate-300); background: var(--slate-50); }

@media (max-width: 992px) {
    .page-header { flex-direction: column; align-items: stretch; }
    .page-header-actions { flex-wrap: wrap; }
    .page-header-actions .btn-ghost-custom { flex: 1 1 auto; justify-content: center; }
}

@media (max-width: 768px) {
    .report-wrap { padding: 1rem; }
    .page-title { font-size: 1.1rem; }
    .filter-form { flex-direction: column; align-items: stretch; gap: 0.625rem; }
    .filter-field { width: 100%; }
    .filter-actions { width: 100%; margin-left: 0; }
    .filter-actions .btn-primary-custom, .filter-actions .btn-ghost-custom { flex: 1; justify-content: center; }
    .metrics-grid { grid-template-columns: 1fr 1fr; gap: 0.625rem; }
    .metric-card { padding: 0.75rem 1rem; }
    .metric-icon { width: 32px; height: 32px; font-size: 0.85rem; }
    .metric-value { font-size: 1rem; }
    .data-table th:nth-child(1), .data-table td:nth-child(1),
    .data-table th:nth-child(3), .data-table td:nth-child(3) { display: none; }
    .data-table th { padding: 0.6rem 0.625rem; font-size: 0.6rem; }
    .data-table td { padding: 0.6rem 0.625rem; font-size: 0.75rem; }
    .table-footer { justify-content: center; }
    .table-foot-note { display: none; }
    .btn-xs { padding: 0.25rem 0.5rem; font-size: 0.62rem; }
}

@media (max-width: 420px) {
    .metrics-grid { grid-template-columns: 1fr; }
    .icon-box { width: 34px; height: 34px; font-size: 0.85rem; }
    .page-title { font-size: 1rem; }
}
</style>
@endsection
