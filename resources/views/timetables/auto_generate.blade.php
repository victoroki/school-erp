@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Auto-Generate Timetable</h1>
            <p class="dash-sub">Follow the steps below to configure and generate a weekly timetable</p>
        </div>
        <div class="col-md-5 text-md-right mt-2 mt-md-0">
            <a href="{{ route('timetables.index') }}" class="btn-dash btn-ghost">
                <i class="fas fa-arrow-left mr-2"></i> Back to Timetables
            </a>
        </div>
    </div>

    @include('flash::message')

    {{-- ═══════════════════════════════════════════════════════════
         HORIZONTAL STEPPER BAR
         ═══════════════════════════════════════════════════════════ --}}
    @php
        $hasResult = $result !== null;
    @endphp
    <div class="stepper-bar mb-4" id="stepper-bar">
        <div class="stepper-item active" data-step="1">
            <div class="stepper-circle">1</div>
            <div class="stepper-label">Academic Year</div>
        </div>
        <div class="stepper-line {{ $selectedAcademicYearId ? 'completed' : '' }}"></div>
        <div class="stepper-item {{ $selectedAcademicYearId ? 'active' : 'pending' }}" data-step="2">
            <div class="stepper-circle">2</div>
            <div class="stepper-label">Periods per Subject</div>
        </div>
        <div class="stepper-line {{ $hasResult ? 'completed' : '' }}"></div>
        <div class="stepper-item {{ $hasResult ? 'active' : 'pending' }}" data-step="3">
            <div class="stepper-circle">3</div>
            <div class="stepper-label">Review Preview</div>
        </div>
        <div class="stepper-line {{ $hasResult ? 'completed' : '' }}"></div>
        <div class="stepper-item {{ $hasResult ? 'active' : 'pending' }}" data-step="4">
            <div class="stepper-circle">4</div>
            <div class="stepper-label">Week Overrides</div>
        </div>
        <div class="stepper-line {{ $hasResult ? 'completed' : '' }}"></div>
        <div class="stepper-item {{ $hasResult ? 'active' : 'pending' }}" data-step="5">
            <div class="stepper-circle">5</div>
            <div class="stepper-label">Confirm &amp; Save</div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 1 — Academic Year Selector
         ═══════════════════════════════════════════════════════════ --}}
    <div class="step-panel active" data-step-panel="1" id="step-1">
        <div class="dash-panel">
            <div class="dash-panel-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-alt text-indigo"></i>
                    <h3 class="dash-panel-title">Step 1 — Select Academic Year</h3>
                </div>
            </div>
            <div class="dash-panel-body">
                <p class="text-muted mb-3" style="font-size: 0.813rem;">Choose the academic year for which you want to generate a timetable.</p>
                {!! Form::open(['route' => 'timetables.auto-generate', 'method' => 'GET', 'class' => 'row g-3 align-items-end']) !!}
                    <div class="col-lg-5 col-md-7">
                        <label class="filter-label">Academic Year</label>
                        {!! Form::select(
                            'academic_year_id',
                            $academicYearOptions,
                            $selectedAcademicYearId,
                            ['class' => 'filter-input']
                        ) !!}
                    </div>
                    <div class="col-lg-3 col-md-5">
                        <label class="filter-label d-none d-md-block">&nbsp;</label>
                        <button type="submit" class="btn-dash btn-primary-dash w-100 py-2">
                            <i class="fas fa-arrow-right mr-2"></i> Continue
                        </button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 2 — Periods per Subject
         ═══════════════════════════════════════════════════════════ --}}
    @if($selectedAcademicYearId)
    <div class="step-panel {{ $hasResult ? '' : 'active' }}" data-step-panel="2" id="step-2">
        <div class="dash-panel mb-4" id="options-panel">
            <div class="dash-panel-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-sliders-h text-indigo"></i>
                    <h3 class="dash-panel-title">Step 2 — Set Weekly Periods per Subject</h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btnExpandAll" class="btn-dash btn-ghost btn-xs">
                        <i class="fas fa-expand-arrows-alt mr-1"></i> All
                    </button>
                    <button type="button" id="btnCollapseAll" class="btn-dash btn-ghost btn-xs">
                        <i class="fas fa-compress-arrows-alt mr-1"></i> All
                    </button>
                    <span class="badge-count">{{ $classSubjectOptions->count() }} Subjects</span>
                </div>
            </div>
            <div class="dash-panel-body p-0">
                {!! Form::open(['route' => 'timetables.auto-generate.options', 'method' => 'POST', 'id' => 'periods-form']) !!}
                {!! Form::hidden('academic_year_id', $selectedAcademicYearId) !!}

                @if($classSubjectOptions->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-book-open fa-2x mb-3" style="opacity: .3;"></i>
                        <p class="mb-1" style="font-size: .875rem; font-weight: 600; color: var(--text);">No subjects assigned to any class</p>
                        <p class="mb-0" style="font-size: .813rem;">Add subjects under <a href="{{ route('class-subjects.index') }}">Class Curriculum Configuration</a> first.</p>
                    </div>
                @else
                    @php
                        $grouped = $classSubjectOptions->groupBy(fn($cs) => $cs->class->name ?? 'Unassigned');
                    @endphp

                    <div class="accordion-list" id="class-accordion">
                        @foreach($grouped as $className => $items)
                            @php
                                $classId = $items->first()->class_id;
                                $totalPeriods = $items->sum('periods_per_week') ?: $items->count();
                                $subjectCount = $items->count();
                                $classNumeric = $items->first()->class->numeric_value ?? null;
                                $byDept = $items->groupBy(fn($cs) => $cs->subject->department->name ?? 'General');
                            @endphp

                            <div class="acc-section {{ $loop->first ? 'acc-open' : 'acc-closed' }}" data-class-id="{{ $classId }}">
                                <div class="acc-header" tabindex="0" role="button" aria-expanded="{{ $loop->first ? 'true' : 'false' }}">
                                    <div class="acc-header-left">
                                        <span class="acc-chevron"><i class="fas fa-chevron-right"></i></span>
                                        <span class="acc-class-name">{{ $className }}</span>
                                        <span class="badge-count acc-badge">{{ $subjectCount }}{{ $subjectCount === 1 ? ' subject' : ' subjects' }}</span>
                                        <span class="acc-total-badge" data-class="{{ $classId }}">{{ $totalPeriods }} periods/wk</span>
                                    </div>
                                    <div class="acc-header-right">
                                        @if($subjectCount > 0)
                                            <div class="bulk-triggers" onclick="event.stopPropagation()">
                                                <select class="bulk-set-select" data-class="{{ $classId }}" title="Set all subjects in {{ $className }} to N periods">
                                                    <option value="">Set all…</option>
                                                    @foreach([2,3,4,5,6] as $n)
                                                        <option value="{{ $n }}">All → {{ $n }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="acc-body">
                                    @if($subjectCount === 0)
                                        <div class="acc-empty">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            No subjects configured for {{ $className }}.
                                            <a href="{{ route('class-subjects.create') }}">Add subjects</a>
                                        </div>
                                    @else
                                        @foreach($byDept as $deptName => $deptItems)
                                            <div class="dept-group">
                                                <div class="dept-label">{{ $deptName }}</div>
                                                @foreach($deptItems as $cs)
                                                    @php
                                                        $isMismatch = $classNumeric !== null
                                                            && $cs->subject->grade_level !== null
                                                            && (int) $cs->subject->grade_level !== (int) $classNumeric;
                                                    @endphp
                                                    <div class="subject-row {{ $isMismatch ? 'row-warn' : '' }}">
                                                        <div class="subject-info">
                                                            <span class="subject-name">{{ $cs->subject->name ?? 'Subject #' . $cs->subject_id }}</span>
                                                            @if($isMismatch)
                                                                <span class="warn-icon" title="Grade mismatch: subject is grade {{ $cs->subject->grade_level }}, class is grade {{ $classNumeric }}">
                                                                    <i class="fas fa-exclamation-triangle"></i>
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="stepper-wrap">
                                                            <button type="button" class="step-btn step-minus" data-target="periods[{{ $cs->class_subject_id }}]" aria-label="Decrease">−</button>
                                                            <input type="number"
                                                                   id="periods[{{ $cs->class_subject_id }}]"
                                                                   name="periods[{{ $cs->class_subject_id }}]"
                                                                   value="{{ $cs->periods_per_week ?? 1 }}"
                                                                   min="1" max="40"
                                                                   class="step-value"
                                                                   data-class="{{ $classId }}"
                                                                   title="Periods per week">
                                                            <button type="button" class="step-btn step-plus" data-target="periods[{{ $cs->class_subject_id }}]" aria-label="Increase">+</button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                {!! Form::close() !!}
            </div>
        </div>

        {{-- Sticky Footer --}}
        @if($classSubjectOptions->isNotEmpty())
            @php
                $classCount = $grouped->count();
            @endphp
            <div class="sticky-footer" id="sticky-footer">
                <div class="sticky-footer-inner">
                    <div class="sticky-footer-left">
                        <div class="sf-progress">
                            <span class="sf-progress-text">{{ $classCount }} class{{ $classCount !== 1 ? 'es' : '' }} configured</span>
                            <span class="sf-sep">·</span>
                            <span class="sf-subjects">{{ $classSubjectOptions->count() }} subjects</span>
                            <span class="sf-sep">·</span>
                            <span class="sf-periods" id="sf-total-periods">{{ $classSubjectOptions->sum('periods_per_week') ?: $classSubjectOptions->count() }} total periods/wk</span>
                        </div>
                        <p class="sf-hint mb-0">Lessons are spread across the week and never double-book a class, teacher, or room.</p>
                    </div>
                    <div class="sticky-footer-right">
                        <button type="submit" form="periods-form" class="btn-dash btn-primary-dash">
                            <i class="fas fa-wand-magic-sparkles mr-2"></i> Save &amp; Preview
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════
         STEP 3 — Review Preview (only when result exists)
         ═══════════════════════════════════════════════════════════ --}}
    @if($hasResult)
    <div class="step-panel" data-step-panel="3" id="step-3">
        {{-- Navigation --}}
        <div class="step-nav mb-3">
            <button type="button" class="btn-dash btn-ghost btn-step-back" data-target-step="2">
                <i class="fas fa-arrow-left mr-2"></i> Back to Periods
            </button>
        </div>

        {{-- Summary Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="dash-panel h-100 p-4 text-center">
                    <div class="w-icon-box bg-indigo-light text-indigo mb-3 mx-auto"><i class="fas fa-calendar-week"></i></div>
                    <h3 class="w-value">{{ $result->placedCount() }}</h3>
                    <p class="w-label">Proposed Lessons</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dash-panel h-100 p-4 text-center">
                    <div class="w-icon-box {{ $result->isComplete() ? 'bg-emerald-light text-emerald' : 'bg-amber-light text-amber' }} mb-3 mx-auto"><i class="fas {{ $result->isComplete() ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i></div>
                    <h3 class="w-value">{{ $result->isComplete() ? 'Complete' : 'Partial' }}</h3>
                    <p class="w-label">Status</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="dash-panel h-100 p-4 text-center">
                    <div class="w-icon-box bg-rose-light text-rose mb-3 mx-auto"><i class="fas fa-exclamation-triangle"></i></div>
                    <h3 class="w-value">{{ $result->unplacedCount() }}</h3>
                    <p class="w-label">Could Not Place</p>
                </div>
            </div>
        </div>

        @if($result->unplacedCount() > 0)
            <div class="dash-panel mb-4" style="border-color: #fde68a;">
                <div class="dash-panel-header" style="background: #fffbeb; border-color: #fef3c7;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-exclamation-triangle text-amber"></i>
                        <h3 class="dash-panel-title">Unplaced requirements</h3>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="tt-options-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Class / Section</th>
                                <th>Subject</th>
                                <th>Assigned Teacher(s)</th>
                                <th class="pe-4">Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result->unplaced as $u)
                                <tr>
                                    <td class="ps-4"><span class="opt-subject">{{ $u['class_section'] }}</span></td>
                                    <td>{{ $u['subject'] }}</td>
                                    <td>
                                        @if(count($u['teachers']))
                                            <span class="text-muted small">{{ implode(', ', $u['teachers']) }}</span>
                                        @else
                                            <em class="text-muted small">none assigned</em>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-muted small">{{ $u['reason'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($result->placedCount() > 0)
            <div class="d-flex justify-content-end">
                <button type="button" class="btn-dash btn-primary-dash btn-step-next" data-target-step="4">
                    Continue to Overrides <i class="fas fa-arrow-right ml-2"></i>
                </button>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 4 — Week-by-Week Overrides
         ═══════════════════════════════════════════════════════════ --}}
    <div class="step-panel" data-step-panel="4" id="step-4">
        <div class="step-nav mb-3">
            <button type="button" class="btn-dash btn-ghost btn-step-back" data-target-step="3">
                <i class="fas fa-arrow-left mr-2"></i> Back to Review
            </button>
        </div>

        <div class="dash-panel mb-4" id="week-manager">
            <div class="dash-panel-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-calendar-week text-indigo"></i>
                    <h3 class="dash-panel-title">Step 4 — Week-by-Week Overrides</h3>
                </div>
                <span class="badge-count" id="override-count">0 Overrides</span>
            </div>
            <div class="dash-panel-body p-0">
                <div class="week-tabs-wrap" id="week-tabs-wrap">
                    <div class="week-tabs" id="week-tabs">
                        <span class="text-muted small py-2 px-3">Loading weeks...</span>
                    </div>
                </div>
                <div class="week-info-bar" id="week-info-bar" style="display:none;">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="week-info-label" id="week-info-label">Week 1</span>
                        <span class="week-info-dates" id="week-info-dates"></span>
                        <span class="week-info-badge" id="week-info-exam" style="display:none;">Exam Week</span>
                        <span class="week-info-badge week-info-holiday" id="week-info-holiday" style="display:none;">Holiday</span>
                    </div>
                    <button type="button" class="btn-dash btn-amber-dash btn-sm" id="btn-add-override" style="display:none;">
                        <i class="fas fa-plus mr-1"></i> Add Override
                    </button>
                </div>
                <div id="override-list" class="p-3" style="display:none;">
                    <div id="override-items"></div>
                </div>
            </div>
        </div>

        {{-- Weekly grids per class section --}}
        @foreach($preview['schedules'] as $csId => $block)
            <div class="dash-panel mb-4">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-users text-indigo"></i>
                        <h3 class="dash-panel-title">{{ $block['label'] }}</h3>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="tt-preview-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" style="width: 110px;">Day / Period</th>
                                @foreach($preview['periods'] as $period)
                                    <th class="text-center">
                                        <div>{{ $period->name }}</div>
                                        <div class="text-muted" style="font-size: .625rem; font-weight: 600;">{{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($preview['daysOfWeek'] as $dayKey => $dayLabel)
                                <tr>
                                    <td class="ps-4"><span class="opt-class">{{ $dayLabel }}</span></td>
                                    @foreach($preview['periods'] as $period)
                                        @php $cell = $block['grid'][$dayKey][$period->period_id] ?? null; @endphp
                                        <td class="text-center p-2">
                                            @if($cell)
                                                <div class="pv-cell" data-timetable-id="{{ $cell['timetable_id'] ?? '' }}" data-day="{{ $dayKey }}" data-period="{{ $period->period_id }}">
                                                    <div class="pv-subject">{{ $cell['subject'] }}</div>
                                                    <div class="pv-meta">{{ $cell['teacher'] }}</div>
                                                    <div class="pv-meta"><i class="fas fa-door-open"></i> {{ $cell['classroom'] }}</div>
                                                </div>
                                            @else
                                                <span class="text-muted" style="font-size: .75rem;">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-end">
            <button type="button" class="btn-dash btn-primary-dash btn-step-next" data-target-step="5">
                Continue to Confirm <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════
         STEP 5 — Confirm & Save
         ═══════════════════════════════════════════════════════════ --}}
    <div class="step-panel" data-step-panel="5" id="step-5">
        <div class="step-nav mb-3">
            <button type="button" class="btn-dash btn-ghost btn-step-back" data-target-step="4">
                <i class="fas fa-arrow-left mr-2"></i> Back to Overrides
            </button>
        </div>

        <div class="dash-panel mb-4" style="border-color: #c7d2fe;">
            <div class="dash-panel-header">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle text-indigo"></i>
                    <h3 class="dash-panel-title">Step 5 — Confirm &amp; Save</h3>
                </div>
            </div>
            <div class="dash-panel-body">
                <div class="dash-alert alert-info mb-3">
                    <div class="da-icon"><i class="fas fa-info-circle"></i></div>
                    <div class="da-body">
                        <p class="da-title">This replaces the current timetable for this year</p>
                        <p class="da-desc">
                            Saving will <strong>delete all existing timetable lessons for {{ $selectedAcademicYearId ? $academicYears->firstWhere('academic_year_id', $selectedAcademicYearId)->name ?? 'this year' : 'this year' }}</strong> and
                            replace them with the {{ $result->placedCount() }} lessons above.
                            @if($result->unplacedCount() > 0)
                                The {{ $result->unplacedCount() }} unplaced requirements will <strong>not</strong> be saved.
                            @endif
                        </p>
                    </div>
                </div>
                {!! Form::open(['route' => 'timetables.auto-generate.store', 'method' => 'POST']) !!}
                {!! Form::hidden('academic_year_id', $selectedAcademicYearId) !!}
                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="btn-dash btn-primary-dash">
                        <i class="fas fa-save mr-2"></i> Confirm &amp; Save {{ $result->placedCount() }} Lessons
                    </button>
                    <a href="{{ route('timetables.index', ['academic_year_id' => $selectedAcademicYearId]) }}" class="btn-dash btn-ghost">
                        Cancel
                    </a>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Override Modal --}}
