@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-magic mr-2" style="color: var(--indigo);"></i>Auto-Generate Exam Timetable</h1>
            <p class="dash-sub mb-0">Build the exam sitting schedule from a few parameters</p>
        </div>
        <div class="mt-3 mt-md-0">
            <a href="{{ route('exam-schedules.index') }}" class="btn-dash btn-ghost">Back to Schedules</a>
        </div>
    </div>

    @include('flash::message')

    @if($exams->isEmpty())
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-file-signature"></i></div>
            <h4 class="empty-title">No Exams Yet</h4>
            <p class="empty-desc">Create an exam first, then come back to auto-generate its timetable.</p>
            <a href="{{ route('exams.create') }}" class="btn-dash btn-primary-dash mt-2">Create Exam</a>
        </div>
    @else
        {{-- STEP 1: pick the exam --}}
        <form method="GET" action="{{ route('exam-schedules.auto-generate') }}" class="card dash-panel mb-4">
            <div class="card-body p-4">
                <label class="filter-label mb-3"><span class="step-badge">1</span> Choose Exam Session</label>
                <div class="form-row align-items-end">
                    <div class="form-group col-md-8 mb-2 mb-md-0">
                        <div class="filter-field">
                            <i class="fas fa-file-signature"></i>
                            <select name="exam_id" required>
                                <option value="">Select exam...</option>
                                @foreach($exams as $exam)
                                    <option value="{{ $exam->exam_id }}" {{ $selectedExamId == $exam->exam_id ? 'selected' : '' }}>
                                        {{ $exam->name }} ({{ optional($exam->start_date)->format('d M Y') }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn-dash btn-primary-dash w-100">
                            Continue <i class="fas fa-arrow-right mr-0 ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        @if($selectedExamId && $classes->isNotEmpty())
            {{-- STEP 2: scope + parameters --}}
            <form method="POST" action="{{ route('exam-schedules.auto-generate.store') }}">
                @csrf
                <input type="hidden" name="exam_id" value="{{ $selectedExamId }}">

                <label class="filter-label mb-3"><span class="step-badge">2</span> Scope &amp; Parameters</label>

                <div class="row">
                    {{-- WHO sits the exam --}}
                    <div class="col-lg-6">
                        <div class="card dash-panel mb-4 h-100">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold mb-0"><i class="fas fa-users mr-2 text-indigo"></i>Classes Sitting</h6>
                                <button type="button" class="btn btn-sm btn-light border" data-check-all="class-cb" data-target="#class-grid">Select all</button>
                            </div>
                            <div class="card-body px-4 pb-3" id="class-grid">
                                <div class="row">
                                    @foreach($classes as $class)
                                        <div class="col-sm-6">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" name="classes[]" value="{{ $class->class_id }}" checked class="custom-control-input class-cb" id="cls{{ $class->class_id }}">
                                                <label class="custom-control-label font-weight-600" for="cls{{ $class->class_id }}">{{ $class->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="card dash-panel mb-4 h-100">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                <h6 class="font-weight-bold mb-0"><i class="fas fa-book mr-2 text-indigo"></i>Subjects Included</h6>
                                <button type="button" class="btn btn-sm btn-light border" data-check-all="subject-cb" data-target="#subject-grid">Select all</button>
                            </div>
                            <div class="card-body px-4 pb-3" id="subject-grid">
                                <div class="row">
                                    @foreach($subjects as $subject)
                                        <div class="col-sm-6">
                                            <div class="custom-control custom-checkbox mb-2">
                                                <input type="checkbox" name="subjects[]" value="{{ $subject->subject_id }}" checked class="custom-control-input subject-cb" id="sub{{ $subject->subject_id }}">
                                                <label class="custom-control-label font-weight-600" for="sub{{ $subject->subject_id }}">{{ $subject->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- WHEN & HOW --}}
                    <div class="col-lg-6">
                        <div class="card dash-panel mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h6 class="font-weight-bold mb-0"><i class="far fa-clock mr-2 text-indigo"></i>Schedule Window &amp; Sessions</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="form-row">
                                    <div class="form-group col-md-7">
                                        <label class="filter-label">First Exam Date <span class="text-danger">*</span></label>
                                        <div class="filter-field">
                                            <i class="fas fa-calendar-day"></i>
                                            <input type="date" name="start_date" value="{{ old('start_date', now()->toDateString()) }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-5 d-flex align-items-end pb-md-3">
                                        <div class="custom-control custom-switch custom-switch-on-success">
                                            <input type="checkbox" name="skip_weekends" value="1" class="custom-control-input" id="skipWeekends" checked>
                                            <label class="custom-control-label font-weight-bold" for="skipWeekends">Skip weekends</label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-8">
                                        <label class="filter-label">Sessions Per Day</label>
                                        <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                            <label class="btn btn-outline-secondary active">
                                                <input type="radio" name="sessions_per_day" value="1" {{ old('sessions_per_day', '1') == '1' ? 'checked' : '' }}> 1 (Morning only)
                                            </label>
                                            <label class="btn btn-outline-secondary">
                                                <input type="radio" name="sessions_per_day" value="2" {{ old('sessions_per_day') == '2' ? 'checked' : '' }}> 2 (Morning + Afternoon)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-4">
                                        <label class="filter-label">Session Duration (min) <span class="text-danger">*</span></label>
                                        <div class="filter-field">
                                            <i class="fas fa-hourglass-half"></i>
                                            <input type="number" name="session_minutes" value="{{ old('session_minutes', 120) }}" min="30" max="360" step="15" required>
                                        </div>
                                        <small class="text-muted font-weight-normal">Exam length per subject; the end time is computed automatically.</small>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="filter-label">Morning Start</label>
                                        <div class="filter-field">
                                            <i class="fas fa-sun"></i>
                                            <input type="time" name="morning_start" value="{{ old('morning_start', '08:00') }}" required>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="filter-label">Afternoon Start</label>
                                        <div class="filter-field">
                                            <i class="fas fa-cloud-sun"></i>
                                            <input type="time" name="afternoon_start" value="{{ old('afternoon_start', '11:00') }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card dash-panel mb-4">
                            <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                                <h6 class="font-weight-bold mb-0"><i class="fas fa-cogs mr-2 text-indigo"></i>Marks &amp; Options</h6>
                            </div>
                            <div class="card-body px-4 pb-4">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label class="filter-label">Max Marks <span class="text-danger">*</span></label>
                                        <div class="filter-field">
                                            <i class="fas fa-sort-numeric-up"></i>
                                            <input type="number" name="max_marks" value="{{ old('max_marks', 100) }}" min="1" max="1000" step="1" required>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label class="filter-label">Passing Marks <span class="text-danger">*</span></label>
                                        <div class="filter-field">
                                            <i class="fas fa-check-double"></i>
                                            <input type="number" name="passing_marks" value="{{ old('passing_marks', 50) }}" min="0" step="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="custom-control custom-switch custom-switch-on-success mb-2">
                                            <input type="checkbox" name="assign_rooms" value="1" class="custom-control-input" id="assignRooms" checked>
                                            <label class="custom-control-label font-weight-bold" for="assignRooms">Auto-assign classrooms (round-robin)</label>
                                        </div>
                                        <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                            <input type="checkbox" name="replace_existing" value="1" class="custom-control-input" id="replaceExisting" checked>
                                            <label class="custom-control-label font-weight-bold text-danger" for="replaceExisting">Replace existing schedule for this exam</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-dash btn-primary-dash px-5 mb-4"
                                onclick="return confirm('Generate the exam timetable now? Existing schedules for this exam will be replaced if that option is ticked.')">
                            <i class="fas fa-magic mr-1"></i> Generate Timetable
                        </button>
                    </div>
                </div>
            </form>
        @elseif($selectedExamId)
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-users"></i></div>
                <h4 class="empty-title">No Classes Found</h4>
                <p class="empty-desc">This exam's academic year has no classes with sections yet.</p>
            </div>
        @endif
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
    .text-indigo { color: var(--indigo); }

    .step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 24px; height: 24px; border-radius: 8px;
        background: var(--indigo); color: #fff; font-weight: 800; font-size: 0.8rem;
        margin-right: 9px; vertical-align: middle;
    }

    .filter-label { font-size: 0.68rem; font-weight: 800; color: var(--slate); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.5rem; display: block; }
    .font-weight-600 { font-weight: 600; }
    .filter-field {
        display: flex; align-items: center; width: 100%; min-height: 44px;
        border: 1px solid var(--border); border-radius: 12px; background-color: #fff;
        padding: 0 12px; transition: border-color 200ms var(--ease-out), box-shadow 200ms var(--ease-out);
    }
    .filter-field:focus-within { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .filter-field > i { color: var(--muted); font-size: 0.85rem; margin-right: 11px; min-width: 15px; text-align: center; }
    .filter-field > select,
    .filter-field > input {
        -webkit-appearance: none; -moz-appearance: none;
        flex: 1 1 auto; min-width: 0; border: 0; outline: none !important; background-color: transparent;
        font-size: 0.875rem; font-weight: 600; color: var(--text); height: 42px;
    }
    .filter-field > select { cursor: pointer; padding-right: 24px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath fill='%2364748b' d='M1.41 0 6 4.58 10.59 0 12 1.41l-6 6-6-6z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right center; }

    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; border: 0; cursor: pointer; }
    .btn-dash i { margin-right: 7px; }
    .btn-dash i.mr-0 { margin-right: 0 !important; margin-left: 7px; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-ghost { background: #fff; border: 1px solid var(--border) !important; color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; border-color: #cbd5e1 !important; }

    .empty-state { background: #fff; border: 1px dashed var(--border); border-radius: 18px; padding: 4rem 2rem; text-align: center; }
    .empty-icon { width: 74px; height: 74px; margin: 0 auto 1.25rem; border-radius: 20px; background: var(--indigo-light); color: var(--indigo); font-size: 1.6rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); letter-spacing: -0.02em; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto 0.5rem; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
    }
</style>

@push('page_scripts')
<script>
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-check-all]');
    if (!btn) return;

    var selector = '.' + btn.getAttribute('data-check-all');
    var boxes = document.querySelectorAll(selector);
    if (!boxes.length) return;

    // If every box is already ticked, untick them all; otherwise tick all.
    var allChecked = Array.prototype.every.call(boxes, function (cb) { return cb.checked; });
    boxes.forEach(function (cb) { cb.checked = !allChecked; });
});
</script>
@endpush
@endsection
