@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">
                @if($isAdmin && $viewingStaff && $viewingStaff->staff_id != ($staff->staff_id ?? 0))
                    {{ $viewingStaff->full_name }}'s Schedule
                @else
                    My Teaching Schedule
                @endif
            </h1>
            <p class="dash-sub">
                {{ $viewingStaff->designation ?? 'Academic Staff' }}
                @if(isset($viewingStaff->department) && $viewingStaff->department)
                    · {{ $viewingStaff->department->name }}
                @endif
            </p>
        </div>
        <div class="col-md-5 text-md-right mt-2 mt-md-0">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                <button onclick="window.print()" class="btn-dash btn-ghost">
                    <i class="fas fa-print mr-2"></i> Print Schedule
                </button>
                @if($isAdmin && $viewingStaff)
                <a href="{{ route('timetables.index') }}" class="btn-dash btn-primary-dash">
                    <i class="fas fa-plus mr-2"></i> Manage Lessons
                </a>
                @endif
            </div>
        </div>
    </div>

    @include('flash::message')

    {{-- ② FILTERS --}}
    <div class="dash-panel mb-4">
        <div class="dash-panel-body p-3">
            {!! Form::open(['route' => 'timetables.teacher', 'method' => 'GET', 'class' => 'row g-3 align-items-end']) !!}
                @if($isAdmin)
                    <div class="col-lg-4 col-md-6">
                        <label class="filter-label">Teacher</label>
                        <select name="staff_id" class="filter-input" onchange="this.form.submit()">
                            @foreach($allTeachers as $t)
                                <option value="{{ $t->staff_id }}" {{ $viewingStaff && $viewingStaff->staff_id == $t->staff_id ? 'selected' : '' }}>
                                    {{ $t->full_name }} ({{ $t->employee_number }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-lg-3 col-md-6">
                    <label class="filter-label">Academic Year</label>
                    {!! Form::select('academic_year_id', $academicYearOptions, $selectedAcademicYearId, ['class' => 'filter-input', 'onchange' => 'this.form.submit()']) !!}
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="filter-label d-none d-md-block">&nbsp;</label>
                    <a href="{{ route('timetables.teacher') }}" class="btn-dash btn-ghost w-100 py-2">
                        <i class="fas fa-undo mr-2"></i> Reset
                    </a>
                </div>
            {!! Form::close() !!}
        </div>
    </div>

    {{-- When no staff member can be resolved, show a friendly empty state --}}
    @if(!$viewingStaff)
        <div class="tt-empty" style="padding: 3rem 1rem; margin-bottom: 1rem;">
            <i class="fas fa-user-slash"></i>
            <p style="font-weight: 800; color: var(--text); margin-bottom: 0.25rem;">
                @if($allTeachers->isEmpty())
                    No teaching staff found.
                @else
                    Teacher records not found.
                @endif
            </p>
            <p style="margin: 0;">Please contact your administrator or select a teacher from the filter above.</p>
        </div>
    @endif

    {{-- ③ METRIC CARDS --}}
    @php
        $teachingPeriods = $periods->where('type', 'period')->count();
        $freeCount = max(0, (count($daysOfWeek) * $teachingPeriods) - $timetables->count());
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="dash-panel h-100 p-4 text-center">
                <div class="w-icon-box bg-blue-light text-blue mb-3 mx-auto"><i class="fas fa-book-open"></i></div>
                <h3 class="w-value">{{ $timetables->count() }}</h3>
                <p class="w-label">Weekly Lessons</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dash-panel h-100 p-4 text-center">
                <div class="w-icon-box bg-emerald-light text-emerald mb-3 mx-auto"><i class="fas fa-calendar-check"></i></div>
                <h3 class="w-value">{{ $todayClasses->count() }}</h3>
                <p class="w-label">Today</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dash-panel h-100 p-4 text-center">
                <div class="w-icon-box bg-amber-light text-amber mb-3 mx-auto"><i class="fas fa-clock"></i></div>
                <h3 class="w-value">{{ number_format($timetables->count() * 0.75, 1) }}</h3>
                <p class="w-label">Credit Hours</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="dash-panel h-100 p-4 text-center">
                <div class="w-icon-box bg-rose-light text-rose mb-3 mx-auto"><i class="fas fa-hourglass-half"></i></div>
                <h3 class="w-value">{{ $freeCount }}</h3>
                <p class="w-label">Free Slots</p>
            </div>
        </div>
    </div>

    {{-- ④ WEEKLY GRID (desktop) --}}
    <div class="dash-panel mb-4 d-none d-lg-block">
        <div class="dash-panel-header">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-th-large text-indigo"></i>
                <h3 class="dash-panel-title">Weekly Grid</h3>
            </div>
            <span class="badge-count">{{ $timetables->count() }} Lessons</span>
        </div>
        <div class="table-responsive">
            <table class="tt-grid mb-0">
                <thead>
                    <tr>
                        <th class="tt-time-col">Time</th>
                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                            @php $isToday = strtolower(now()->format('l')) == $dayKey; @endphp
                            <th class="tt-day {{ $isToday ? 'tt-today' : '' }}">
                                <span class="tt-day-name">{{ $dayLabel }}</span>
                                @if($isToday) <span class="tt-today-pill">Today</span> @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($periods as $period)
                        @php
                            $isBreak = ($period->type ?? 'period') === 'break';
                            $pStart = \Carbon\Carbon::parse($period->start_time);
                            $pEnd = \Carbon\Carbon::parse($period->end_time);
                            $isNow = !$isBreak && now()->between($pStart, $pEnd);
                        @endphp
                        <tr class="{{ $isBreak ? 'tt-break-row' : '' }}">
                            <td class="tt-time-col {{ $isNow ? 'tt-now' : '' }}">
                                <div class="tt-period-name">{{ $period->name }}</div>
                                <div class="tt-period-time">{{ $pStart->format('h:i A') }}</div>
                                @if($isNow) <span class="tt-now-pill">Now</span> @endif
                            </td>
                            @foreach($daysOfWeek as $dayKey => $dayLabel)
                                @php
                                    $entry = $schedule[$dayKey][$period->period_id] ?? null;
                                    $isToday = strtolower(now()->format('l')) == $dayKey;
                                @endphp
                                <td class="tt-cell {{ $isToday ? 'tt-cell-today' : '' }}">
                                    @if($isBreak)
                                        <div class="tt-break">
                                            <i class="fas fa-mug-hot"></i>
                                            <span>Break</span>
                                        </div>
                                    @elseif($entry)
                                        <div class="tt-lesson" style="--subj: {{ $entry->subject_id % 6 }}">
                                            <div class="tt-lesson-top">
                                                <span class="tt-class">{{ $entry->classSection->class->name ?? '' }} {{ $entry->classSection->section->name ?? '' }}</span>
                                                @if($isAdmin)
                                                    <a href="{{ route('timetables.edit', $entry->timetable_id) }}" class="tt-edit" title="Edit lesson">
                                                        <i class="fas fa-pen"></i>
                                                    </a>
                                                @endif
                                            </div>
                                            <div class="tt-subject">{{ $entry->subject->name }}</div>
                                            <div class="tt-room"><i class="fas fa-map-marker-alt"></i> {{ $entry->classroom->room_number ?? 'Room' }}</div>
                                        </div>
                                    @else
                                        <div class="tt-free"></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ⑤ MOBILE VIEW: day tabs + cards --}}
    <div class="d-lg-none mb-4">
        <div class="tt-mobile-tabs mb-3">
            @foreach($daysOfWeek as $dayKey => $dayLabel)
                @php $isToday = strtolower(now()->format('l')) == $dayKey; @endphp
                <button type="button" class="tt-day-tab {{ $isToday ? 'active' : '' }}" data-day="{{ $dayKey }}">
                    {{ $dayLabel }}
                </button>
            @endforeach
        </div>

        <div id="tt-mobile-panels">
            @foreach($daysOfWeek as $dayKey => $dayLabel)
                @php
                    $isToday = strtolower(now()->format('l')) == $dayKey;
                    $dayLessons = collect();
                    foreach ($periods as $period) {
                        $entry = $schedule[$dayKey][$period->period_id] ?? null;
                        if ($entry) $dayLessons->push($entry);
                    }
                @endphp
                <div class="tt-day-panel {{ $isToday ? '' : 'd-none' }}" data-panel="{{ $dayKey }}">
                    @forelse($dayLessons as $entry)
                        <div class="tt-mobile-card" style="--subj: {{ $entry->subject_id % 6 }}">
                            <div class="tt-mobile-time">
                                <span class="tt-mobile-start">{{ \Carbon\Carbon::parse($entry->period->start_time)->format('h:i') }}</span>
                                <span class="tt-mobile-end">{{ \Carbon\Carbon::parse($entry->period->end_time)->format('h:i A') }}</span>
                            </div>
                            <div class="tt-mobile-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <span class="tt-class">{{ $entry->classSection->class->name ?? '' }} {{ $entry->classSection->section->name ?? '' }}</span>
                                    <span class="tt-room"><i class="fas fa-map-marker-alt"></i> {{ $entry->classroom->room_number ?? '-' }}</span>
                                </div>
                                <div class="tt-subject">{{ $entry->subject->name }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="tt-empty">
                            <i class="fas fa-calendar-day"></i>
                            <p>No classes scheduled for {{ $dayLabel }}.</p>
                        </div>
                    @endforelse
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
/* ── Design tokens (matches Teacher Workload / Academic Dashboard) ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --blue: #3b82f6; --blue-light: #eff6ff;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a; --muted: #64748b;
    --border: #e2e8f0; --border-soft: #f1f5f9;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
    --subj-0: #4f46e5; --subj-0-soft: #eef2ff;
    --subj-1: #0d9488; --subj-1-soft: #f0fdfa;
    --subj-2: #d97706; --subj-2-soft: #fffbeb;
    --subj-3: #dc2626; --subj-3-soft: #fef2f2;
    --subj-4: #7c3aed; --subj-4-soft: #f5f3ff;
    --subj-5: #0369a1; --subj-5-soft: #f0f9ff;
}

.bg-blue-light { background: var(--blue-light); } .text-blue { color: var(--blue); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }
.text-indigo { color: var(--indigo); }

.dash-wrap { padding: 1.25rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.5rem; font-weight: 850; color: var(--text); letter-spacing: -0.04em; margin-bottom: 0.125rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; }

.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03); }
.dash-panel-header { padding: 0.875rem 1.25rem; border-bottom: 1px solid var(--border-soft); display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: 0.875rem; font-weight: 800; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.25rem; }

.w-icon-box { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
.w-value { font-size: 1.375rem; font-weight: 850; margin: 0; letter-spacing: -0.02em; color: var(--text); }
.w-label { font-size: 0.7rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.05em; margin: 0.125rem 0 0; }

.filter-label { font-size: 0.65rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
.filter-input { width: 100%; border-radius: 8px; border: 1px solid var(--border); background: #fff; padding: 0.5rem 0.875rem; font-size: 0.813rem; font-weight: 600; color: var(--text); transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out); appearance: none; background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 0.875rem center; background-size: 10px; padding-right: 2.25rem; }
.filter-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); outline: none; }

.btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 0.813rem; font-weight: 750; text-decoration: none !important; cursor: pointer; transition: transform 160ms var(--ease-out), background-color 160ms var(--ease-out), border-color 160ms var(--ease-out); }
.btn-dash:active { transform: scale(0.97); }
.btn-primary-dash { background: var(--indigo); color: #fff; padding: 0.5rem 1rem; border: 1px solid var(--indigo); }
.btn-ghost { background: #fff; border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; }
@media (hover: hover) and (pointer: fine) {
    .btn-primary-dash:hover { background: #4338ca; border-color: #4338ca; color: #fff; }
    .btn-ghost:hover { background: var(--slate-light); color: var(--text); }
}

.badge-count { background: var(--indigo-light); color: var(--indigo); font-size: 0.688rem; font-weight: 800; padding: 0.25rem 0.5rem; border-radius: 6px; }
.gap-2 { gap: 0.5rem; }
.g-3 > * { margin-bottom: 1rem; }

/* ── Weekly grid ── */
.tt-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
.tt-grid thead th { background: #f8fafc; font-size: 0.688rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slate); padding: 0.75rem 0.5rem; border-bottom: 1px solid var(--border); }
.tt-grid tbody td { padding: 0.5rem; border-bottom: 1px solid var(--border-soft); border-right: 1px solid var(--border-soft); vertical-align: top; }
.tt-grid tbody tr:last-child td { border-bottom: 0; }
.tt-grid tbody td:last-child { border-right: 0; }

.tt-time-col { width: 96px; background: #fcfcfd; text-align: center; vertical-align: middle !important; position: relative; }
.tt-period-name { font-size: 0.75rem; font-weight: 800; color: var(--text); }
.tt-period-time { font-size: 0.688rem; color: var(--muted); margin-top: 2px; }
.tt-now { background: var(--indigo-light) !important; }
.tt-now-pill { position: absolute; top: 0.375rem; left: 0; right: 0; font-size: 0.563rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--indigo); }

.tt-day { position: relative; text-align: center; }
.tt-grid thead th.tt-today { background: var(--indigo-light); color: var(--indigo); }
.tt-today-pill { display: inline-block; margin-left: 0.375rem; font-size: 0.563rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; background: var(--indigo); color: #fff; border-radius: 4px; padding: 0.1rem 0.375rem; vertical-align: middle; }
.tt-grid tbody td.tt-cell-today { background: #fbfcff; }

.tt-break-row td { background: #fffbeb !important; }
.tt-break { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 84px; color: #b45309; font-size: 0.688rem; font-weight: 700; gap: 0.375rem; }
.tt-break i { font-size: 1rem; opacity: 0.7; }

.tt-lesson { display: flex; flex-direction: column; gap: 0.25rem; height: 100%; min-height: 84px; padding: 0.625rem 0.75rem; border-radius: 10px; background: #fff; border: 1px solid var(--border); border-left: 3px solid var(--subj-0, var(--indigo)); transition: transform 160ms var(--ease-out), box-shadow 160ms var(--ease-out), border-color 160ms var(--ease-out); }
.tt-lesson[style*="--subj: 0"] { border-left-color: var(--subj-0); }
.tt-lesson[style*="--subj: 1"] { border-left-color: var(--subj-1); }
.tt-lesson[style*="--subj: 2"] { border-left-color: var(--subj-2); }
.tt-lesson[style*="--subj: 3"] { border-left-color: var(--subj-3); }
.tt-lesson[style*="--subj: 4"] { border-left-color: var(--subj-4); }
.tt-lesson[style*="--subj: 5"] { border-left-color: var(--subj-5); }
@media (hover: hover) and (pointer: fine) {
    .tt-lesson:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08); }
}
.tt-lesson-top { display: flex; align-items: center; justify-content: space-between; gap: 0.25rem; }
.tt-class { font-size: 0.625rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; color: var(--slate); background: var(--slate-light); border-radius: 5px; padding: 0.15rem 0.4rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tt-subject { font-size: 0.813rem; font-weight: 800; color: var(--text); line-height: 1.2; }
.tt-room { font-size: 0.688rem; color: var(--muted); display: flex; align-items: center; gap: 0.25rem; }
.tt-edit { color: #94a3b8; font-size: 0.625rem; transition: color 150ms var(--ease-out); }
@media (hover: hover) and (pointer: fine) { .tt-edit:hover { color: var(--indigo); } }

.tt-free { height: 100%; min-height: 84px; border: 1px dashed var(--border); border-radius: 8px; }

/* ── Mobile view ── */
.tt-mobile-tabs { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.25rem; -webkit-overflow-scrolling: touch; }
.tt-day-tab { flex-shrink: 0; border: 1px solid var(--border); background: #fff; color: var(--slate); border-radius: 8px; padding: 0.5rem 1rem; font-size: 0.75rem; font-weight: 750; cursor: pointer; transition: transform 160ms var(--ease-out), background-color 160ms var(--ease-out), color 160ms var(--ease-out), border-color 160ms var(--ease-out); }
.tt-day-tab:active { transform: scale(0.96); }
.tt-day-tab.active { background: var(--indigo); border-color: var(--indigo); color: #fff; }

.tt-day-panel { animation: ttFade 200ms var(--ease-out); }
@keyframes ttFade { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

.tt-mobile-card { display: flex; background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; margin-bottom: 0.75rem; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03); }
.tt-mobile-time { flex-shrink: 0; width: 72px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f8fafc; border-right: 1px solid var(--border-soft); gap: 0.125rem; }
.tt-mobile-start { font-size: 0.875rem; font-weight: 800; color: var(--text); }
.tt-mobile-end { font-size: 0.625rem; color: var(--muted); }
.tt-mobile-body { flex: 1; padding: 0.75rem 0.875rem; border-left: 3px solid var(--subj-0, var(--indigo)); }
.tt-mobile-body[style*="--subj: 0"] { border-left-color: var(--subj-0); }
.tt-mobile-body[style*="--subj: 1"] { border-left-color: var(--subj-1); }
.tt-mobile-body[style*="--subj: 2"] { border-left-color: var(--subj-2); }
.tt-mobile-body[style*="--subj: 3"] { border-left-color: var(--subj-3); }
.tt-mobile-body[style*="--subj: 4"] { border-left-color: var(--subj-4); }
.tt-mobile-body[style*="--subj: 5"] { border-left-color: var(--subj-5); }

.tt-empty { background: #fff; border: 1px dashed var(--border); border-radius: 12px; text-align: center; padding: 2.5rem 1rem; color: var(--muted); }
.tt-empty i { font-size: 1.5rem; opacity: 0.4; margin-bottom: 0.5rem; }
.tt-empty p { margin: 0; font-size: 0.813rem; font-weight: 600; }

/* ── Print ── */
@media print {
    @page { size: A4 landscape; margin: 10mm; }
    .dash-wrap { padding: 0; background: #fff; min-height: 0; }
    .btn-dash, .tt-mobile-tabs, .d-lg-none, .dash-panel form, .dash-panel-header .badge-count { display: none !important; }
    .dash-panel { border: 1px solid #e2e8f0 !important; box-shadow: none !important; }
    .tt-grid thead th { background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .tt-today, .tt-today .tt-day-name { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .tt-break-row td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .content-wrapper { margin-left: 0 !important; }
    .main-sidebar, .main-header, .main-footer { display: none !important; }
}

@media (prefers-reduced-motion: reduce) {
    .tt-lesson, .btn-dash, .tt-day-tab, .tt-edit { transition: none; }
    .tt-day-panel { animation: none; }
    .tt-lesson:hover { transform: none; box-shadow: none; }
}
</style>

@push('page_scripts')
<script>
(function () {
    'use strict';

    // Day tab switching (dependency-free — no jQuery, works with deferred bundles)
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.tt-day-tab'));
    var panels = Array.prototype.slice.call(document.querySelectorAll('.tt-day-panel'));

    function activate(dayKey) {
        tabs.forEach(function (tab) {
            tab.classList.toggle('active', tab.getAttribute('data-day') === dayKey);
        });
        panels.forEach(function (panel) {
            panel.classList.toggle('d-none', panel.getAttribute('data-panel') !== dayKey);
        });
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            activate(tab.getAttribute('data-day'));
        });
    });

    // Jump to a day from the query string if present
    var params = new URLSearchParams(window.location.search);
    var dayParam = params.get('day');
    if (dayParam && panels.some(function (p) { return p.getAttribute('data-panel') === dayParam; })) {
        activate(dayParam);
    }
})();
</script>
@endpush
@endsection