<div class="modal fade" id="overrideModal" tabindex="-1" role="dialog" aria-labelledby="overrideModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius: 12px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid var(--border-soft); padding: 1rem 1.25rem;">
                <h5 class="modal-title" id="overrideModalLabel" style="font-size: 0.938rem; font-weight: 800;">
                    <i class="fas fa-sliders-h text-indigo mr-2"></i> Override Lesson
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 1.25rem;">
                <div id="override-conflict-alert" class="alert alert-danger" style="display:none; font-size: 0.813rem;"></div>
                <div id="override-workload-alert" class="alert alert-warning" style="display:none; font-size: 0.813rem;"></div>

                <div class="mb-3">
                    <label class="dash-label">Override Type</label>
                    <select id="ov-type" class="form-control dash-control">
                        <option value="cancel">Cancel (no class this week)</option>
                        <option value="substitute">Substitute Teacher</option>
                        <option value="reschedule">Reschedule to Different Day/Period</option>
                    </select>
                </div>

                <div id="ov-substitute-fields" style="display:none;">
                    <div class="mb-3">
                        <label class="dash-label">Substitute Teacher</label>
                        <select id="ov-sub-teacher" class="form-control dash-control">
                            <option value="">Select Teacher</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="dash-label">Substitute Classroom (optional)</label>
                        <input type="text" id="ov-sub-classroom" class="form-control dash-control" placeholder="Room number">
                    </div>
                </div>

                <div id="ov-reschedule-fields" style="display:none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="dash-label">New Day</label>
                            <select id="ov-new-day" class="form-control dash-control">
                                <option value="monday">Monday</option>
                                <option value="tuesday">Tuesday</option>
                                <option value="wednesday">Wednesday</option>
                                <option value="thursday">Thursday</option>
                                <option value="friday">Friday</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="dash-label">New Period</label>
                            <select id="ov-new-period" class="form-control dash-control"></select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="dash-label">New Teacher (optional)</label>
                        <select id="ov-new-teacher" class="form-control dash-control">
                            <option value="">Keep Original Teacher</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="dash-label">New Classroom (optional)</label>
                        <input type="text" id="ov-new-classroom" class="form-control dash-control" placeholder="Room number">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="dash-label">Reason <span id="ov-reason-required" style="color: var(--rose); display:none;">*</span></label>
                    <input type="text" id="ov-reason" class="form-control dash-control" placeholder="e.g. Teacher sick leave, Exam scheduling">
                </div>

                <input type="hidden" id="ov-timetable-id">
                <input type="hidden" id="ov-effective-date">
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--border-soft); padding: 0.875rem 1.25rem;">
                <button type="button" class="btn-dash btn-ghost" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn-dash btn-primary-dash" id="btn-save-override">
                    <i class="fas fa-save mr-1"></i> Save Override
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Design tokens ── */
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
}

