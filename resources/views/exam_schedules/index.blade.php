@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-calendar-check mr-2" style="color: var(--indigo);"></i>Exam Timetables</h1>
            <p class="dash-sub mb-0">Sitting schedule for every exam session</p>
        </div>
        <div class="d-flex mt-3 mt-md-0">
            <a class="btn-dash btn-ghost mr-2" href="{{ route('exam-schedules.auto-generate', $examId ? ['exam_id' => $examId] : []) }}">
                <i class="fas fa-magic"></i> Auto-Generate
            </a>
            <a class="btn-dash btn-primary-dash shadow-sm" href="{{ route('exam-schedules.create') }}">
                <i class="fas fa-plus"></i> Add Sitting
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- FILTER --}}
    <div class="card dash-panel mb-4">
        <div class="card-body p-4">
            <form action="{{ route('exam-schedules.index') }}" method="GET" id="exam-filter-form">
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-6 mb-2 mb-lg-0">
                        <label class="filter-label">Exam Session</label>
                        <div class="filter-field">
                            <i class="fas fa-file-signature"></i>
                            <select name="exam_id" onchange="this.form.submit()">
                                <option value="">Select an exam session...</option>
                                @foreach($exams as $id => $name)
                                    <option value="{{ $id }}" {{ $examId == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex justify-content-lg-end align-items-center">
                        @if($examId)
                            <button type="button" onclick="window.print()" class="btn-dash btn-primary-dash mr-2">
                                <i class="fas fa-print"></i> Print Timetable
                            </button>
                            <a href="{{ route('exam-schedules.index') }}" class="btn-dash btn-ghost">Reset</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($selectedExam)
        @php
            $uniqueDates = $selectedExam->examSchedules
                ->map(fn ($s) => \Carbon\Carbon::parse($s->exam_date)->format('Y-m-d'))
                ->unique()->sort()->values();

            $uniqueClasses = $selectedExam->examSchedules
                ->map(fn ($s) => $s->class)->filter()
                ->unique('class_id')
                ->sortBy(fn ($c) => [$c->numeric_value ?? 0, $c->name])
                ->values();

            $grid = [];
            foreach ($selectedExam->examSchedules as $s) {
                $d = \Carbon\Carbon::parse($s->exam_date)->format('Y-m-d');
                $grid[$d][$s->class_id][] = $s;
            }
        @endphp

        {{-- CONTEXT STRIP --}}
        <div class="stats-strip mb-4 px-1">
            <div class="stat-chip"><i class="fas fa-file-signature mr-1"></i><strong>{{ $selectedExam->name }}</strong></div>
            <span class="v-divider"></span>
            <div class="stat-chip"><strong>{{ $uniqueDates->count() }}</strong> Exam Days</div>
            <span class="v-divider"></span>
            <div class="stat-chip"><strong>{{ $uniqueClasses->count() }}</strong> Classes</div>
            <span class="v-divider"></span>
            <div class="stat-chip"><strong>{{ $selectedExam->examSchedules->count() }}</strong> Sittings</div>
        </div>

        {{-- MASTER EXAM TIMETABLE --}}
        <div class="card dash-panel overflow-hidden">
            <div class="table-responsive">
                <table class="extt-table" id="exam-timetable">
                    <thead>
                        <tr>
                            <th class="extt-date-col">Date / Day</th>
                            @foreach($uniqueClasses as $class)
                                <th>{{ $class->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uniqueDates as $date)
                            <tr>
                                <td class="extt-date-col">
                                    <div class="extt-day">{{ \Carbon\Carbon::parse($date)->format('l') }}</div>
                                    <div class="extt-date">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</div>
                                </td>
                                @foreach($uniqueClasses as $class)
                                    @php $entries = collect($grid[$date][$class->class_id] ?? []); @endphp
                                    <td class="extt-cell {{ $entries->isEmpty() ? 'extt-free' : '' }}">
                                        @foreach($entries as $schedule)
                                            <div class="extt-item">
                                                <div class="extt-subject">{{ $schedule->subject->name ?? '-' }}</div>
                                                <div class="extt-time">
                                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                                </div>
                                                <div class="extt-room">Room {{ $schedule->room->room_number ?? '-' }}</div>
                                                <a href="{{ route('exam-schedules.edit', $schedule->schedule_id) }}" class="extt-edit no-print" title="Edit sitting">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                        @if($entries->isEmpty())
                                            <span>No Exam</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $uniqueClasses->count() + 1 }}">
                                    <div class="empty-state" style="border: none;">
                                        <div class="empty-icon"><i class="far fa-calendar-times"></i></div>
                                        <h4 class="empty-title">No Sittings Scheduled</h4>
                                        <p class="empty-desc">Use Auto-Generate or add sittings manually for this exam.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <div class="empty-state">
            <div class="empty-icon"><i class="far fa-calendar-alt"></i></div>
            <h4 class="empty-title">Select an Exam Session</h4>
            <p class="empty-desc">Choose an exam above to view its full sitting timetable across all classes - or auto-generate one.</p>
        </div>
    @endif
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
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

    /* Filter */
    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
    .filter-field {
        display: flex; align-items: center; width: 100%; min-height: 44px;
        border: 1px solid var(--border); border-radius: 12px; background-color: #fff;
        padding: 0 12px; transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
        cursor: pointer;
    }
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
    .btn-ghost { background: #fff; border: 1px solid var(--border) !important; color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1 !important; }

    /* - Exam timetable table - */
    .extt-table { width: 100%; border-collapse: collapse; min-width: 760px; }
    .extt-table th {
        background: #fafbfd; padding: 0.85rem 1rem; text-align: center;
        font-size: 0.78rem; font-weight: 800; color: var(--text);
        text-transform: uppercase; letter-spacing: 0.05em;
        border-bottom: 2px solid var(--indigo);
    }
    .extt-date-col { width: 150px; background: #fafbfd; text-align: left !important; padding: 0.9rem 1.1rem; border-right: 2px solid var(--border); }
    .extt-day { font-size: 0.88rem; font-weight: 800; color: var(--text); }
    .extt-date { font-size: 0.72rem; color: var(--slate); font-weight: 700; margin-top: 2px; }

    .extt-cell {
        border: 1px solid #eef2f7; padding: 0.65rem 0.75rem; vertical-align: top;
        text-align: center; position: relative; min-width: 140px;
    }
    tbody tr:hover td.extt-cell:not(.extt-free) { background: #fafbff; }

    .extt-free span { font-size: 0.72rem; color: #b6c2d2; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }

    .extt-item {
        background: #fff; border: 1px solid var(--border); border-left: 3px solid var(--indigo);
        border-radius: 10px; padding: 0.55rem 0.6rem; margin-bottom: 0.5rem;
        position: relative; text-align: left;
    }
    .extt-item:last-child { margin-bottom: 0; }
    .extt-subject { font-size: 0.82rem; font-weight: 800; color: var(--indigo-dark); line-height: 1.25; padding-right: 18px; }
    .extt-time { font-size: 0.71rem; color: var(--muted); font-weight: 600; margin-top: 3px; }
    .extt-room { font-size: 0.68rem; color: var(--muted); font-weight: 500; margin-top: 1px; }

    .extt-edit {
        position: absolute; top: 6px; right: 6px; opacity: 0;
        width: 22px; height: 22px; border-radius: 7px; background: var(--indigo-light); color: var(--indigo);
        display: flex; align-items: center; justify-content: center; font-size: 0.62rem;
        transition: opacity 150ms ease, background 150ms ease;
    }
    .extt-item:hover .extt-edit { opacity: 1; }
    .extt-edit:hover { background: var(--indigo); color: #fff; text-decoration: none; }

    /* Empty state */
    .empty-state { padding: 3.5rem 2rem; text-align: center; }
    .empty-icon { width: 74px; height: 74px; margin: 0 auto 1.25rem; border-radius: 20px; background: var(--indigo-light); color: var(--indigo); font-size: 1.6rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; margin-bottom: 0.25rem; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
    }

    /* - PRINT: clean landscape table - */
    @media print {
        @page { size: A4 landscape; margin: 10mm; }

        body { background: #fff !important; }
        .main-sidebar, .main-header, .main-footer, .content-header,
        .no-print, form, .stats-strip, .extt-edit { display: none !important; }

        .content-wrapper { margin-left: 0 !important; }
        .dash-wrap { padding: 0 !important; }

        /* Report heading */
        .dash-heading, .dash-sub { display: none; }
        .dash-header { display: block !important; margin-bottom: 6mm !important; text-align: center; }
        .dash-header::before {
            content: "{{ config('app.name') }} - Exam Timetable";
            display: block; font-size: 16pt; font-weight: 800; color: #000; margin-bottom: 2mm;
        }

        .dash-panel { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
        .table-responsive { overflow: visible !important; }

        .extt-table { min-width: 0 !important; width: 100% !important; font-size: 9pt; }
        .extt-table th, .extt-table td { border: 1pt solid #64748b !important; }

        .extt-table th {
            background: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;
            border-bottom: 1.5pt solid #000 !important; padding: 2mm 1.5mm; color: #000 !important;
        }
        .extt-date-col { background: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; border-right: 1.5pt solid #000 !important; }
        .extt-day { font-size: 9pt; color: #000 !important; }
        .extt-date { font-size: 7.5pt; color: #444 !important; }

        .extt-cell { page-break-inside: avoid; }
        .extt-item { border: none !important; border-bottom: 0.5pt solid #cbd5e1 !important; border-radius: 0 !important; box-shadow: none !important; background: transparent !important; margin-bottom: 1mm; padding: 1mm 0; }
        .extt-item:last-child { border-bottom: none !important; }
        .extt-subject { font-size: 9pt; font-weight: 700; color: #000 !important; }
        .extt-time { font-size: 7.5pt; color: #333 !important; }
        .extt-room { font-size: 7pt; color: #555 !important; }
        .extt-free span { color: #999 !important; }

        tr { page-break-inside: avoid; }
    }
</style>
@endsection
