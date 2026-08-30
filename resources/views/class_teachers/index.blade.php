@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-user-tie mr-2" style="color: var(--indigo);"></i>Class Teachers</h1>
            <p class="dash-sub mb-0">Form tutors responsible for attendance, pastoral care &amp; academic monitoring</p>
        </div>
    </div>

    @include('flash::message')

    {{-- FILTER BAR --}}
    <div class="card dash-panel mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('class-teachers.index') }}">
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-5 mb-3 mb-lg-0">
                        <label class="filter-label">Academic Year</label>
                        <div class="filter-field">
                            <i class="fas fa-calendar-alt"></i>
                            <select name="academic_year_id" onchange="this.form.submit()">
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->academic_year_id }}" {{ $year->academic_year_id == $selectedAcademicYearId ? 'selected' : '' }}>
                                        {{ $year->name }} @if($year->is_current) — Current Session @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="custom-control custom-switch ct-filter-switch">
                            <input type="checkbox" class="custom-control-input" id="showUnassignedOnly">
                            <label class="custom-control-label font-weight-bold" for="showUnassignedOnly">Show unassigned sections only</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- STATS STRIP --}}
    @php
        $totalSections = $classSections->count();
        $assignedCount = $classSections->whereNotNull('class_teacher_id')->count();
        $unassignedCount = $totalSections - $assignedCount;
        $avatarColors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#f6a821', '#e9506d'];
    @endphp
    <div class="stats-strip mb-4 px-1">
        <div class="stat-chip">
            <strong>{{ $totalSections }}</strong> Sections
        </div>
        <span class="v-divider"></span>
        <div class="stat-chip stat-ok">
            <strong>{{ $assignedCount }}</strong> Assigned
        </div>
        @if($unassignedCount > 0)
            <span class="v-divider"></span>
            <div class="stat-chip stat-warn">
                <strong>{{ $unassignedCount }}</strong> Unassigned
            </div>
        @endif
    </div>

    {{-- ASSIGNMENT TABLE --}}
    @if($classSections->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-users"></i></div>
            <h4 class="empty-title">No Class Sections</h4>
            <p class="empty-desc">There are no class sections registered for this academic year yet.</p>
        </div>
    @else
        <div class="card dash-panel">
            <div class="table-responsive">
                <table class="table table-clean mb-0" id="ct-table">
                    <thead>
                        <tr>
                            <th style="width: 26%">Class &amp; Section</th>
                            <th>Current Class Teacher</th>
                            <th class="text-right" style="width: 34%">Change Assignment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($classSections as $cs)
                            @php
                                $ct = $cs->classTeacher;
                                $initials = $ct ? strtoupper(mb_substr($ct->first_name ?? '?', 0, 1)) . strtoupper(mb_substr($ct->last_name ?? '', 0, 1)) : '?';
                                $avatarColor = $avatarColors[$cs->class_section_id % count($avatarColors)];
                            @endphp
                            <tr data-assigned="{{ $ct ? '1' : '0' }}" data-row="{{ $cs->class_section_id }}">
                                <td>
                                    <div class="sub-name">{{ $cs->class?->name ?? '—' }} <span class="sec-text">{{ $cs->section?->name ?? '' }}</span></div>
                                    <div class="sub-type">{{ $cs->capacity ? 'Capacity: ' . $cs->capacity : 'Class section #' . $cs->class_section_id }}</div>
                                </td>
                                <td>
                                    @if($ct)
                                        <div class="d-flex align-items-center">
                                            <div class="fc-avatar" style="background: {{ $avatarColor }}">{{ $initials }}</div>
                                            <div>
                                                <div class="teacher-name">{{ $ct->full_name }}</div>
                                                <div class="sub-type">@if($ct->employee_id){{ $ct->employee_id }}@else&nbsp;@endif</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge-unassigned"><i class="fas fa-exclamation-circle mr-1"></i>Not Assigned</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    {!! Form::open(['route' => ['class-teachers.update', $cs->class_section_id], 'method' => 'PATCH', 'class' => 'ct-form d-inline-block text-left', 'style' => 'width: 280px; max-width: 100%;']) !!}
                                        <div class="filter-input-wrap">
                                            <i class="fas fa-chalkboard-teacher filter-icon"></i>
                                            <select name="teacher_id" class="ct-select"
                                                    data-original="{{ $cs->class_teacher_id }}"
                                                    data-class-name="{{ ($cs->class?->name ?? '') . ' - ' . ($cs->section?->name ?? '') }}">
                                                <option></option>
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->staff_id }}" {{ $cs->class_teacher_id == $teacher->staff_id ? 'selected' : '' }}>
                                                        {{ $teacher->first_name }} {{ $teacher->last_name }}@if($teacher->employee_id) · {{ $teacher->employee_id }}@endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($classSections->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Pick a teacher to assign them. You'll be asked to confirm before the change is saved.</small>
                    {{ $classSections->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @else
            <div class="card-footer bg-white border-0 py-3">
                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i>Pick a teacher to assign them. You'll be asked to confirm before the change is saved.</small>
            </div>
            @endif
        </div>
    @endif
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
        --emerald: #10b981;
        --amber-bg: #fffbeb;
        --amber: #d97706;
        --slate: #64748b;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dash-wrap { padding: 2rem 1.5rem; }
    .dash-heading { font-size: 1.65rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em; margin-bottom: 0.15rem; }
    .dash-sub { font-size: 0.9rem; color: var(--muted); font-weight: 500; }

    /* Panels */
    .dash-panel { border: 1px solid var(--border); border-radius: 16px !important; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important; }

    /* Filters */
    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
    .filter-input-wrap { position: relative; }
    .filter-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.85rem; pointer-events: none; z-index: 5; }
    .filter-input {
        width: 100%; min-height: 44px; border-radius: 12px; border: 1px solid var(--border); background: #fff;
        padding: 0.7rem 2.4rem 0.7rem 2.4rem; font-size: 0.875rem; font-weight: 600; color: var(--text);
        transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out); -webkit-appearance: none; -moz-appearance: none; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1.41 0 6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
    }
    .filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }
    .ct-filter-switch { margin-top: 0.6rem; }

    /* Icon + select combined field — icon is a flex sibling, immune to
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

    /* Select2 alignment */
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
    .filter-input-wrap .select2-container--bootstrap4 .select2-selection--single .select2-selection__placeholder { color: var(--muted); }
    .filter-input-wrap .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow { right: 10px; height: 42px; }

    /* Stats */
    .stats-strip { display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem 0; }
    .stat-chip { background: #fff; padding: 0.45rem 1rem; border-radius: 20px; border: 1px solid var(--border); font-size: 0.85rem; color: var(--muted); font-weight: 500; }
    .stat-chip strong { color: var(--text); font-weight: 800; }
    .stat-ok strong { color: var(--emerald); }
    .stat-warn { background: var(--amber-bg); border-color: #fde68a; }
    .stat-warn strong { color: var(--amber); }
    .v-divider { width: 1px; height: 20px; background: var(--border); margin: 0 1rem; }

    /* Table */
    .table-clean thead th { background: #fafbfd; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; color: var(--slate); border-bottom: 1px solid #eef2f7; padding: 0.8rem 1.5rem; letter-spacing: 0.07em; }
    .table-clean tbody td { padding: 0.85rem 1.5rem; vertical-align: middle; border-top: 1px solid #f6f8fb; }
    #ct-table tbody tr { transition: background 150ms ease; }
    #ct-table tbody tr:hover { background: #fafbff; }
    tr.row-hidden { display: none; }
    .sec-text { opacity: 0.65; font-weight: 600; font-size: 0.85em; }
    .sub-name { font-size: 0.95rem; font-weight: 700; color: var(--text); line-height: 1.2; }
    .sub-type { font-size: 0.68rem; color: var(--muted); text-transform: uppercase; font-weight: 600; margin-top: 3px; letter-spacing: 0.04em; }
    .teacher-name { font-weight: 700; color: var(--indigo-dark); font-size: 0.92rem; }

    /* Avatar */
    .fc-avatar {
        width: 42px; height: 42px; min-width: 42px; border-radius: 12px; margin-right: 12px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.9rem;
    }

    /* Badges */
    .badge-unassigned { background: var(--amber-bg); color: var(--amber); border: 1px solid #fde68a; font-size: 0.72rem; font-weight: 800; padding: 0.35rem 0.75rem; border-radius: 20px; display: inline-flex; align-items: center; white-space: nowrap; }

    /* Empty state */
    .empty-state { background: #fff; border: 1px dashed var(--border); border-radius: 18px; padding: 4rem 2rem; text-align: center; }
    .empty-icon { width: 74px; height: 74px; margin: 0 auto 1.25rem; border-radius: 20px; background: var(--indigo-light); color: var(--indigo); font-size: 1.75rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto; }

    /* ── Icon spacing guarantees ─────────────────────────────
       Never rely on spacing utilities alone for icon gaps. */
    .dash-heading i { margin-right: 12px; }
    .fc-avatar + div { margin-left: 0; }
    .fc-meta i { margin-right: 7px; }
    .badge-unassigned i, .stat-chip i, .card-footer small i { margin-right: 6px; }
    .filter-icon, .fc-toggle i, .action-btn i { margin-right: 0 !important; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
        .table-clean thead th, .table-clean tbody td { padding-left: 1rem; padding-right: 1rem; }
    }
</style>

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
<script>
    // Safe even if jQuery is not ready yet — Vite loads it as a deferred module,
    // so poll until jQuery + Select2 are available.
    (function () {
        function initPage() {
            if (!(window.jQuery && window.jQuery.fn && window.jQuery.fn.select2)) {
                return false;
            }

            var $ = window.jQuery;

            $('.ct-select').each(function () {
                var $sel = $(this);

                $sel.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    placeholder: $sel.find('option:selected').text().trim()
                        ? $sel.find('option:selected').text().trim()
                        : 'Assign a teacher...',
                    allowClear: false,
                    minimumResultsForSearch: 5
                });

                $sel.on('change', function () {
                    var newVal = $sel.val();
                    var original = String($sel.data('original') || '');

                    // Picked the empty placeholder — just revert quietly.
                    if (!newVal) {
                        $sel.val(original || null).trigger('change.select2');
                        return;
                    }

                    // Nothing actually changed.
                    if (String(newVal) === original) return;

                    var teacherName = $sel.find('option:selected').text().split('·')[0].trim();
                    var className = $sel.data('class-name');

                    if (window.confirm('Assign ' + teacherName + ' as Class Teacher of ' + className + '?')) {
                        $sel.closest('.ct-form')[0].submit();
                    } else {
                        $sel.val(original || null).trigger('change.select2');
                    }
                });
            });

            // Show unassigned sections only
            $('#showUnassignedOnly').on('change', function () {
                if (this.checked) {
                    $('#ct-table tbody tr[data-assigned="1"]').addClass('row-hidden');
                } else {
                    $('#ct-table tbody tr').removeClass('row-hidden');
                }
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
