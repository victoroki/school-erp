@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1>Teacher Subject Assignments</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary" href="{{ route('teacher-subjects.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add Assignment
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        {{-- ── Filter bar ──────────────────────────────────────────────── --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('teacher-subjects.index') }}" id="filter-form">
                    <div class="form-row align-items-end">

                        {{-- Academic Year tabs-as-dropdown --}}
                        <div class="form-group col-md-4 mb-0">
                            <label class="small font-weight-bold text-muted mb-1">
                                <i class="fas fa-calendar-alt mr-1"></i>Academic Year
                            </label>
                            <select name="academic_year_id" id="filter-year" class="form-control"
                                    onchange="this.form.submit()">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->academic_year_id }}"
                                        {{ $year->academic_year_id == $selectedYearId ? 'selected' : '' }}>
                                        {{ $year->name }}
                                        @if($year->is_current) &nbsp;★ Current @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Teacher quick-filter --}}
                        <div class="form-group col-md-4 mb-0">
                            <label class="small font-weight-bold text-muted mb-1">
                                <i class="fas fa-chalkboard-teacher mr-1"></i>Filter by Teacher
                            </label>
                            <select name="staff_id" id="filter-staff" class="form-control"
                                    onchange="this.form.submit()">
                                <option value="">— All Teachers —</option>
                                @foreach($teacherOptions as $sid => $sname)
                                    <option value="{{ $sid }}"
                                        {{ $sid == $selectedStaffId ? 'selected' : '' }}>
                                        {{ $sname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Reset --}}
                        <div class="form-group col-md-4 mb-0 d-flex align-items-end">
                            @if($selectedStaffId)
                                <a href="{{ route('teacher-subjects.index', ['academic_year_id' => $selectedYearId]) }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i>Clear filter
                                </a>
                            @endif
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- ── Selected year label ─────────────────────────────────────── --}}
        @php
            $selectedYear = $academicYears->firstWhere('academic_year_id', $selectedYearId);
        @endphp

        <div class="d-flex align-items-center mb-3">
            <h5 class="mb-0 mr-2">
                Assignments for:
                <span class="badge badge-primary badge-pill px-3 py-2" style="font-size:.95rem;">
                    {{ $selectedYear->name ?? '—' }}
                </span>
                @if($selectedYear && $selectedYear->is_current)
                    <span class="badge badge-success ml-1">Current Year</span>
                @endif
            </h5>
            <span class="ml-auto text-muted small">
                {{ $totalAssignments }} assignment(s) across
                <strong>{{ $totalTeachers }}</strong> teacher(s)
                @if($paginator->lastPage() > 1)
                    &mdash; page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
                @endif
            </span>
        </div>

        {{-- ── Content ──────────────────────────────────────────────────── --}}
        @if($grouped->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-chalkboard-teacher fa-3x mb-3 d-block opacity-50"></i>
                    No subject assignments found for this academic year.
                    <div class="mt-3">
                        <a href="{{ route('teacher-subjects.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Add First Assignment
                        </a>
                    </div>
                </div>
            </div>
        @else
            @foreach($grouped as $staffId => $assignments)
                @php
                    $teacher = $assignments->first()->staff;
                @endphp

                <div class="card mb-3 shadow-sm">
                    {{-- Teacher header row --}}
                    <div class="card-header d-flex align-items-center py-2"
                         style="background: linear-gradient(135deg,#f8f9fa,#e9ecef)">
                        <div class="mr-3">
                            <div class="rounded-circle d-flex align-items-center justify-content-center
                                        bg-primary text-white font-weight-bold"
                                 style="width:38px;height:38px;font-size:1rem;">
                                {{ strtoupper(substr($teacher->first_name ?? '?', 0, 1)) }}{{ strtoupper(substr($teacher->last_name ?? '', 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <strong class="d-block">{{ $teacher->full_name ?? '— Unknown —' }}</strong>
                            @if($teacher && $teacher->employee_id)
                                <small class="text-muted">ID: {{ $teacher->employee_id }}</small>
                            @elseif($teacher && $teacher->tsc_number)
                                <small class="text-muted">TSC: {{ $teacher->tsc_number }}</small>
                            @endif
                        </div>
                        <span class="badge badge-info ml-auto">
                            {{ $assignments->count() }} {{ Str::plural('subject', $assignments->count()) }}
                        </span>
                    </div>

                    {{-- Assignments table for this teacher --}}
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th class="pl-3">#</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th class="text-center" style="width:110px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assignments->sortBy(fn($a) => [$a->classSection?->class?->name, $a->subject?->name]) as $i => $record)
                                    <tr>
                                        <td class="pl-3 text-muted small">{{ $i + 1 }}</td>
                                        <td>
                                            <span class="badge badge-secondary" style="font-size:.8rem;">
                                                {{ $record->subject?->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $record->classSection?->class?->name ?? '—' }}</td>
                                        <td>{{ $record->classSection?->section?->name ?? '—' }}</td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('teacher-subjects.edit', $record->teacher_subject_id) }}"
                                                   class="btn btn-outline-primary btn-xs"
                                                   title="Edit">
                                                    <i class="far fa-edit"></i>
                                                </a>
                                                {!! Form::open([
                                                    'route'  => ['teacher-subjects.destroy', $record->teacher_subject_id],
                                                    'method' => 'delete',
                                                    'style'  => 'display:inline',
                                                ]) !!}
                                                {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                                    'type'    => 'submit',
                                                    'class'   => 'btn btn-outline-danger btn-xs',
                                                    'title'   => 'Delete',
                                                    'onclick' => "return confirm('Remove this assignment?')",
                                                ]) !!}
                                                {!! Form::close() !!}
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            {{-- ── Pagination ──────────────────────────────────────────── --}}
            @if($paginator->lastPage() > 1)
                <div class="card">
                    <div class="card-body py-2">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">

                            <span class="text-muted small">
                                Showing teachers
                                <strong>{{ $paginator->firstItem() }}</strong>
                                –
                                <strong>{{ $paginator->lastItem() }}</strong>
                                of <strong>{{ $paginator->total() }}</strong>
                            </span>

                            <nav aria-label="Teacher pagination">
                                <ul class="pagination pagination-sm mb-0">

                                    {{-- Previous --}}
                                    <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link"
                                           href="{{ $paginator->previousPageUrl() ?? '#' }}"
                                           aria-label="Previous">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>

                                    {{-- Page numbers --}}
                                    @foreach(range(1, $paginator->lastPage()) as $page)
                                        @if(
                                            $page === 1 ||
                                            $page === $paginator->lastPage() ||
                                            abs($page - $paginator->currentPage()) <= 2
                                        )
                                            <li class="page-item {{ $page == $paginator->currentPage() ? 'active' : '' }}">
                                                <a class="page-link"
                                                   href="{{ $paginator->url($page) }}">
                                                    {{ $page }}
                                                </a>
                                            </li>
                                        @elseif(
                                            $page === 2 && $paginator->currentPage() > 4 ||
                                            $page === $paginator->lastPage() - 1 && $paginator->currentPage() < $paginator->lastPage() - 3
                                        )
                                            <li class="page-item disabled">
                                                <span class="page-link">&hellip;</span>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next --}}
                                    <li class="page-item {{ !$paginator->hasMorePages() ? 'disabled' : '' }}">
                                        <a class="page-link"
                                           href="{{ $paginator->nextPageUrl() ?? '#' }}"
                                           aria-label="Next">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>

                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            @endif

        @endif
    </div>
@endsection