.bg-indigo-light { background: var(--indigo-light); } .text-indigo { color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); } .text-emerald { color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); } .text-amber { color: var(--amber); }
.bg-rose-light { background: var(--rose-light); } .text-rose { color: var(--rose); }

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
.btn-xs { padding: 0.2rem 0.5rem; font-size: 0.688rem; border-radius: 6px; }
@media (hover: hover) and (pointer: fine) {
    .btn-primary-dash:hover { background: #4338ca; border-color: #4338ca; color: #fff; }
    .btn-ghost:hover { background: var(--slate-light); color: var(--text); }
}

.badge-count { background: var(--indigo-light); color: var(--indigo); font-size: 0.688rem; font-weight: 800; padding: 0.25rem 0.5rem; border-radius: 6px; }
.gap-2 { gap: 0.5rem; }

/* ── Stepper Bar ── */
.stepper-bar {
    display: flex; align-items: center; justify-content: center;
    background: #fff; border: 1px solid var(--border); border-radius: 12px;
    padding: 1rem 1.5rem; box-shadow: 0 1px 3px rgba(15,23,42,0.03);
}
.stepper-item {
    display: flex; flex-direction: column; align-items: center; gap: 0.375rem;
    min-width: 0; flex-shrink: 0;
}
.stepper-circle {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem; font-weight: 800;
    background: var(--slate-light); color: var(--muted);
    border: 2px solid var(--border);
    transition: all 200ms var(--ease-out);
}
.stepper-item.active .stepper-circle {
    background: var(--indigo); color: #fff; border-color: var(--indigo);
    box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.15);
}
.stepper-item.completed .stepper-circle {
    background: var(--emerald); color: #fff; border-color: var(--emerald);
}
.stepper-label {
    font-size: 0.625rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.06em; color: var(--muted);
    white-space: nowrap; text-align: center;
}
.stepper-item.active .stepper-label { color: var(--indigo); }
.stepper-item.completed .stepper-label { color: var(--emerald); }

