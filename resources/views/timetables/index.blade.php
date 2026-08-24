@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4 no-print">
        <div>
            <h1 class="dash-heading"><i class="fas fa-table mr-2" style="color: var(--indigo);"></i>Master Timetable</h1>
            <p class="dash-sub mb-0">Weekly lesson schedule for each class section</p>
        </div>
        <div class="d-flex mt-3 mt-md-0">
            <button type="button" onclick="window.print()" class="btn-dash btn-ghost mr-2">
                <i class="fas fa-print"></i> Print
            </button>
            <a class="btn-dash btn-ghost mr-2" href="{{ route('timetables.auto-generate', ['academic_year_id' => $selectedAcademicYearId]) }}">
                <i class="fas fa-magic"></i> Auto-Generate
            </a>
            <a class="btn-dash btn-primary-dash shadow-sm" href="{{ route('timetables.create') }}">
                <i class="fas fa-plus"></i> Add Lesson
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- PRINT-ONLY HEADER --}}
    <div class="print-header">
        <h2>{{ config('app.name') }} - Master Timetable</h2>
        <p>
            {{ $classSectionOptions[$selectedClassSectionId] ?? '' }}@if(isset($academicYearOptions[$selectedAcademicYearId])) | {{ $academicYearOptions[$selectedAcademicYearId] }}@endif
        </p>
    </div>

    {{-- FILTER PANEL --}}
    @if($academicYearOptions->isNotEmpty())
        <div class="card dash-panel mb-4 no-print">
            <div class="card-body p-4">
                {!! Form::open(['route' => 'timetables.index', 'method' => 'GET', 'id' => 'timetable-filter-form']) !!}
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-4 mb-3 mb-lg-0">
                        <label class="filter-label">Academic Year</label>
                        <div class="filter-field">
                            <i class="fas fa-calendar-alt"></i>
                            {!! Form::select('academic_year_id', $academicYearOptions, $selectedAcademicYearId, ['id' => 'academic_year_id']) !!}
                        </div>
                    </div>
                    <div class="form-group col-lg-5 mb-3 mb-lg-0">
                        <label class="filter-label">Class &amp; Section</label>
                        <div class="filter-field">
                            <i class="fas fa-school"></i>
                            <select name="class_section_id" id="class_section_id">
                                @if($classSectionOptions->isEmpty())
                                    <option value="">- Select academic year first -</option>
                                @else
                                    <option value="">Select class and section</option>
                                    @foreach($classSectionOptions as $id => $label)
                                        <option value="{{ $id }}" {{ $id == $selectedClassSectionId ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <small id="class-section-loading" class="text-muted d-none">
                            <i class="fas fa-spinner fa-spin"></i> Loading classes...
                        </small>
                    </div>
                    <div class="col-lg-3 d-flex">
                        <div class="ml-md-auto">
                            <button type="submit" id="apply-btn" class="btn-dash btn-primary-dash mr-2">Apply</button>
                            <a href="{{ route('timetables.index') }}" class="btn-dash btn-ghost">Reset</a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    @endif

    @php
        $selectedClassLabel = $classSectionOptions[$selectedClassSectionId] ?? null;
        $selectedYearName = $academicYearOptions[$selectedAcademicYearId] ?? null;
    @endphp

    {{-- CONTEXT STRIP --}}
    @if($selectedClassLabel)
        <div class="stats-strip mb-4 px-1 no-print">
            <div class="stat-chip"><i class="fas fa-school mr-1"></i><strong>{{ $selectedClassLabel }}</strong></div>
            <span class="v-divider"></span>
            <div class="stat-chip">{{ $selectedYearName }}</div>
            <span class="v-divider"></span>
            <div class="stat-chip"><strong>{{ $timetables->count() }}</strong> Lessons / week</div>
        </div>
    @endif

    {{-- GRID --}}
    @if($academicYearOptions->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="far fa-calendar-alt"></i></div>
            <h4 class="empty-title">No Academic Years</h4>
            <p class="empty-desc">Set up at least one academic year before building timetables.</p>
        </div>
    @elseif($classSectionOptions->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-school"></i></div>
            <h4 class="empty-title">No Class Sections</h4>
            <p class="empty-desc">No class sections found for this academic year yet.</p>
        </div>
    @elseif($periods->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="far fa-clock"></i></div>
            <h4 class="empty-title">No Periods Defined</h4>
            <p class="empty-desc">Define timetable periods before scheduling lessons.</p>
        </div>
    @else
        <div class="card dash-panel overflow-hidden">
            <div class="table-responsive">
                <table class="tt-table" id="master-timetable">
                    <thead>
                        <tr>
                            <th class="tt-day-col no-print-border">Day / Period</th>
                            @foreach($periods as $period)
                                @php $isBreak = ($period->type ?? 'period') === 'break'; @endphp
                                <th class="{{ $isBreak ? 'tt-break-col' : '' }}">
                                    <div class="tt-period-name">
                                        @if($isBreak)<i class="fas fa-mug-hot mr-1"></i>@endif
                                        {{ $period->name }}
                                    </div>
                                    <div class="tt-period-time">{{ $period->start_time }} - {{ $period->end_time }}</div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                            @php
                                $dayLessonCount = collect($periods)->sum(fn($p) => isset($schedule[$dayKey][$p->period_id]) && ($p->type ?? 'period') !== 'break' ? 1 : 0);
                            @endphp
                            <tr>
                                <td class="tt-day-col">
                                    <div class="tt-day-name">{{ $dayLabel }}</div>
                                    <div class="tt-day-count">{{ $dayLessonCount }} lessons</div>
                                </td>
                                @foreach($periods as $period)
                                    @php
                                        $isBreak = ($period->type ?? 'period') === 'break';
                                        $entry = $schedule[$dayKey][$period->period_id] ?? null;
                                    @endphp
                                    @if($isBreak)
                                        <td class="tt-cell tt-break">
                                            <i class="fas fa-mug-hot"></i>
                                            <span>Break</span>
                                        </td>
                                    @elseif($entry)
                                        <td class="tt-cell tt-lesson">
                                            <div class="tt-subject">{{ $entry->subject->name }}</div>
                                            <div class="tt-teacher">{{ $entry->teacher ? trim(($entry->teacher->first_name ?? '') . ' ' . ($entry->teacher->last_name ?? '')) : 'TBA' }}</div>
                                            <div class="tt-room">Room {{ $entry->classroom->room_number }}</div>
                                            <a href="{{ route('timetables.edit', $entry->timetable_id) }}" class="tt-edit no-print" title="Edit lesson"><i class="fas fa-pen"></i></a>
                                        </td>
                                    @else
                                        <td class="tt-cell tt-free"><span>Free</span></td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
        --amber-bg: #fef3c7;
        --slate: #64748b;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
    }

    .dash-wrap { padding: 2rem 1.5rem; }
    .dash-heading { font-size: 1.65rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em; margin-bottom: 0.15rem; }
    .dash-sub { font-size: 0.9rem; color: var(--muted); font-weight: 500; }

    .dash-panel { border: 1px solid var(--border); border-radius: 16px !important; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04) !important; }

    /* Filters */
    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
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
        -webkit-appearance: none; -moz-appearance: none;
        flex: 1 1 auto; min-width: 0; border: 0; outline: none !important; background-color: transparent;
        font-size: 0.875rem; font-weight: 600; color: var(--text); height: 42px; cursor: pointer;
        padding-right: 24px; appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1.41 0 6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right center;
    }

    /* Stats */
    .stats-strip { display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem 0; }
    .stat-chip { background: #fff; padding: 0.45rem 1rem; border-radius: 20px; border: 1px solid var(--border); font-size: 0.85rem; color: var(--muted); font-weight: 500; }
    .stat-chip strong { color: var(--text); font-weight: 800; }
    .v-divider { width: 1px; height: 20px; background: var(--border); margin: 0 1rem; }

    /* Buttons */
    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; border: 0; cursor: pointer; }
    .btn-dash i { margin-right: 7px; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1; }

    /* - Timetable grid - */
    .tt-table { width: 100%; border-collapse: collapse; min-width: 900px; }
    .tt-table th {
        background: #fafbfd; padding: 0.75rem 0.9rem; text-align: center;
        border-bottom: 2px solid var(--indigo);
        vertical-align: middle;
    }
    .tt-period-name { font-size: 0.78rem; font-weight: 800; color: var(--text); text-transform: uppercase; letter-spacing: 0.05em; }
    .tt-period-time { font-size: 0.68rem; color: var(--muted); font-weight: 600; margin-top: 2px; }
    .tt-break-col { background: var(--amber-bg); }

    .tt-day-col { width: 130px; background: #fafbfd; text-align: left; padding: 0.9rem 1.1rem; border-right: 2px solid var(--border); }
    .tt-day-name { font-size: 0.88rem; font-weight: 800; color: var(--text); }
    .tt-day-count { font-size: 0.68rem; color: var(--muted); font-weight: 600; margin-top: 2px; }

    .tt-cell {
        border: 1px solid #eef2f7; padding: 0.7rem 0.75rem; vertical-align: middle;
        text-align: center; position: relative; height: 76px; transition: background 150ms ease;
    }
    tbody tr:hover .tt-lesson { background: #fafbff; }

    .tt-break { background: var(--amber-bg); color: #92400e; }
    .tt-break i { display: block; font-size: 0.95rem; margin-bottom: 4px; opacity: 0.7; }
    .tt-break span { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }

    .tt-subject { font-size: 0.88rem; font-weight: 800; color: var(--indigo-dark); line-height: 1.25; }
    .tt-teacher { font-size: 0.72rem; color: var(--muted); font-weight: 600; margin-top: 3px; }
    .tt-room { font-size: 0.66rem; color: var(--muted); font-weight: 500; margin-top: 1px; }

    .tt-edit {
        position: absolute; top: 6px; right: 6px; opacity: 0;
        width: 22px; height: 22px; border-radius: 7px; background: var(--indigo-light); color: var(--indigo);
        display: flex; align-items: center; justify-content: center; font-size: 0.62rem;
        transition: opacity 150ms ease, background 150ms ease;
    }
    .tt-lesson:hover .tt-edit { opacity: 1; }
    .tt-edit:hover { background: var(--indigo); color: #fff; text-decoration: none; }

    .tt-free span { font-size: 0.72rem; color: #b6c2d2; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }

    /* Empty state */
    .empty-state { background: #fff; border: 1px dashed var(--border); border-radius: 18px; padding: 4rem 2rem; text-align: center; }
    .empty-icon { width: 74px; height: 74px; margin: 0 auto 1.25rem; border-radius: 20px; background: var(--indigo-light); color: var(--indigo); font-size: 1.6rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto; }

    /* Print-only header */
    .print-header { display: none; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
    }

    /* - PRINT - */
    @media print {
        @page { size: A4 landscape; margin: 12mm; }

        body { background: #fff !important; }
        .main-header, .main-sidebar, .main-footer, .content-header,
        .no-print, .dash-header, form#timetable-filter-form, .stats-strip, .tt-edit { display: none !important; }

        .content-wrapper { margin-left: 0 !important; }
        .dash-wrap { padding: 0 !important; }

        /* Show clean report heading */
        .print-header {
            display: block !important; text-align: center; margin-bottom: 10mm;
        }
        .print-header h2 { font-size: 18pt; font-weight: 800; margin: 0 0 2mm; color: #000; }
        .print-header p { font-size: 11pt; color: #333; margin: 0; }

        .dash-panel { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
        .table-responsive { overflow: visible !important; }

        .tt-table { min-width: 0 !important; width: 100% !important; font-size: 9pt; }
        .tt-table th, .tt-table td { border: 1pt solid #94a3b8 !important; }

        .tt-table th {
            background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;
            border-bottom: 1.5pt solid #000 !important; padding: 2mm 1.5mm;
        }
        .tt-period-name { font-size: 8.5pt; }
        .tt-period-time { font-size: 7.5pt; }

        .tt-day-col { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; border-right: 1.5pt solid #000 !important; }
        .tt-day-name { font-size: 9pt; }
        .tt-day-count { display: none; }

        .tt-cell { height: auto !important; padding: 2.5mm 1.5mm; page-break-inside: avoid; }
        .tt-subject { font-size: 9pt; color: #000 !important; }
        .tt-teacher { font-size: 7.5pt; color: #444 !important; }
        .tt-room { font-size: 7pt; color: #555 !important; }
        .tt-break { background: #fef9c3 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; color: #713f12 !important; }
        .tt-free span { color: #999 !important; }

        tr { page-break-inside: avoid; }
    }
</style>

@push('page_scripts')
<script>
(function () {
    'use strict';

    var academicYearSelect  = document.getElementById('academic_year_id');
    var classSectionSelect  = document.getElementById('class_section_id');
    var loadingIndicator    = document.getElementById('class-section-loading');
    var filterForm          = document.getElementById('timetable-filter-form');

    function loadClassSections(academicYearId, preselectId, autoSubmit) {
        if (!academicYearId) {
            resetClassSectionDropdown('- Select academic year first -');
            return;
        }

        classSectionSelect.disabled = true;
        loadingIndicator.classList.remove('d-none');
        resetClassSectionDropdown('Loading...');

        var url = '{{ url("api/academic-years") }}/' + encodeURIComponent(academicYearId) + '/class-sections';

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }
            return response.json();
        })
        .then(function (sections) {
            classSectionSelect.innerHTML = '';

            if (!sections || sections.length === 0) {
                var opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No classes found for this academic year';
                classSectionSelect.appendChild(opt);
            } else {
                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select class and section';
                classSectionSelect.appendChild(placeholder);

                sections.forEach(function (section) {
                    var opt = document.createElement('option');
                    opt.value = section.id;
                    opt.textContent = section.label;
                    if (preselectId && String(section.id) === String(preselectId)) {
                        opt.selected = true;
                    }
                    classSectionSelect.appendChild(opt);
                });

                if (!preselectId && sections.length > 0) {
                    classSectionSelect.options[1].selected = true;
                }
            }

            classSectionSelect.disabled = false;
            loadingIndicator.classList.add('d-none');

            if (autoSubmit) {
                filterForm.submit();
            }
        })
        .catch(function (err) {
            console.error('Failed to load class sections:', err);
            resetClassSectionDropdown('Error loading classes - please try again');
            classSectionSelect.disabled = false;
            loadingIndicator.classList.add('d-none');
        });
    }

    function resetClassSectionDropdown(message) {
        classSectionSelect.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = message;
        classSectionSelect.appendChild(opt);
    }

    academicYearSelect.addEventListener('change', function () {
        loadClassSections(this.value, null, true);
    });
})();
</script>
@endpush
@endsection
