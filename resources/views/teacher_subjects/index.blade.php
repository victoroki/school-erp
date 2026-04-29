@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Teacher Subjects</h1>
            <p class="dash-sub">Manage subject assignments for teachers across different classes</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <a class="btn-dash btn-primary-dash" href="{{ route('teacher-subjects.create') }}">
                <i class="fas fa-plus me-1"></i> Add Assignment
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ② FILTER BAR --}}
    <div class="dash-panel mb-4">
        <div class="dash-panel-body py-3">
            <form method="GET" action="{{ route('teacher-subjects.index') }}" id="filter-form">
                <div class="row align-items-end">
                    {{-- Academic Year --}}
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="filter-label"><i class="fas fa-calendar-alt mr-1"></i> Academic Year</label>
                        <select name="academic_year_id" class="filter-input" onchange="this.form.submit()">
                            @foreach($academicYears as $year)
                                <option value="{{ $year->academic_year_id }}" {{ $year->academic_year_id == $selectedYearId ? 'selected' : '' }}>
                                    {{ $year->name }} @if($year->is_current) ★ Current @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Teacher Filter --}}
                    <div class="col-md-4 mb-3 mb-md-0">
                        <label class="filter-label"><i class="fas fa-chalkboard-teacher mr-1"></i> Teacher</label>
                        <select name="staff_id" class="filter-input" onchange="this.form.submit()">
                            <option value="">— All Teachers —</option>
                            @foreach($teacherOptions as $sid => $sname)
                                <option value="{{ $sid }}" {{ $sid == $selectedStaffId ? 'selected' : '' }}>
                                    {{ $sname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Clear Filter --}}
                    <div class="col-md-4 mb-3 mb-md-0 d-flex align-items-end">
                        @if($selectedStaffId)
                            <a href="{{ route('teacher-subjects.index', ['academic_year_id' => $selectedYearId]) }}" class="btn-dash btn-ghost">
                                <i class="fas fa-times me-1"></i> Clear Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ③ SELECTED YEAR LABEL --}}
    @php
        $selectedYear = $academicYears->firstWhere('academic_year_id', $selectedYearId);
    @endphp
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0 text-dark" style="font-size: .938rem; font-weight: 700;">Assignments for:</h5>
            <span class="badge-count" style="font-size: .813rem;">{{ $selectedYear->name ?? '—' }}</span>
            @if($selectedYear && $selectedYear->is_current)
                <span class="badge-soft text-emerald bg-emerald-light">Current Year</span>
            @endif
        </div>
        <span class="text-muted" style="font-size: .813rem; font-weight: 500;">
            {{ $totalAssignments }} assignment(s) across <strong>{{ $totalTeachers }}</strong> teacher(s)
        </span>
    </div>

    {{-- ④ ASSIGNMENTS GRID --}}
    @if($grouped->isEmpty())
        <div class="dash-alert mt-4">
            <div class="da-icon bg-blue-light text-blue">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="da-body">
                <h4 class="da-title">No assignments found</h4>
                <p class="da-desc">There are no subject assignments for this academic year.</p>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($grouped as $staffId => $assignments)
                @php
                    $teacher = $assignments->first()->staff;
                @endphp
                <div class="col-lg-6 mb-4">
                    <div class="ts-card">
                        <div class="ts-header">
                            <div class="d-flex align-items-center gap-3">
                                <div class="ts-avatar">
                                    {{ strtoupper(substr($teacher->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($teacher->last_name ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="ts-name">{{ $teacher->full_name ?? '— Unknown —' }}</div>
                                    <div class="ts-sub">
                                        @if($teacher && $teacher->employee_id) ID: {{ $teacher->employee_id }}
                                        @elseif($teacher && $teacher->tsc_number) TSC: {{ $teacher->tsc_number }}
                                        @else Staff ID: {{ $staffId }} @endif
                                    </div>
                                </div>
                            </div>
                            <span class="badge-count">{{ $assignments->count() }} Subjects</span>
                        </div>
                        <div class="ts-body">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Subject</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments->sortBy(fn($a) => [$a->classSection?->class?->name, $a->subject?->name]) as $record)
                                        <tr>
                                            <td><span class="font-weight-bold text-dark" style="font-size: .813rem;">{{ $record->subject?->name ?? '—' }}</span></td>
                                            <td><span class="badge-soft">{{ $record->classSection?->class?->name ?? '—' }}</span></td>
                                            <td><span class="badge-soft bg-indigo-light text-indigo">{{ $record->classSection?->section?->name ?? '—' }}</span></td>
                                            <td class="text-right p-2">
                                                <div class="d-flex justify-content-end gap-1">
                                                    <a href="{{ route('teacher-subjects.edit', $record->teacher_subject_id) }}" class="action-btn" title="Edit">
                                                        <i class="far fa-edit"></i>
                                                    </a>
                                                    {!! Form::open(['route' => ['teacher-subjects.destroy', $record->teacher_subject_id], 'method' => 'delete', 'class' => 'm-0 d-flex']) !!}
                                                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'action-btn btn-delete', 'title' => 'Delete', 'onclick' => "return confirm('Remove this assignment?')"]) !!}
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- ⑤ PAGINATION --}}
        @if($paginator->lastPage() > 1)
            <div class="dash-panel mt-2 py-2 px-3">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-muted" style="font-size: .813rem;">
                        Showing <strong>{{ $paginator->firstItem() }}</strong> – <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> teachers
                    </span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link shadow-none" href="{{ $paginator->previousPageUrl() ?? '#' }}"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            @foreach(range(1, $paginator->lastPage()) as $page)
                                @if($page === 1 || $page === $paginator->lastPage() || abs($page - $paginator->currentPage()) <= 2)
                                    <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                                        <a class="page-link shadow-none" href="{{ $paginator->url($page) }}">{{ $page }}</a>
                                    </li>
                                @elseif($page === 2 && $paginator->currentPage() > 4 || $page === $paginator->lastPage() - 1 && $paginator->currentPage() < $paginator->lastPage() - 3)
                                    <li class="page-item disabled"><span class="page-link shadow-none">&hellip;</span></li>
                                @endif
                            @endforeach
                            <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                                <a class="page-link shadow-none" href="{{ $paginator->nextPageUrl() ?? '#' }}"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        @endif
    @endif
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --blue: #3b82f6; --blue-light: #eff6ff;
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --slate: #64748b;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-blue-light { background: var(--blue-light); } .text-blue { color: var(--blue); }