.stepper-line {
    flex: 1; height: 2px; min-width: 20px; max-width: 60px;
    background: var(--border); margin: 0 0.25rem;
    margin-bottom: 1.25rem; /* align with circle center */
    border-radius: 1px;
    transition: background 200ms var(--ease-out);
}
.stepper-line.completed { background: var(--emerald); }

/* ── Step Panels ── */
.step-panel { display: none; }
.step-panel.active { display: block; animation: stepFadeIn 280ms var(--ease-out); }
@keyframes stepFadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.step-nav { display: flex; align-items: center; }

/* ── Accordion ── */
.accordion-list { padding: 0; }
.acc-section { border-bottom: 1px solid var(--border-soft); }
.acc-section:last-child { border-bottom: 0; }
.acc-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.625rem 1.25rem; cursor: pointer; user-select: none;
    transition: background 150ms var(--ease-out);
}
.acc-header:hover { background: #f8fafc; }
.acc-header:focus-visible { outline: 2px solid var(--indigo); outline-offset: -2px; border-radius: 4px; }
.acc-header-left { display: flex; align-items: center; gap: 0.5rem; }
.acc-header-right { display: flex; align-items: center; gap: 0.5rem; }
.acc-chevron {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; color: var(--muted); font-size: 0.625rem;
    transition: transform 180ms var(--ease-out);
}
.acc-open .acc-chevron { transform: rotate(90deg); }
.acc-class-name { font-size: 0.813rem; font-weight: 800; color: var(--text); }
.acc-total-badge {
    font-size: 0.625rem; font-weight: 700; color: var(--muted);
    background: var(--slate-light); padding: 0.125rem 0.375rem; border-radius: 4px;
}
.acc-body {
    max-height: 0; overflow: hidden;
    transition: max-height 200ms var(--ease-out), opacity 180ms var(--ease-out);
    opacity: 0;
}
.acc-open .acc-body { max-height: 2000px; opacity: 1; }
.acc-empty {
    padding: 1rem 1.25rem 1rem 2.5rem; font-size: 0.813rem; color: var(--muted);
}
.acc-empty a { color: var(--indigo); font-weight: 600; text-decoration: none; }
.acc-empty a:hover { text-decoration: underline; }

/* ── Department Group ── */
.dept-group { padding: 0 1.25rem 0.5rem; }
.dept-label {
    font-size: 0.625rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: 0.08em; color: var(--slate); padding: 0.375rem 0 0.25rem;
    border-bottom: 1px solid var(--border-soft); margin-bottom: 0.25rem;
}

/* ── Subject Row ── */
.subject-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 0.375rem 0.5rem; border-radius: 6px;
    transition: background 120ms var(--ease-out);
}
.subject-row:hover { background: #f8fafc; }
.subject-row.row-warn { background: #fffbeb; }
.subject-row.row-warn:hover { background: #fef3c7; }
.subject-info { display: flex; align-items: center; gap: 0.375rem; }
.subject-name { font-size: 0.813rem; font-weight: 600; color: var(--text); }
.warn-icon { color: var(--amber); font-size: 0.688rem; }

/* ── Compact Stepper ── */
.stepper-wrap {
    display: inline-flex; align-items: center;
    border: 1px solid var(--border); border-radius: 8px; overflow: hidden;
    background: #fff; height: 30px;
}
.step-btn {
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    border: none; background: transparent; color: var(--muted); font-size: 0.875rem;
    font-weight: 700; cursor: pointer; transition: background 120ms var(--ease-out), color 120ms var(--ease-out);
    padding: 0; line-height: 1;
}
.step-btn:hover { background: var(--slate-light); color: var(--text); }
.step-btn:active { background: var(--border); }
.step-btn:focus-visible { outline: 2px solid var(--indigo); outline-offset: -2px; }
.step-value {
    width: 32px; height: 28px; text-align: center; border: none; border-left: 1px solid var(--border-soft);
    border-right: 1px solid var(--border-soft); font-size: 0.813rem; font-weight: 700;
    color: var(--text); background: transparent; padding: 0;
    -moz-appearance: textfield;
}
.step-value::-webkit-inner-spin-button,
.step-value::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.step-value:focus { outline: none; }

/* ── Bulk Actions ── */
.bulk-set-select {
    font-size: 0.688rem; font-weight: 600; color: var(--muted);
    border: 1px solid var(--border); border-radius: 6px; padding: 0.1875rem 0.375rem;
    background: #fff; cursor: pointer; appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat; background-position: right 0.25rem center; background-size: 8px;
    padding-right: 1.25rem;
}
.bulk-set-select:focus { outline: 2px solid var(--indigo); outline-offset: 1px; }

/* ── Preview grid ── */
.tt-preview-table { width: 100%; border-collapse: collapse; }
.tt-preview-table thead th { background: #f8fafc; font-size: 0.688rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slate); padding: 0.625rem 1rem; border-bottom: 1px solid var(--border); }
.tt-preview-table tbody td { padding: 0.375rem; border-bottom: 1px solid var(--border-soft); border-right: 1px solid var(--border-soft); vertical-align: middle; }
.tt-preview-table tbody tr:last-child td { border-bottom: 0; }
.tt-preview-table tbody td:last-child { border-right: 0; }
.pv-cell { background: var(--indigo-light); border-radius: 8px; padding: 0.375rem 0.5rem; }
.pv-subject { font-size: 0.75rem; font-weight: 800; color: var(--text); }
.pv-meta { font-size: 0.625rem; color: var(--muted); font-weight: 600; }

/* ── Alert ── */
.dash-alert { display: flex; align-items: center; gap: 0.75rem; border-radius: 10px; padding: 0.625rem 0.875rem; border: 1px solid #dbeafe; background: #eff6ff; }
.da-icon { width: 28px; height: 28px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 0.875rem; background: #dbeafe; color: var(--blue); }
.da-body { flex: 1; }
.da-title { font-weight: 700; font-size: 0.813rem; margin: 0 0 0.125rem; color: #1e40af; }
.da-desc { font-size: 0.75rem; margin: 0; color: #1d4ed8; opacity: 0.85; }

/* ── Sticky Footer ── */
.sticky-footer {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
    background: #fff; border-top: 1px solid var(--border);
    box-shadow: 0 -2px 8px rgba(15, 23, 42, 0.06);
    padding: 0.625rem 1.25rem;
}
.sticky-footer-inner {
    max-width: 1200px; margin: 0 auto;
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
}
.sticky-footer-left { flex: 1; }
.sf-progress { display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap; }
.sf-progress-text { font-size: 0.813rem; font-weight: 800; color: var(--text); }
.sf-sep { color: var(--border); font-size: 0.75rem; }
.sf-subjects, .sf-periods { font-size: 0.75rem; font-weight: 600; color: var(--muted); }
.sf-hint { font-size: 0.688rem; color: var(--muted); margin-top: 0.125rem; }
.sticky-footer-right { flex-shrink: 0; }

/* ── Week tabs ── */
.week-tabs-wrap { overflow-x: auto; border-bottom: 1px solid var(--border-soft); }
.week-tabs { display: flex; gap: 0; min-width: max-content; }
.week-tab { padding: 0.625rem 1rem; font-size: 0.75rem; font-weight: 700; color: var(--muted); cursor: pointer; border-bottom: 2px solid transparent; transition: all 160ms var(--ease-out); white-space: nowrap; position: relative; }
.week-tab:hover { color: var(--text); background: var(--slate-light); }
.week-tab.active { color: var(--indigo); border-bottom-color: var(--indigo); }
.week-tab .week-tab-exam { position: absolute; top: 4px; right: 4px; width: 6px; height: 6px; border-radius: 50%; background: var(--amber); }
.week-tab .week-tab-holiday { position: absolute; top: 4px; right: 4px; width: 6px; height: 6px; border-radius: 50%; background: var(--rose); }

/* ── Week info bar ── */
.week-info-bar { display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--border-soft); }
.week-info-label { font-size: 0.813rem; font-weight: 800; color: var(--text); }
.week-info-dates { font-size: 0.75rem; color: var(--muted); font-weight: 600; }
.week-info-badge { font-size: 0.625rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.125rem 0.375rem; border-radius: 4px; background: var(--amber-light); color: var(--amber); }
.week-info-holiday { background: var(--rose-light); color: var(--rose); }

/* ── Override indicator on cells ── */
.pv-cell.has-override { border: 2px solid var(--emerald); position: relative; }
.pv-cell.has-override::after { content: ''; position: absolute; top: -3px; right: -3px; width: 8px; height: 8px; border-radius: 50%; background: var(--emerald); }
.pv-cell.cancelled { opacity: 0.4; text-decoration: line-through; border-color: var(--rose); }
.pv-cell.cancelled::after { background: var(--rose); }

/* ── Override list items ── */
.override-item { display: flex; align-items: center; justify-content: space-between; padding: 0.625rem 0.875rem; background: #fff; border: 1px solid var(--border); border-radius: 8px; margin-bottom: 0.5rem; }
.override-item-type { font-size: 0.688rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; padding: 0.125rem 0.375rem; border-radius: 4px; }
.override-item-type.cancel { background: var(--rose-light); color: var(--rose); }
.override-item-type.substitute { background: var(--amber-light); color: var(--amber); }
.override-item-type.reschedule { background: var(--blue-light); color: var(--blue); }
.override-item-detail { font-size: 0.75rem; color: var(--text); font-weight: 600; }
.override-item-reason { font-size: 0.688rem; color: var(--muted); }

/* ── Override buttons ── */
.btn-amber-dash { background: var(--amber); color: #fff; padding: 0.375rem 0.75rem; border: 1px solid var(--amber); font-size: 0.75rem; }
.btn-sm { padding: 0.25rem 0.5rem; font-size: 0.75rem; }
.btn-danger-dash { background: var(--rose); color: #fff; padding: 0.25rem 0.5rem; border: 1px solid var(--rose); font-size: 0.688rem; border-radius: 6px; cursor: pointer; }
@media (hover: hover) and (pointer: fine) {
    .btn-amber-dash:hover { background: #d97706; border-color: #d97706; color: #fff; }
    .btn-danger-dash:hover { background: #e11d48; border-color: #e11d48; color: #fff; }
}

@media (prefers-reduced-motion: reduce) {
    .step-panel.active { animation: none; }
    .btn-dash { transition: none; }
    .acc-body { transition: none; }
    .acc-chevron { transition: none; }
    .stepper-circle { transition: none; }
    .stepper-line { transition: none; }
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .stepper-bar { flex-wrap: wrap; gap: 0.25rem; padding: 0.75rem; }
    .stepper-line { min-width: 12px; max-width: 30px; }
    .stepper-label { font-size: 0.5rem; }
    .stepper-circle { width: 26px; height: 26px; font-size: 0.625rem; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Step Navigation ──
    var currentStep = {{ $hasResult ? 3 : ($selectedAcademicYearId ? 2 : 1) }};
    var hasResult = {{ $hasResult ? 'true' : 'false' }};

    function goToStep(step) {
        // Hide all panels
        document.querySelectorAll('.step-panel').forEach(function(panel) {
            panel.classList.remove('active');
        });
        // Show target panel
        var target = document.querySelector('[data-step-panel="' + step + '"]');
        if (target) target.classList.add('active');

        // Update stepper bar
        document.querySelectorAll('.stepper-item').forEach(function(item) {
            var s = parseInt(item.dataset.step);
            item.classList.remove('active', 'completed', 'pending');
            if (s < step) item.classList.add('completed');
            else if (s === step) item.classList.add('active');
            else item.classList.add('pending');
        });
        document.querySelectorAll('.stepper-line').forEach(function(line, i) {
            line.classList.toggle('completed', i < step - 1);
        });

        // Show/hide sticky footer
        var sticky = document.getElementById('sticky-footer');
        if (sticky) {
            sticky.style.display = (step === 2 && !hasResult) ? 'block' : 'none';
        }

        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Back buttons
    document.querySelectorAll('.btn-step-back').forEach(function(btn) {
        btn.addEventListener('click', function() {
            goToStep(parseInt(this.dataset.targetStep));
        });
    });

    // Next buttons (only work when result exists)
    document.querySelectorAll('.btn-step-next').forEach(function(btn) {
        btn.addEventListener('click', function() {
            goToStep(parseInt(this.dataset.targetStep));
        });
    });

    // If no year selected, only step 1 is active; if year selected but no result, step 2 is active
    if (!hasResult) {
        // Show step 1 or 2 based on whether year is selected
        var academicYearId = '{{ $selectedAcademicYearId }}';
        if (!academicYearId) {
            goToStep(1);
        } else {
            goToStep(2);
        }
    } else {
        // Result exists, start at step 3
        goToStep(3);
    }

    // ── Accordion Toggle ──
    document.querySelectorAll('.acc-header').forEach(function(header) {
        header.addEventListener('click', function() {
            var section = this.closest('.acc-section');
            section.classList.toggle('acc-open');
            section.classList.toggle('acc-closed');
            this.setAttribute('aria-expanded', section.classList.contains('acc-open'));
        });
        header.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // Expand All / Collapse All
    var btnExpandAll = document.getElementById('btnExpandAll');
    var btnCollapseAll = document.getElementById('btnCollapseAll');
    if (btnExpandAll) {
        btnExpandAll.addEventListener('click', function() {
            document.querySelectorAll('.acc-section').forEach(function(s) {
                s.classList.remove('acc-closed');
                s.classList.add('acc-open');
            });
            document.querySelectorAll('.acc-header').forEach(function(h) {
                h.setAttribute('aria-expanded', 'true');
            });
        });
    }
    if (btnCollapseAll) {
        btnCollapseAll.addEventListener('click', function() {
            document.querySelectorAll('.acc-section').forEach(function(s) {
                s.classList.remove('acc-open');
                s.classList.add('acc-closed');
            });
            document.querySelectorAll('.acc-header').forEach(function(h) {
                h.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // ── Compact Stepper Controls ──
    function updateStepper(input, delta) {
        var min = parseInt(input.min) || 1;
        var max = parseInt(input.max) || 40;
        var val = parseInt(input.value) || 1;
        val = Math.min(max, Math.max(min, val + delta));
        input.value = val;
        input.dispatchEvent(new Event('change'));
    }

    document.querySelectorAll('.step-minus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.dataset.target);
            if (input) updateStepper(input, -1);
        });
    });

    document.querySelectorAll('.step-plus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = document.getElementById(this.dataset.target);
            if (input) updateStepper(input, 1);
        });
    });

    document.querySelectorAll('.step-value').forEach(function(input) {
        input.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowUp') {
                e.preventDefault();
                updateStepper(this, 1);
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                updateStepper(this, -1);
            }
        });
    });

    // ── Bulk Actions ──
    function recalcClassTotal(classId) {
        var total = 0;
        document.querySelectorAll('.step-value[data-class="' + classId + '"]').forEach(function(input) {
            total += parseInt(input.value) || 0;
        });
        var badge = document.querySelector('.acc-total-badge[data-class="' + classId + '"]');
        if (badge) badge.textContent = total + ' periods/wk';
        recalcGlobalTotal();
    }

    function recalcGlobalTotal() {
        var total = 0;
        document.querySelectorAll('.step-value').forEach(function(input) {
            total += parseInt(input.value) || 0;
        });
        var el = document.getElementById('sf-total-periods');
        if (el) el.textContent = total + ' total periods/wk';
    }

    document.querySelectorAll('.step-value').forEach(function(input) {
        input.addEventListener('change', function() {
            recalcClassTotal(this.dataset.class);
        });
    });

    document.querySelectorAll('.bulk-set-select').forEach(function(select) {
        select.addEventListener('change', function() {
            var classId = this.dataset.class;
            var val = parseInt(this.value);
            if (!val) return;
            document.querySelectorAll('.step-value[data-class="' + classId + '"]').forEach(function(input) {
                input.value = val;
                input.dispatchEvent(new Event('change'));
            });
            this.value = '';
        });
    });

    recalcGlobalTotal();

    // ── Week Overrides Logic (same as before) ──
    var academicYearId = '{{ $selectedAcademicYearId }}';
    var weekTabs = document.getElementById('week-tabs');
    var weekInfoBar = document.getElementById('week-info-bar');
    var weekInfoLabel = document.getElementById('week-info-label');
    var weekInfoDates = document.getElementById('week-info-dates');
    var weekInfoExam = document.getElementById('week-info-exam');
    var weekInfoHoliday = document.getElementById('week-info-holiday');
    var btnAddOverride = document.getElementById('btn-add-override');
    var overrideList = document.getElementById('override-list');
    var overrideItems = document.getElementById('override-items');
    var overrideCount = document.getElementById('override-count');
    var selectedWeekId = null;

    fetch('/timetables/teachers')
        .then(function(r) { return r.json(); })
        .then(function(teachers) {
            var subSel = document.getElementById('ov-sub-teacher');
            var newSel = document.getElementById('ov-new-teacher');
            teachers.forEach(function(t) {
                if (subSel) { var o = document.createElement('option'); o.value = t.id; o.textContent = t.name; subSel.appendChild(o); }
                if (newSel) { var o2 = document.createElement('option'); o2.value = t.id; o2.textContent = t.name; newSel.appendChild(o2); }
            });
        });

    fetch('/timetables/periods')
        .then(function(r) { return r.json(); })
        .then(function(periods) {
            var sel = document.getElementById('ov-new-period');
            if (sel) {
                periods.forEach(function(p) {
                    var o = document.createElement('option'); o.value = p.id; o.textContent = p.name; sel.appendChild(o);
                });
            }
        });

    fetch('/timetables/term-weeks/' + academicYearId)
        .then(function(r) { return r.json(); })
        .then(function(weeks) {
            if (weeks.length === 0) {
                weekTabs.innerHTML = '<span class="text-muted small py-2 px-3">No term weeks defined. Create terms with start/end dates first.</span>';
                return;
            }
            weekTabs.innerHTML = '';
            weeks.forEach(function(w, i) {
                var tab = document.createElement('div');
                tab.className = 'week-tab' + (i === 0 ? ' active' : '');
                tab.dataset.weekId = w.id;
                tab.dataset.weekLabel = w.label;
                tab.dataset.weekDates = w.start_date + ' to ' + w.end_date;
                tab.dataset.isExam = w.is_exam_week ? '1' : '0';
                tab.dataset.isHoliday = (w.label && w.label.indexOf('Holiday') !== -1) ? '1' : '0';
                tab.innerHTML = w.label;
                if (w.is_exam_week) tab.innerHTML += ' <span class="week-tab-exam"></span>';
                if (w.label && w.label.indexOf('Holiday') !== -1) tab.innerHTML += ' <span class="week-tab-holiday"></span>';
                tab.addEventListener('click', function() { selectWeek(w, this); });
                weekTabs.appendChild(tab);
            });
            if (weeks.length > 0) selectWeek(weeks[0], weekTabs.firstChild);
        });

    function selectWeek(week, tabEl) {
        document.querySelectorAll('.week-tab').forEach(function(t) { t.classList.remove('active'); });
        if (tabEl) tabEl.classList.add('active');
        selectedWeekId = week.id;
        weekInfoBar.style.display = 'flex';
        weekInfoLabel.textContent = week.label;
        weekInfoDates.textContent = week.start_date + ' — ' + week.end_date;
        weekInfoExam.style.display = week.is_exam_week ? 'inline-block' : 'none';
        weekInfoHoliday.style.display = (week.label && week.label.indexOf('Holiday') !== -1) ? 'inline-block' : 'none';
        btnAddOverride.style.display = 'inline-flex';
        loadOverrides(week.id);
    }

    function loadOverrides(weekId) {
        fetch('/timetables/overrides/' + weekId)
            .then(function(r) { return r.json(); })
            .then(function(overrides) {
                overrideList.style.display = 'block';
                overrideCount.textContent = overrides.length + ' Override' + (overrides.length !== 1 ? 's' : '');
                if (overrides.length === 0) {
                    overrideItems.innerHTML = '<div class="text-muted small text-center py-2">No overrides for this week. Click a timetable cell to create one.</div>';
                    return;
                }
                var html = '';
                overrides.forEach(function(ov) {
                    var typeClass = ov.override_type;
                    var detail = '';
                    if (ov.override_type === 'cancel') {
                        detail = ov.timetable.subject + ' (' + ov.timetable.teacher + ') — CANCELLED';
                    } else if (ov.override_type === 'substitute') {
                        detail = ov.timetable.subject + ' — Substitute: ' + (ov.substitute_teacher || 'TBD');
                    } else if (ov.override_type === 'reschedule') {
                        detail = ov.timetable.subject + ' → ' + (ov.new_day_of_week || ov.timetable.day_of_week) + ' ' + (ov.new_period || ov.timetable.period);
                        if (ov.new_teacher) detail += ' (' + ov.new_teacher + ')';
                    }
                    html += '<div class="override-item">' +
                        '<div>' +
                            '<span class="override-item-type ' + typeClass + '">' + ov.override_type + '</span> ' +
                            '<span class="override-item-detail">' + detail + '</span>' +
                            (ov.reason ? '<div class="override-item-reason">Reason: ' + ov.reason + '</div>' : '') +
                        '</div>' +
                        '<button class="btn-dash btn-danger-dash" onclick="deleteOverride(' + ov.id + ')"><i class="fas fa-trash"></i></button>' +
                    '</div>';
                });
                overrideItems.innerHTML = html;

                document.querySelectorAll('.pv-cell[data-timetable-id]').forEach(function(cell) {
                    cell.classList.remove('has-override', 'cancelled');
                });
                overrides.forEach(function(ov) {
                    var cell = document.querySelector('.pv-cell[data-timetable-id="' + ov.timetable_id + '"]');
                    if (cell) {
                        cell.classList.add('has-override');
                        if (ov.override_type === 'cancel') cell.classList.add('cancelled');
                    }
                });
            });
    }

    // Override modal logic
    var ovType = document.getElementById('ov-type');
    var ovSubFields = document.getElementById('ov-substitute-fields');
    var ovRescheduleFields = document.getElementById('ov-reschedule-fields');
    var ovReasonRequired = document.getElementById('ov-reason-required');

    if (ovType) {
        ovType.addEventListener('change', function() {
            ovSubFields.style.display = this.value === 'substitute' ? 'block' : 'none';
            ovRescheduleFields.style.display = this.value === 'reschedule' ? 'block' : 'none';
            ovReasonRequired.style.display = (this.value === 'cancel' || this.value === 'reschedule') ? 'inline' : 'none';
        });
    }

    // Conflict check
    var ovSubTeacher = document.getElementById('ov-sub-teacher');
    var ovNewTeacher = document.getElementById('ov-new-teacher');
    var conflictAlert = document.getElementById('override-conflict-alert');
    var workloadAlert = document.getElementById('override-workload-alert');

    [ovSubTeacher, ovNewTeacher].forEach(function(sel) {
        if (!sel) return;
        sel.addEventListener('change', function() {
            var teacherId = this.value;
            if (!teacherId || !selectedWeekId) return;
            var day = document.getElementById('ov-new-day') ? document.getElementById('ov-new-day').value : 'monday';
            var period = document.getElementById('ov-new-period') ? document.getElementById('ov-new-period').value : '1';
            checkConflictAjax(teacherId, day, period);
        });
    });

    function checkConflictAjax(teacherId, day, periodId) {
        var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        fetch('/timetables/check-conflict', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                academic_year_id: academicYearId,
                term_week_id: selectedWeekId,
                teacher_id: teacherId,
                day_of_week: day,
                period_id: periodId,
                ignore_timetable_id: document.getElementById('ov-timetable-id').value || null,
            }),
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            conflictAlert.style.display = data.has_conflicts ? 'block' : 'none';
            if (data.has_conflicts) {
                var msgs = [];
                if (data.conflicts.teacher) msgs.push(data.conflicts.teacher.message);
                if (data.conflicts.class_section) msgs.push(data.conflicts.class_section.message);
                if (data.conflicts.classroom) msgs.push(data.conflicts.classroom.message);
                conflictAlert.innerHTML = '<strong>Conflict detected:</strong><br>' + msgs.join('<br>');
            }
            workloadAlert.style.display = (data.warnings && data.warnings.workload && data.warnings.workload.length > 0) ? 'block' : 'none';
            if (data.warnings && data.warnings.workload) {
                var wMsgs = data.warnings.workload.map(function(w) { return w.message; });
                workloadAlert.innerHTML = '<strong>Workload Warning:</strong><br>' + wMsgs.join('<br>') + '<br><small>You can proceed, but be aware of the teacher\'s workload.</small>';
            }
        });
    }

    // Cell click → override modal
    document.querySelectorAll('.pv-cell[data-timetable-id]').forEach(function(cell) {
        cell.style.cursor = 'pointer';
        cell.addEventListener('click', function() {
            var timetableId = this.dataset.timetableId;
            if (!timetableId || !selectedWeekId) return;
            document.getElementById('ov-timetable-id').value = timetableId;
            document.getElementById('ov-type').value = 'cancel';
            document.getElementById('ov-type').dispatchEvent(new Event('change'));
            document.getElementById('ov-reason').value = '';
            conflictAlert.style.display = 'none';
            workloadAlert.style.display = 'none';
            $('#overrideModal').modal('show');
        });
    });

    // Save override
    var btnSaveOverride = document.getElementById('btn-save-override');
    if (btnSaveOverride) {
        btnSaveOverride.addEventListener('click', function() {
            var timetableId = document.getElementById('ov-timetable-id').value;
            var type = document.getElementById('ov-type').value;
            var reason = document.getElementById('ov-reason').value;

            if ((type === 'cancel' || type === 'reschedule') && !reason) {
                alert('Reason is required for ' + type + ' overrides.');
                return;
            }

            var overrideData = {
                timetable_id: parseInt(timetableId),
                override_type: type,
                effective_date: new Date().toISOString().split('T')[0],
                reason: reason || null,
            };

            if (type === 'substitute') {
                var subTeacher = document.getElementById('ov-sub-teacher').value;
                if (subTeacher) overrideData.substitute_teacher_id = parseInt(subTeacher);
            }
            if (type === 'reschedule') {
                overrideData.new_day_of_week = document.getElementById('ov-new-day').value;
                overrideData.new_period_id = parseInt(document.getElementById('ov-new-period').value);
                var newTeacher = document.getElementById('ov-new-teacher').value;
                if (newTeacher) overrideData.new_teacher_id = parseInt(newTeacher);
            }

            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/timetables/store-overrides', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    academic_year_id: academicYearId,
                    term_week_id: selectedWeekId,
                    overrides: [overrideData],
                }),
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.errors) {
                    alert('Error: ' + data.errors.join(', '));
                } else {
                    $('#overrideModal').modal('hide');
                    loadOverrides(selectedWeekId);
                }
            });
        });
    }
});

function deleteOverride(overrideId) {
    if (!confirm('Remove this override?')) return;
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/timetables/store-overrides', {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ override_id: overrideId }),
    })
    .then(function(r) { return r.json(); })
    .then(function() { location.reload(); });
}
</script>
@endsection
