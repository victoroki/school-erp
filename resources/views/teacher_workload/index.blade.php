@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Teacher Workload Analytics</h1>
            <p class="dash-sub">Resource allocation and capacity monitoring across all departments</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <button onclick="window.print()" class="btn-dash btn-ghost px-3">
                    <i class="fas fa-file-pdf me-2 text-rose"></i> Export PDF
                </button>
                <a href="{{ route('timetables.create') }}" class="btn-dash btn-primary-dash shadow-sm px-3">
                    <i class="fas fa-plus me-2"></i> Assign Period
                </a>
            </div>
        </div>
    </div>

    @include('flash::message')

    {{-- ② ANALYTICS WIDGETS --}}
    <div class="row g-3 mb-4">
        {{-- Total Evaluated --}}
        <div class="col-lg-3 col-md-6">
            <div class="dash-panel h-100 border-0 shadow-sm p-4 text-center">
                <div class="w-icon-box bg-blue-light text-blue mb-3 mx-auto">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="w-value">{{ sprintf('%02d', $stats['total']) }}</h3>
                <p class="w-label">Total Faculty</p>
            </div>
        </div>
        
        {{-- Overloaded --}}
        <div class="col-lg-3 col-md-6">
            @php 
                $overPercent = $stats['total'] > 0 ? round(($stats['overloaded'] / $stats['total']) * 100) : 0; 
                $overStatus = $stats['overloaded'] > 0 ? 'text-rose' : 'text-slate';
                $overBg = $stats['overloaded'] > 0 ? 'bg-rose-light' : 'bg-slate-light';
            @endphp
            <div class="dash-panel h-100 border-0 shadow-sm p-4 text-center">
                <div class="w-icon-box {{ $overBg }} {{ $overStatus }} mb-3 mx-auto">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="w-value {{ $overStatus }}">{{ sprintf('%02d', $stats['overloaded']) }}</h3>
                <p class="w-label">Overloaded <span class="badge-soft {{ $overBg }} {{ $overStatus }} ms-1">{{ $overPercent }}%</span></p>
            </div>
        </div>

        {{-- Optimum --}}
        <div class="col-lg-3 col-md-6">
            @php 
                $optPercent = $stats['total'] > 0 ? round(($stats['optimum'] / $stats['total']) * 100) : 0; 
            @endphp
            <div class="dash-panel h-100 border-0 shadow-sm p-4 text-center">
                <div class="w-icon-box bg-emerald-light text-emerald mb-3 mx-auto">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3 class="w-value text-emerald">{{ sprintf('%02d', $stats['optimum']) }}</h3>
                <p class="w-label">Optimum <span class="badge-soft bg-emerald-light text-emerald ms-1">{{ $optPercent }}%</span></p>
            </div>
        </div>

        {{-- Avg Load --}}
        <div class="col-lg-3 col-md-6">
            <div class="dash-panel h-100 border-0 shadow-sm p-4 text-center">
                <div class="w-icon-box bg-amber-light text-amber mb-3 mx-auto">
                    <i class="fas fa-clock"></i>
                </div>
                <h3 class="w-value text-amber">{{ round($stats['avg_hours']) }}<span class="fs-6 ms-1">h</span></h3>
                <p class="w-label">Avg Weekly Load</p>
            </div>
        </div>
    </div>

    {{-- ③ FILTER PANEL --}}
    <div class="dash-panel mb-3 border-0 shadow-sm">
        <div class="dash-panel-body p-3">
            <form method="GET" action="{{ route('teacher-workload.index') }}" id="filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="filter-label">Academic Session</label>
                        <select name="academic_year_id" class="filter-input" onchange="this.form.submit()">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->academic_year_id }}" {{ $year->academic_year_id == $selectedAcademicYearId ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) (Current) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="filter-label">Filter Department</label>
                        <select name="department_id" class="filter-input" onchange="this.form.submit()">
                            @foreach($departments as $val => $text)
                                <option value="{{ $val }}" {{ $val == $selectedDepartmentId ? 'selected' : '' }}>{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        @if($selectedDepartmentId)
                            <a href="{{ route('teacher-workload.index', ['academic_year_id' => $selectedAcademicYearId]) }}" class="btn-dash btn-ghost w-100 py-2">
                                <i class="fas fa-undo me-2"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ④ WORKLOAD TABLE --}}
    <div class="dash-panel border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-clean mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Faculty Member</th>
                        <th>Department</th>
                        <th class="text-center">Load (Periods)</th>
                        <th>Capacity Utilization</th>
                        <th class="text-center">Hrs/Week</th>
                        <th class="pe-4 text-end">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workloadData as $data)
                        @php
                            $percent = min(($data['total_periods'] / 40) * 100, 100);
                            $status = $data['status']['label'];
                            $theme = 'slate';
                            if($status == 'Overloaded') $theme = 'rose';
                            if($status == 'Standard') $theme = 'emerald';
                            if($status == 'Underloaded') $theme = 'blue';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <div class="sub-name">{{ $data['teacher']->full_name }}</div>
                                        <div class="sub-type">{{ $data['teacher']->jobPosition->name ?? 'Faculty Member' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-slate fw-600 fs-7">{{ $data['teacher']->department->name ?? 'General' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-850 text-dark fs-6">{{ $data['total_periods'] }}</span>
                            </td>
                            <td>
                                <div class="capacity-box">
                                    <div class="progress-track">
                                        <div class="progress-fill bg-{{ $theme }}" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="capacity-text">{{ round($percent) }}% Utilized</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="fw-700 text-dark">{{ round($data['est_hours']) }}h</span>
                            </td>
                            <td class="pe-4 text-end">
                                <span class="badge-soft bg-{{ $theme }}-light text-{{ $theme }} px-2 py-1 fs-xs fw-800">
                                    {{ strtoupper($status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-search text-slate opacity-20 fs-1 mb-3 d-block"></i>
                                <p class="text-muted">No faculty members found for this criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($paginator->lastPage() > 1)
            <div class="p-4 border-top bg-light-soft">
                <div class="d-flex align-items-center justify-content-between">
                    <p class="text-muted small mb-0">
                        Showing <strong>{{ $paginator->firstItem() }}</strong> – <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong>
                    </p>
                    {!! $paginator->appends(request()->query())->links() !!}
                </div>
            </div>
        @endif
    </div>
</div>

<style>
/* ── Emil Kowalski Design Engineering System ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --blue: #3b82f6; --blue-light: #eff6ff;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1.25rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.5rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; }
.w-icon-box { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.w-value { font-size: 1.375rem; font-weight: 850; margin: 0; letter-spacing: -0.02em; }
.w-label { font-size: 0.7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0.125rem 0 0; }

.filter-label { font-size: 0.65rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
.filter-input { width: 100%; border-radius: 8px; border: 1px solid var(--border); background: #fff; padding: 0.5rem 0.875rem; font-size: 0.813rem; font-weight: 600; color: var(--text); transition: all 200ms var(--ease-out); appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.875rem center; background-size: 10px; }
.filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

.table-clean thead th { background: #fcfcfd; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: var(--slate); border-bottom: 1px solid #f1f5f9; padding: 0.75rem 1rem; letter-spacing: 0.08em; }
.table-clean tbody td { padding: 0.75rem 1rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }

.sub-name { font-size: 0.875rem; font-weight: 750; color: var(--text); line-height: 1.1; }
.sub-type { font-size: 0.65rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-top: 2px; }

.capacity-box { min-width: 100px; }
.progress-track { height: 5px; background: #f1f5f9; border-radius: 10px; overflow: hidden; margin-bottom: 0.25rem; }
.progress-fill { height: 100%; border-radius: 10px; transition: width 0.6s ease; }
.capacity-text { font-size: 0.65rem; font-weight: 700; color: var(--muted); }

.badge-soft { border-radius: 4px; }
.fs-xs { font-size: 0.6rem; }
.fw-850 { font-weight: 850; }
.fw-750 { font-weight: 750; }
.fw-600 { font-weight: 600; }
.fs-7 { font-size: 0.75rem; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.813rem; font-weight: 750; transition: all 200ms var(--ease-out); text-decoration: none !important; }
.btn-primary-dash { background: var(--indigo); color: #fff; padding: 0.5rem 1rem; }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; }
.bg-light-soft { background: #f8fafc; }

@media print {
    .dash-wrap { padding: 0; background: #fff; }
    .btn-dash, .dash-panel form { display: none !important; }
    .dash-panel { border: 1px solid #eee !important; box-shadow: none !important; }
}
</style>
@endsection
