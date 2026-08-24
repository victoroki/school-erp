@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-chalkboard-teacher mr-2 text-indigo"></i>Teacher Assignments</h1>
            <p class="dash-sub mb-0">Faculty subject distribution and class management</p>
        </div>
        <div class="d-flex mt-3 mt-md-0">
            <button type="button" id="btnToggleAll" class="btn-dash btn-ghost mr-2">
                <i class="fas fa-expand-arrows-alt mr-1"></i> Expand All
            </button>
            <a class="btn-dash btn-primary-dash shadow-sm" href="{{ route('teacher-subjects.create') }}">
                <i class="fas fa-plus mr-1"></i> New Assignment
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- FILTER BAR --}}
    <div class="card dash-panel mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('teacher-subjects.index') }}" id="filter-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-4 mb-3 mb-lg-0">
                        <label class="filter-label">Academic Year</label>
                        <div class="filter-field">
                            <i class="fas fa-calendar-alt"></i>
                            <select name="academic_year_id" onchange="this.form.submit()">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->academic_year_id }}" {{ $year->academic_year_id == $selectedYearId ? 'selected' : '' }}>
                                        {{ $year->name }} @if($year->is_current) - Current Session @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group col-lg-5 mb-3 mb-lg-0">
                        <label class="filter-label">Search Faculty Member</label>
                        <div class="filter-input-wrap">
                            <i class="fas fa-search filter-icon"></i>
                            <select name="staff_id" id="staff-filter" class="filter-input" onchange="this.form.submit()">
                                <option value="">- Show All Teachers -</option>
                                @foreach($teacherOptions as $sid => $sname)
                                    <option value="{{ $sid }}" {{ $sid == $selectedStaffId ? 'selected' : '' }}>{{ $sname }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        @if($selectedStaffId)
                            <a href="{{ route('teacher-subjects.index', ['academic_year_id' => $selectedYearId]) }}" class="btn-dash btn-ghost w-100">
                                <i class="fas fa-undo mr-1"></i> Reset Filters
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- STATS STRIP --}}
    @php
        $selectedYear = $academicYears->firstWhere('academic_year_id', $selectedYearId);
    @endphp
    <div class="stats-strip mb-4 px-1">
        <div class="year-indicator">
            <span class="dot {{ $selectedYear && $selectedYear->is_current ? 'active' : '' }}"></span>
            <span class="year-text">{{ $selectedYear->name ?? '-' }}</span>
        </div>
        <span class="v-divider"></span>
        <div class="stats-text">
            <strong>{{ $totalAssignments }}</strong> Assignments across <strong>{{ $totalTeachers }}</strong> Teachers
        </div>
    </div>

    {{-- FACULTY GRID --}}
    @if($grouped->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-chalkboard-teacher"></i></div>
            <h4 class="empty-title">No Active Assignments</h4>
            <p class="empty-desc">No teacher-subject assignments exist for this academic year yet.</p>
            <a href="{{ route('teacher-subjects.create') }}" class="btn-dash btn-primary-dash mt-2">Start Assigning</a>
        </div>
    @else
        <div class="row">
            @foreach($grouped as $staffId => $assignments)
                @php
                    $teacher = $assignments->first()->staff;
                    $initials = strtoupper(mb_substr($teacher->first_name ?? '?', 0, 1)) . strtoupper(mb_substr($teacher->last_name ?? '', 0, 1));
                    $avatarColors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#f6a821', '#e9506d'];
                    $avatarColor = $avatarColors[$staffId % count($avatarColors)];
                    $sorted = $assignments->sortBy(fn($a) => [$a->classSection?->class?->name, $a->subject?->name]);
                @endphp
                <div class="col-xl-6 mb-4">
                    <div class="faculty-card collapsed" data-staff-id="{{ $staffId }}">
                        <div class="fc-header" role="button" tabindex="0" aria-expanded="false">
                            <div class="d-flex align-items-center">
                                <div class="fc-avatar" style="background: {{ $avatarColor }}">{{ $initials }}</div>
                                <div>
                                    <h4 class="fc-name mb-0">{{ $teacher->full_name ?? '-' }}</h4>
                                    <div class="fc-meta">
                                        <i class="fas fa-id-badge mr-1"></i>{{ $teacher->employee_id ?: ($teacher->tsc_number ?: 'Faculty #' . $staffId) }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="fc-badge">{{ $assignments->count() }} {{ $assignments->count() === 1 ? 'Subject' : 'Subjects' }}</span>
                                <span class="fc-toggle ml-3"><i class="fas fa-chevron-down"></i></span>
                            </div>
                        </div>
                        <div class="fc-content">
                            <div class="table-responsive">
                                <table class="table table-clean mb-0">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Class &amp; Section</th>
                                            <th class="text-right w-actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sorted as $record)
                                            <tr>
                                                <td>
                                                    <div class="sub-name">{{ $record->subject?->name ?? '-' }}</div>
                                                    <div class="sub-type">Regular Course</div>
                                                </td>
                                                <td>
                                                    <span class="class-pill">
                                                        <i class="fas fa-graduation-cap mr-1"></i>
                                                        {{ $record->classSection?->class?->name ?? '-' }}
                                                        @if($record->classSection?->section?->name)
                                                            <span class="sec-text">{{ $record->classSection->section->name }}</span>
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="text-right">
                                                    <div class="d-flex justify-content-end">
                                                        <a href="{{ route('teacher-subjects.edit', $record->teacher_subject_id) }}" class="action-btn mr-2" title="Edit assignment">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                        {!! Form::open(['route' => ['teacher-subjects.destroy', $record->teacher_subject_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                                                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'action-btn btn-delete', 'title' => 'Remove assignment', 'onclick' => "return confirm('Remove this assignment?')"]) !!}
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
                </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($paginator->lastPage() > 1)
            <div class="pagination-panel mt-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="pagination-info mb-2 mb-md-0">
                        Showing <strong>{{ $paginator->firstItem() }}</strong>-<strong>{{ $paginator->lastItem() }}</strong>
                        of <strong>{{ $paginator->total() }}</strong> faculty members
                    </div>
                    <div class="pagination-links">
                        {!! $paginator->appends(['academic_year_id' => $selectedYearId, 'staff_id' => $selectedStaffId])->links() !!}
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
        --emerald: #10b981;
        --slate: #64748b;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dash-wrap { padding: 2rem 1.5rem; }

    /* Header */
    .dash-heading { font-size: 1.65rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em; margin-bottom: 0.15rem; }
    .dash-sub { font-size: 0.9rem; color: var(--muted); font-weight: 500; }
    .text-indigo { color: var(--indigo); }

    /* Filter bar */
    .dash-panel { border: 1px solid var(--border); border-radius: 16px !important; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important; }
    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
    .filter-input-wrap { position: relative; }
    .filter-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.85rem; pointer-events: none; z-index: 5; }
    .filter-input {
        width: 100%; min-height: 44px; border-radius: 12px; border: 1px solid var(--border); background: #fff;
        padding: 0.7rem 2.4rem 0.7rem 2.4rem; font-size: 0.875rem; font-weight: 600; color: var(--text);
        transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out); -webkit-appearance: none; -moz-appearance: none; appearance: none;
        /* restore a dropdown arrow for native selects */
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

    /* Select2 must match the filter inputs and leave room for the icon */
    .filter-input-wrap .select2-container--bootstrap4 .select2-selection--single {
        height: 44px; border-radius: 12px; border-color: var(--border);
        display: flex; align-items: center; transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
    }
    .filter-input-wrap .select2-container--bootstrap4.select2-container--focus .select2-selection--single {
        border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08);
    }
    .filter-input-wrap .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        padding-left: 2.4rem; color: var(--text); font-weight: 600; font-size: 0.875rem; line-height: 42px;
    }
    .filter-input-wrap .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder {
        color: var(--muted);
    }
    .filter-input-wrap .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        right: 10px; height: 42px;
    }

    /* Stats strip */
    .stats-strip { display: flex; align-items: center; }
    .year-indicator { background: #fff; padding: 0.45rem 1rem; border-radius: 20px; border: 1px solid var(--border); display: inline-flex; align-items: center; }
    .dot { width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; margin-right: 8px; }
    .dot.active { background: var(--emerald); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.18); }
    .year-text { font-size: 0.85rem; font-weight: 700; color: var(--text); }
    .v-divider { width: 1px; height: 20px; background: var(--border); margin: 0 1rem; }
    .stats-text { font-size: 0.875rem; color: var(--muted); font-weight: 500; }
    .stats-text strong { color: var(--text); font-weight: 800; }

    /* Faculty cards */
    .faculty-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04); transition: border-color 250ms var(--ease-out), box-shadow 250ms var(--ease-out); overflow: hidden; height: 100%; }
    .faculty-card:hover { border-color: #c7d2fe; box-shadow: 0 6px 20px rgba(79, 70, 229, 0.08); }
    .faculty-card:not(.collapsed) { border-color: var(--indigo); box-shadow: 0 12px 30px rgba(79, 70, 229, 0.12); }

    .fc-header { padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none; }
    .fc-avatar {
        width: 44px; height: 44px; min-width: 44px; border-radius: 12px; margin-right: 14px;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.95rem; letter-spacing: 0.02em;
    }
    .fc-name { font-size: 1.05rem; font-weight: 800; color: var(--text); letter-spacing: -0.01em; }
    .fc-meta { font-size: 0.78rem; color: var(--muted); font-weight: 600; margin-top: 3px; }
    .fc-badge { background: var(--indigo-light); color: var(--indigo); font-size: 0.72rem; font-weight: 800; padding: 0.35rem 0.75rem; border-radius: 20px; white-space: nowrap; }

    /* Collapse behaviour */
    .fc-content { max-height: 2000px; opacity: 1; border-top: 1px solid #f1f5f9; overflow: hidden; transition: max-height 300ms var(--ease-out), opacity 250ms ease; }
    .faculty-card.collapsed .fc-content { max-height: 0; opacity: 0; border-top-width: 0; }
    .fc-toggle { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 9px; border: 1px solid var(--border); background: #fff; color: var(--muted); transition: transform 300ms var(--ease-out), background 150ms ease; }
    .fc-toggle i { transition: transform 300ms var(--ease-out); transform: rotate(180deg); }
    .faculty-card.collapsed .fc-toggle i { transform: rotate(0); }
    .fc-header:focus-visible { outline: 2px solid var(--indigo); outline-offset: 2px; border-radius: 16px; }

    /* Table */
    .table-clean { margin-bottom: 0; }
    .table-clean thead th { background: #fafbfd; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: var(--slate); border-bottom: 1px solid #eef2f7; padding: 0.8rem 1.5rem; letter-spacing: 0.07em; }
    .table-clean tbody td { padding: 0.85rem 1.5rem; vertical-align: middle; border-top: 1px solid #f6f8fb; }
    .w-actions { width: 110px; }
    .sub-name { font-size: 0.92rem; font-weight: 700; color: var(--text); line-height: 1.2; }
    .sub-type { font-size: 0.67rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-top: 3px; letter-spacing: 0.04em; }
    .class-pill { background: var(--indigo-light); color: var(--indigo); font-size: 0.8rem; font-weight: 700; padding: 0.35rem 0.85rem; border-radius: 20px; display: inline-flex; align-items: center; white-space: nowrap; }
    .sec-text { opacity: 0.65; font-weight: 500; margin-left: 0.3rem; }

    /* Row actions */
    .action-btn { width: 32px; height: 32px; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; color: var(--slate); transition: all 150ms ease; background: #fff; border: 1px solid var(--border); font-size: 0.85rem; }
    .action-btn:hover { background: var(--indigo-light); color: var(--indigo); border-color: var(--indigo); text-decoration: none; }
    .btn-delete:hover { background: #fee2e2; color: #ef4444; border-color: #fca5a5; }

    /* Buttons */
    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

    /* Empty state */
    .empty-state { background: #fff; border: 1px dashed var(--border); border-radius: 18px; padding: 4rem 2rem; text-align: center; }
    .empty-icon { width: 74px; height: 74px; margin: 0 auto 1.25rem; border-radius: 20px; background: var(--indigo-light); color: var(--indigo); font-size: 1.75rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto 1rem; }

    /* Pagination */
    .pagination-panel { background: #fff; padding: 1rem 1.5rem; border-radius: 16px; border: 1px solid var(--border); }
    .pagination-info { font-size: 0.875rem; color: var(--muted); }
    .pagination-info strong { color: var(--text); font-weight: 800; }
    .pagination { margin: 0; }
    .pagination .page-link { border-radius: 10px !important; margin: 0 3px; border: 1px solid var(--border); color: var(--slate); font-weight: 700; font-size: 0.85rem; min-width: 34px; text-align: center; padding: 0.35rem 0.6rem; }
    .pagination .page-item.active .page-link { background: var(--indigo); border-color: var(--indigo); color: #fff; }
    .pagination .page-link:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .pagination .disabled .page-link { opacity: 0.45; }

    /* - Icon spacing guarantees -
       Never rely on spacing utilities alone for icon gaps. */
    .dash-heading i { margin-right: 12px; }
    .btn-dash i { margin-right: 7px; }
    .btn-dash i:only-child { margin-right: 0; }
    .fc-meta i { margin-right: 7px; }
    .class-pill i { margin-right: 6px; }
    .filter-icon, .fc-toggle i, .action-btn i { margin-right: 0 !important; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
        .fc-header { padding: 1rem 1.1rem; }
        .table-clean thead th, .table-clean tbody td { padding-left: 1.1rem; padding-right: 1.1rem; }
    }
</style>

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
<script>
    // Safe even if jQuery is not ready yet - Vite loads it as a deferred
    // module, so poll until jQuery + Select2 are available.
    (function () {
        function initPage() {
            if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                return false;
            }

            window.jQuery('#staff-filter').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Search for a faculty member...'
            });

            var $ = window.jQuery;

            function setToggleLabel(expanded) {
                $('#btnToggleAll').html(expanded
                    ? '<i class="fas fa-compress-arrows-alt mr-1"></i> Collapse All'
                    : '<i class="fas fa-expand-arrows-alt mr-1"></i> Expand All');
            }

            $('.fc-header').on('click keypress', function (e) {
                if (e.type === 'keypress' && e.key !== 'Enter' && e.key !== ' ') return;
                e.preventDefault();
                var $card = $(this).closest('.faculty-card');
                $card.toggleClass('collapsed');
                $(this).attr('aria-expanded', String(!$card.hasClass('collapsed')));
            });

            var allExpanded = false;
            $('#btnToggleAll').on('click', function () {
                $('.faculty-card').toggleClass('collapsed', !allExpanded).each(function () {
                    $(this).find('.fc-header').attr('aria-expanded', String(allExpanded));
                });
                allExpanded = !allExpanded;
                setToggleLabel(allExpanded);
            });

            return true;
        }

        if (!initPage()) {
            var tries = 0;
            var timer = setInterval(function () {
                tries++;
                if (initPage() || tries > 100) clearInterval(timer);
            }, 50);
        }
    })();
</script>
@endpush
@endsection
