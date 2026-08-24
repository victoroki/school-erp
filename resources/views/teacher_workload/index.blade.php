@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-chart-bar mr-2" style="color: var(--indigo);"></i>Teacher Workload Analytics</h1>
            <p class="dash-sub mb-0">Resource allocation and capacity monitoring across all departments</p>
        </div>
        <div class="d-flex mt-3 mt-md-0">
            <button onclick="window.print()" class="btn-dash btn-ghost mr-2">
                <i class="fas fa-file-pdf mr-1 text-rose"></i> Export PDF
            </button>
            <a href="{{ route('timetables.create') }}" class="btn-dash btn-primary-dash shadow-sm">
                <i class="fas fa-plus mr-1"></i> Assign Period
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ANALYTICS WIDGETS --}}
    @php
        $overPercent = $stats['total'] > 0 ? round(($stats['overloaded'] / $stats['total']) * 100) : 0;
        $optPercent = $stats['total'] > 0 ? round(($stats['optimum'] / $stats['total']) * 100) : 0;
    @endphp
    <div class="row mb-4">
        {{-- Total Faculty --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="dash-panel stat-card h-100">
                <div class="w-icon-box bg-blue-light text-blue"><i class="fas fa-users"></i></div>
                <h3 class="w-value">{{ sprintf('%02d', $stats['total']) }}</h3>
                <p class="w-label">Total Faculty</p>
            </div>
        </div>

        {{-- Overloaded --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="dash-panel stat-card h-100 {{ $stats['overloaded'] > 0 ? 'stat-alert' : '' }}">
                <div class="w-icon-box {{ $stats['overloaded'] > 0 ? 'bg-rose-light text-rose' : 'bg-slate-light text-slate' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="w-value {{ $stats['overloaded'] > 0 ? 'text-rose' : 'text-slate' }}">{{ sprintf('%02d', $stats['overloaded']) }}</h3>
                <p class="w-label">Overloaded
                    <span class="badge-soft {{ $stats['overloaded'] > 0 ? 'bg-rose-light text-rose' : 'bg-slate-light text-slate' }}">{{ $overPercent }}%</span>
                </p>
            </div>
        </div>

        {{-- Optimum --}}
        <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
            <div class="dash-panel stat-card h-100">
                <div class="w-icon-box bg-emerald-light text-emerald"><i class="fas fa-check-circle"></i></div>
                <h3 class="w-value text-emerald">{{ sprintf('%02d', $stats['optimum']) }}</h3>
                <p class="w-label">Optimum <span class="badge-soft bg-emerald-light text-emerald">{{ $optPercent }}%</span></p>
            </div>
        </div>

        {{-- Avg Load --}}
        <div class="col-lg-3 col-md-6">
            <div class="dash-panel stat-card h-100">
                <div class="w-icon-box bg-amber-light text-amber"><i class="fas fa-clock"></i></div>
                <h3 class="w-value text-amber">{{ round($stats['avg_hours']) }}<span class="w-unit">h</span></h3>
                <p class="w-label">Avg Weekly Load</p>
            </div>
        </div>
    </div>

    {{-- FILTER PANEL --}}
    <div class="dash-panel mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('teacher-workload.index') }}" id="filter-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4 mb-3 mb-md-0">
                        <label class="filter-label">Academic Session</label>
                        <div class="filter-field">
                            <i class="fas fa-calendar-alt"></i>
                            <select name="academic_year_id" onchange="this.form.submit()">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->academic_year_id }}" {{ $year->academic_year_id == $selectedAcademicYearId ? 'selected' : '' }}>
                                        {{ $year->name }} @if($year->is_current) - Current Session @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-5 mb-3 mb-md-0">
                        <label class="filter-label">Filter Department</label>
                        <div class="filter-field">
                            <i class="fas fa-sitemap"></i>
                            <select name="department_id" onchange="this.form.submit()">
                                @foreach($departments as $val => $text)
                                    <option value="{{ $val }}" {{ $val == $selectedDepartmentId ? 'selected' : '' }}>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        @if($selectedDepartmentId)
                            <a href="{{ route('teacher-workload.index', ['academic_year_id' => $selectedAcademicYearId]) }}" class="btn-dash btn-ghost w-100">
                                <i class="fas fa-undo mr-1"></i> Reset Filters
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- WORKLOAD TABLE --}}
    <div class="dash-panel overflow-hidden">
        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th class="pl-x">Faculty Member</th>
                        <th>Department</th>
                        <th class="text-center">Load (Periods)</th>
                        <th style="min-width: 180px;">Capacity Utilization</th>
                        <th class="text-center">Hrs/Week</th>
                        <th class="text-right pr-x">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workloadData as $data)
                        @php
                            $percent = min(($data['total_periods'] / 40) * 100, 100);
                            $status = $data['status']['label'];
                            $theme = 'slate';
                            if ($status == 'Overloaded') $theme = 'rose';
                            if ($status == 'Standard') $theme = 'emerald';
                            if ($status == 'Underloaded') $theme = 'blue';
                            $t = $data['teacher'];
                            $initials = strtoupper(mb_substr($t->first_name ?? '?', 0, 1)) . strtoupper(mb_substr($t->last_name ?? '', 0, 1));
                            $avatarColors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#f6a821', '#e9506d'];
                            $avatarColor = $avatarColors[$t->staff_id % count($avatarColors)];
                        @endphp
                        <tr>
                            <td class="pl-x">
                                <div class="d-flex align-items-center">
                                    <div class="fc-avatar" style="background: {{ $avatarColor }}">{{ $initials }}</div>
                                    <div>
                                        <div class="sub-name">{{ $t->full_name }}</div>
                                        <div class="sub-type">{{ $t->jobPosition->name ?? 'Faculty Member' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="dept-pill">{{ $t->department->name ?? 'General' }}</span></td>
                            <td class="text-center"><span class="periods-value">{{ $data['total_periods'] }}</span></td>
                            <td>
                                <div class="capacity-box">
                                    <div class="progress-track">
                                        <div class="progress-fill bg-{{ $theme }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="capacity-text">{{ round($percent) }}% Utilized</span>
                                </div>
                            </td>
                            <td class="text-center"><span class="hours-value">{{ round($data['est_hours']) }}h</span></td>
                            <td class="text-right pr-x">
                                <span class="badge-soft badge-status bg-{{ $theme }}-light text-{{ $theme }}">{{ strtoupper($status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state" style="border: none;">
                                    <div class="empty-icon"><i class="fas fa-search"></i></div>
                                    <h4 class="empty-title">No Faculty Found</h4>
                                    <p class="empty-desc">No teaching staff match the selected department.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($paginator->lastPage() > 1)
            <div class="pagination-panel m-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="pagination-info mb-2 mb-md-0">
                        Showing <strong>{{ $paginator->firstItem() }}</strong>-<strong>{{ $paginator->lastItem() }}</strong>
                        of <strong>{{ $paginator->total() }}</strong> faculty members
                    </div>
                    <div class="pagination-links">
                        {!! $paginator->appends(request()->query())->links() !!}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
        --emerald: #10b981; --emerald-light: #ecfdf5;
        --amber: #f59e0b; --amber-light: #fffbeb;
        --rose: #f43f5e; --rose-light: #fff1f2;
        --blue: #3b82f6; --blue-light: #eff6ff;
        --slate: #64748b; --slate-light: #f1f5f9;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dash-wrap { padding: 2rem 1.5rem; }
    .dash-heading { font-size: 1.65rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em; margin-bottom: 0.15rem; }
    .dash-sub { font-size: 0.9rem; color: var(--muted); font-weight: 500; }

    .dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 16px !important; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important; }

    /* Stat cards */
    .stat-card { padding: 1.35rem 1.25rem; display: flex; flex-direction: column; align-items: center; text-align: center; transition: transform 200ms var(--ease-out), box-shadow 200ms var(--ease-out); }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(15, 23, 42, 0.07) !important; }
    .stat-alert { border-color: #fecdd3 !important; background: linear-gradient(180deg, #fff 60%, var(--rose-light)); }
    .w-icon-box { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-bottom: 0.75rem; }
    .w-value { font-size: 1.45rem; font-weight: 800; margin: 0; letter-spacing: -0.02em; color: var(--text); }
    .w-unit { font-size: 0.85rem; margin-left: 2px; font-weight: 700; }
    .w-label { font-size: 0.68rem; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; margin: 0.25rem 0 0; }

    /* Filters */
    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
    .filter-input-wrap { position: relative; }
    .filter-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.85rem; pointer-events: none; z-index: 5; }
    .filter-input {
        width: 100%; min-height: 44px; border-radius: 12px; border: 1px solid var(--border); background-color: #fff;
        padding: 0.7rem 2.4rem 0.7rem 2.4rem; font-size: 0.875rem; font-weight: 600; color: var(--text);
        transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out); -webkit-appearance: none; -moz-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1.41 0 6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
    }
    .filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

    /* Icon + select combined field - icon is a flex sibling, immune to
       Chromium ignoring horizontal padding on native <select>. */
    .filter-field {
        display: flex; align-items: center; width: 100%; min-height: 44px;
        border: 1px solid var(--border); border-radius: 12px; background-color: #fff;
        padding: 0 12px; transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
        cursor: pointer;
    }
    .filter-field:hover { border-color: #cbd5e1; }
    .filter-field:focus-within { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .filter-field > i { color: var(--muted); font-size: 0.85rem; margin-right: 11px; min-width: 15px; text-align: center; }
    .filter-field > select {
        -webkit-appearance: none !important; -moz-appearance: none !important; appearance: none !important;
        flex: 1 1 auto !important; min-width: 0 !important; border: 0 !important; outline: none !important; background-color: transparent !important;
        font-size: 0.875rem !important; font-weight: 600 !important; color: var(--text) !important; height: 42px !important; cursor: pointer !important;
        padding: 0 24px 0 0 !important;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1.41 0 6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important; background-position: right center !important;
        box-shadow: none !important;
    }

    /* Table */
    .table-clean { margin-bottom: 0; }
    .table-clean thead th { background: #fafbfd; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: var(--slate); border-bottom: 1px solid #eef2f7; padding: 0.8rem 1.25rem; letter-spacing: 0.07em; }
    .table-clean tbody td { padding: 0.85rem 1.25rem; vertical-align: middle; border-top: 1px solid #f6f8fb; }
    #ct-table tbody tr:hover, .table-clean tbody tr:hover { background: #fafbff; }
    .pl-x { padding-left: 1.5rem !important; }
    .pr-x { padding-right: 1.5rem !important; }
    .sub-name { font-size: 0.92rem; font-weight: 700; color: var(--text); line-height: 1.2; }
    .sub-type { font-size: 0.67rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-top: 3px; letter-spacing: 0.04em; }
    .dept-pill { background: var(--slate-light); color: var(--slate); font-size: 0.78rem; font-weight: 700; padding: 0.3rem 0.8rem; border-radius: 20px; white-space: nowrap; }
    .periods-value, .hours-value { font-weight: 800; color: var(--text); font-size: 0.95rem; }

    /* Avatar */
    .fc-avatar {
        width: 42px; height: 42px; min-width: 42px; border-radius: 12px; margin-right: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.9rem;
    }

    /* Capacity */
    .capacity-box { min-width: 120px; }
    .progress-track { height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-bottom: 0.3rem; }
    .progress-fill { height: 100%; border-radius: 10px; transition: width 0.6s var(--ease-out); }
    .capacity-text { font-size: 0.68rem; font-weight: 700; color: var(--muted); }

    /* Badges */
    .badge-soft { border-radius: 20px; font-weight: 800; font-size: 0.68rem; padding: 0.28rem 0.65rem; letter-spacing: 0.04em; }
    .badge-status { display: inline-block; min-width: 92px; text-align: center; }

    /* Buttons */
    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

    /* Pagination */
    .pagination-panel { background: #fff; padding: 1rem 1.5rem; border-radius: 16px; border: 1px solid var(--border); }
    .pagination-info { font-size: 0.875rem; color: var(--muted); }
    .pagination-info strong { color: var(--text); font-weight: 800; }
    .pagination { margin: 0; }
    .pagination .page-link { border-radius: 10px !important; margin: 0 3px; border: 1px solid var(--border); color: var(--slate); font-weight: 700; font-size: 0.85rem; min-width: 34px; text-align: center; padding: 0.35rem 0.6rem; }
    .pagination .page-item.active .page-link { background: var(--indigo); border-color: var(--indigo); color: #fff; }
    .pagination .page-link:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .pagination .disabled .page-link { opacity: 0.45; }

    /* Empty state */
    .empty-state { padding: 3rem 1rem; text-align: center; }
    .empty-icon { width: 64px; height: 64px; margin: 0 auto 1rem; border-radius: 18px; background: var(--slate-light); color: var(--slate); font-size: 1.4rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.25rem; }
    .empty-desc { color: var(--muted); margin: 0; }

    /* Color helpers (BS4-safe) */
    .text-indigo { color: var(--indigo); } .text-emerald { color: var(--emerald); }
    .text-amber { color: var(--amber); } .text-rose { color: var(--rose); }
    .text-blue { color: var(--blue); } .text-slate { color: var(--slate); }
    .bg-indigo-light { background: var(--indigo-light); } .bg-emerald-light { background: var(--emerald-light); }
    .bg-amber-light { background: var(--amber-light); } .bg-rose-light { background: var(--rose-light); }
    .bg-blue-light { background: var(--blue-light); } .bg-slate-light { background: var(--slate-light); }
    .bg-emerald { background: var(--emerald); } .bg-amber { background: var(--amber); }
    .bg-rose { background: var(--rose); } .bg-blue { background: var(--blue); }
    .bg-slate { background: var(--slate); }

    @media print {
        .dash-wrap { padding: 0; background: #fff; }
        .btn-dash, form#filter-form { display: none !important; }
        .dash-panel { border: 1px solid #eee !important; box-shadow: none !important; break-inside: avoid; }
    }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
        .pl-x { padding-left: 1rem !important; }
        .pr-x { padding-right: 1rem !important; }
    }
</style>
@endsection