.dash-wrap { padding: 1rem; }
.dash-heading { font-size: 1.375rem; font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
.dash-panel-body { padding: 1rem; }

/* Form Filters */
.filter-label { font-size: .75rem; font-weight: 700; color: var(--slate); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: .375rem; display: block; }
.filter-input { 
    width: 100%; padding: .5rem .875rem; border-radius: 8px; border: 1px solid var(--border); 
    background: #f8fafc; font-size: .813rem; font-weight: 500; color: var(--text);
    transition: all 150ms var(--ease-out); appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat; background-position: right .75rem center; background-size: 16px 12px;
}
.filter-input:focus { background: #fff; border-color: var(--blue); outline: none; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .5rem .875rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; cursor: pointer; }
.btn-primary-dash { background: var(--indigo); color: #fff; border-color: var(--indigo); }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2); }
.btn-ghost { background: #f1f5f9; color: var(--slate); border-color: transparent; }
.btn-ghost:hover { background: #e2e8f0; color: var(--text); }

/* Teacher Subject Cards */
.ts-card { background: #fff; border: 1px solid var(--border); border-radius: 14px; overflow: hidden; transition: all 200ms var(--ease-out); box-shadow: 0 1px 3px rgba(0,0,0,0.02); height: 100%; display: flex; flex-direction: column; }
.ts-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.ts-header { padding: 1rem 1.25rem; background: #fff; border-bottom: 1px solid #f8fafc; display: flex; align-items: center; justify-content: space-between; }
.ts-avatar { width: 40px; height: 40px; border-radius: 10px; background: var(--blue-light); color: var(--blue); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: .938rem; }
.ts-name { font-size: .938rem; font-weight: 700; color: var(--text); line-height: 1.2; }
.ts-sub { font-size: .75rem; color: var(--muted); margin-top: .125rem; }
.ts-body { padding: 0; flex: 1; }

.badge-count { background: var(--indigo-light); color: var(--indigo); font-size: .688rem; font-weight: 800; padding: .25rem .5rem; border-radius: 6px; }
.badge-soft { background: #f1f5f9; color: #475569; font-size: .688rem; font-weight: 700; padding: .2rem .5rem; border-radius: 6px; }

/* Tables */
.table { margin-bottom: 0; }
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); font-size: .688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .625rem 1.25rem; }
.table tbody td { padding: .75rem 1.25rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }
.table tbody tr:last-child td { border-bottom: 0; }
.table-hover tbody tr:hover { background-color: #f8fafc; }

/* Action Buttons */
.action-btn { width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; color: var(--slate); transition: all 150ms ease; border: 1px solid transparent; background: transparent; font-size: .75rem; }
.action-btn:hover { background: #f1f5f9; color: var(--text); border-color: #e2e8f0; }
.btn-delete:hover { background: #fee2e2; color: #ef4444; border-color: #fecaca; }

/* Empty state */
.dash-alert { display: flex; gap: 1rem; padding: 1.25rem; border-radius: 12px; background: #fff; border: 1px solid var(--border); align-items: center; }
.da-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 1.25rem; }
.da-body { flex: 1; }
.da-title { font-size: .938rem; font-weight: 700; color: var(--text); margin: 0; }
.da-desc { font-size: .813rem; color: var(--muted); margin: .125rem 0 0; }

.pagination .page-link { border-radius: 6px; margin: 0 2px; color: var(--slate); border: 1px solid transparent; background: transparent; font-weight: 600; font-size: .813rem; }
.pagination .page-item.active .page-link { background: var(--blue-light); color: var(--blue); border-color: transparent; }
.pagination .page-link:hover { background: #f1f5f9; color: var(--text); border-color: var(--border); }
</style>
@endsection
