@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-7">
            <h1 class="dash-heading">Teacher Assignments</h1>
            <p class="dash-sub">Faculty subject distribution and class management</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="d-flex justify-content-md-end gap-2">
                <button type="button" id="btnToggleAll" class="btn-dash btn-ghost px-3">
                    <i class="fas fa-expand-arrows-alt me-2"></i> Expand All
                </button>
                <a class="btn-dash btn-primary-dash shadow-sm px-3" href="{{ route('teacher-subjects.create') }}">
                    <i class="fas fa-plus me-2"></i> New Assignment
                </a>
            </div>
        </div>
    </div>

    @include('flash::message')

    {{-- ② PREMIUM FILTER BAR --}}
    <div class="dash-panel mb-5 border-0 shadow-sm">
        <div class="dash-panel-body p-4">
            <form method="GET" action="{{ route('teacher-subjects.index') }}" id="filter-form">
                <div class="row g-4 align-items-end">
                    {{-- Academic Year --}}
                    <div class="col-lg-4">
                        <label class="filter-label">Academic Year</label>
                        <div class="filter-input-wrap">
                            <i class="fas fa-calendar-alt filter-icon text-indigo"></i>
                            <select name="academic_year_id" class="filter-input ps-5" onchange="this.form.submit()">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->academic_year_id }}" {{ $year->academic_year_id == $selectedYearId ? 'selected' : '' }}>
                                        {{ $year->name }} @if($year->is_current) (Current Session) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Teacher Filter --}}
                    <div class="col-lg-5">
                        <label class="filter-label">Search Faculty Member</label>
                        <div class="filter-input-wrap">
                            <i class="fas fa-search filter-icon"></i>
                            <select name="staff_id" class="filter-input ps-5 select2" onchange="this.form.submit()">
                                <option value="">— Show All Teachers —</option>
                                @foreach($teacherOptions as $sid => $sname)
                                    <option value="{{ $sid }}" {{ $sid == $selectedStaffId ? 'selected' : '' }}>
                                        {{ $sname }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Action Area --}}
                    <div class="col-lg-3">
                        @if($selectedStaffId)
                            <a href="{{ route('teacher-subjects.index', ['academic_year_id' => $selectedYearId]) }}" class="btn-dash btn-ghost w-100 py-2">
                                <i class="fas fa-undo me-2"></i> Reset Filters
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ③ STATISTICS OVERVIEW --}}
    @php
        $selectedYear = $academicYears->firstWhere('academic_year_id', $selectedYearId);
    @endphp
    <div class="d-flex align-items-center justify-content-between mb-4 px-1">
        <div class="d-flex align-items-center gap-3">
            <div class="year-indicator">
                <span class="dot {{ $selectedYear && $selectedYear->is_current ? 'active' : '' }}"></span>
                <span class="year-text">{{ $selectedYear->name ?? '—' }} Session</span>
            </div>
            <div class="v-divider"></div>
            <div class="stats-text">
                <span class="count">{{ $totalAssignments }}</span> Assignments Across <span class="count">{{ $totalTeachers }}</span> Teachers
            </div>
        </div>
    </div>

    {{-- ④ FACULTY GRID --}}
    @if($grouped->isEmpty())
        <div class="empty-state">
            <div class="empty-icon bg-indigo-light text-indigo">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4 class="empty-title">No Active Assignments</h4>
            <p class="empty-desc">Teachers haven't been assigned to any subjects for the selected academic year yet.</p>
            <a href="{{ route('teacher-subjects.create') }}" class="btn-dash btn-indigo-dash mt-3">Start Assigning</a>
        </div>
    @else
        <div class="row g-5"> {{-- Increased gutter spacing --}}
            @foreach($grouped as $staffId => $assignments)
                @php
                    $teacher = $assignments->first()->staff;
                    $initials = strtoupper(substr($teacher->first_name ?? '?', 0, 1)) . strtoupper(substr($teacher->last_name ?? '', 0, 1));
                @endphp
                <div class="col-xl-6">
                    <div class="faculty-card collapsed"> {{-- Added collapsed class by default --}}
                        <div class="fc-header">
                            <div class="d-flex align-items-center gap-2">
                                <div>
                                    <h4 class="fc-name">{{ $teacher->full_name ?? '—' }}</h4>
                                    <div class="fc-meta">
                                        <i class="fas fa-id-badge me-2"></i> {{-- Added spacing to icon --}}
                                        {{ $teacher->employee_id ?: ($teacher->tsc_number ?: 'Faculty #'.$staffId) }}
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="fc-badge">{{ $assignments->count() }} Subjects</div>
                                <button type="button" class="fc-toggle action-btn">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>
                        </div>
                        <div class="fc-content">
                            <div class="table-responsive">
                                <table class="table table-clean mb-0">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Class & Section</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assignments->sortBy(fn($a) => [$a->classSection?->class?->name, $a->subject?->name]) as $record)
                                            <tr>
                                                <td>
                                                    <div class="sub-name">{{ $record->subject?->name ?? '—' }}</div>
                                                    <div class="sub-type">Regular Course</div>
                                                </td>
                                                <td>
                                                    <span class="class-pill">
                                                        <i class="fas fa-graduation-cap me-2"></i> {{-- Added spacing to icon --}}
                                                        {{ $record->classSection?->class?->name ?? '—' }} 
                                                        <span class="sec-text">{{ $record->classSection?->section?->name ?? '' }}</span>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <a href="{{ route('teacher-subjects.edit', $record->teacher_subject_id) }}" class="action-btn" title="Edit">
                                                            <i class="far fa-edit"></i>
                                                        </a>
                                                        {!! Form::open(['route' => ['teacher-subjects.destroy', $record->teacher_subject_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                                                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'action-btn btn-delete', 'onclick' => "return confirm('Remove this assignment?')"]) !!}
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

        {{-- ⑤ PAGINATION --}}
        @if($paginator->lastPage() > 1)
            <div class="pagination-panel mt-5 shadow-sm border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="pagination-info">
                        Showing <strong>{{ $paginator->firstItem() }}</strong> – <strong>{{ $paginator->lastItem() }}</strong> of <strong>{{ $paginator->total() }}</strong> faculty members
                    </div>
                    {!! $paginator->appends(['academic_year_id' => $selectedYearId, 'staff_id' => $selectedStaffId])->links() !!}
                </div>
            </div>
        @endif
    @endif
</div>

<style>
/* ── Emil Kowalski Impeccable Design System ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 2.5rem; background: #fafafa; min-height: 100vh; } {{-- Increased wrap padding --}}
.dash-heading { font-size: 1.75rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.938rem; color: var(--muted); font-weight: 500; }

/* Filter Bar Enhancements */
.filter-label { font-size: 0.7rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.75rem; display: block; }
.filter-input-wrap { position: relative; }
.filter-icon { position: absolute; left: 1.25rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.95rem; pointer-events: none; }
.filter-input { 
    width: 100%; border-radius: 12px; border: 1px solid var(--border); background: #fff; 
    padding: 0.75rem 1rem; font-size: 0.875rem; font-weight: 600; color: var(--text);
    transition: all 200ms var(--ease-out); appearance: none;
}
.filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

/* Indicators */
.year-indicator { background: #fff; padding: 0.5rem 1rem; border-radius: 20px; border: 1px solid var(--border); display: flex; align-items: center; gap: 0.75rem; }
.dot { width: 8px; height: 8px; border-radius: 50%; background: #cbd5e1; }
.dot.active { background: var(--emerald); box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); }
.year-text { font-size: 0.875rem; font-weight: 700; color: var(--text); }
.v-divider { width: 1px; height: 20px; background: var(--border); }
.stats-text { font-size: 0.875rem; font-weight: 500; color: var(--muted); }
.stats-text .count { color: var(--text); font-weight: 800; }

/* Faculty Cards */
.faculty-card { background: #fff; border: 1px solid var(--border); border-radius: 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.02); transition: all 300ms var(--ease-out); overflow: hidden; margin-bottom: 0.5rem; }
.faculty-card:not(.collapsed) { border-color: var(--indigo); box-shadow: 0 12px 30px rgba(0,0,0,0.08); }

.fc-header { padding: 1.5rem; display: flex; align-items: center; justify-content: space-between; background: #fff; cursor: pointer; user-select: none; }
.fc-name { font-size: 1.063rem; font-weight: 800; color: var(--text); margin: 0; letter-spacing: -0.01em; }
.fc-meta { font-size: 0.813rem; color: var(--muted); font-weight: 500; margin-top: 4px; }
.fc-badge { background: #f8fafc; color: var(--indigo); font-size: 0.75rem; font-weight: 800; padding: 0.35rem 0.75rem; border-radius: 10px; border: 1px solid #edf2f7; }

/* Collapse/Expand Logic */
.fc-content { transition: all 300ms var(--ease-out); max-height: 2000px; opacity: 1; border-top: 1px solid #f8fafc; }
.faculty-card.collapsed .fc-content { max-height: 0; opacity: 0; border-top: 0; overflow: hidden; }
.faculty-card.collapsed .fc-toggle i { transform: rotate(0); }
.fc-toggle i { transition: transform 300ms var(--ease-out); transform: rotate(180deg); color: var(--muted); }

/* Table Cleanup */
.table-clean thead th { background: #fcfcfd; font-size: 0.688rem; font-weight: 800; text-transform: uppercase; color: var(--slate); border-bottom: 1px solid #f1f5f9; padding: 1rem 1.5rem; letter-spacing: 0.08em; }
.table-clean tbody td { padding: 1rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; }
.sub-name { font-size: 0.938rem; font-weight: 750; color: var(--text); line-height: 1.1; }
.sub-type { font-size: 0.688rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-top: 4px; }

.class-pill { background: var(--indigo-light); color: var(--indigo); font-size: 0.813rem; font-weight: 750; padding: 0.4rem 1rem; border-radius: 20px; display: inline-flex; align-items: center; }
.sec-text { opacity: 0.6; font-weight: 500; margin-left: 0.25rem; }

/* Action Buttons */
.action-btn { width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--slate); transition: all 150ms ease; background: #fff; border: 1px solid var(--border); font-size: 0.875rem; }
.action-btn:hover { background: var(--indigo-light); color: var(--indigo); border-color: var(--indigo); }
.btn-delete:hover { background: #fee2e2; color: #ef4444; border-color: #fca5a5; }

/* Pagination Panel */
.pagination-panel { background: #fff; padding: 1.5rem; border-radius: 20px; border: 1px solid var(--border); }
.pagination-info { font-size: 0.875rem; color: var(--muted); }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 750; transition: all 200ms var(--ease-out); text-decoration: none !important; }
.btn-primary-dash { background: var(--indigo); color: #fff; padding: 0.75rem 1.5rem; }
.btn-primary-dash:hover { background: #4338ca; transform: translateY(-1px); }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.625rem 1.25rem; }
.btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }
</style>

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%',
            placeholder: 'Search for a faculty member...'
        });

        // Toggle Single Card
        $(document).on('click', '.fc-header', function() {
            $(this).closest('.faculty-card').toggleClass('collapsed');
        });

        // Expand/Collapse All
        let allExpanded = false;
        $('#btnToggleAll').on('click', function() {
            if (allExpanded) {
                $('.faculty-card').addClass('collapsed');
                $(this).html('<i class="fas fa-expand-arrows-alt me-2"></i> Expand All');
            } else {
                $('.faculty-card').removeClass('collapsed');
                $(this).html('<i class="fas fa-compress-arrows-alt me-2"></i> Collapse All');
            }
            allExpanded = !allExpanded;
        });
    });
</script>
@endpush
@endsection
