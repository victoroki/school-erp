@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-user-pen mr-2" style="color: var(--indigo);"></i>Add Marks - Single Student</h1>
            <p class="dash-sub mb-0">Pick the class first - only its students will appear. Enter saves and loads the next unmarked student.</p>
        </div>
        <a href="{{ route('exam-results.bulk') }}" class="btn-dash btn-ghost no-print">
            <i class="fas fa-list-ol"></i> Prefer a whole-class sheet? Use Bulk Entry
        </a>
    </div>

    @include('flash::message')

    <form method="POST" action="{{ route('exam-results.store') }}" id="single-entry-form">
        @csrf

        {{-- STEP 1: CONTEXT --}}
        <label class="filter-label mb-3 mt-2"><span class="step-badge">1</span> Context</label>
        <div class="card dash-panel mb-4">
            <div class="card-body p-4">
                <div class="form-row">
                    <div class="form-group col-md-4 mb-2 mb-md-0">
                        <label class="filter-label">Exam Session <span class="text-danger">*</span></label>
                        <div class="filter-field">
                            <i class="fas fa-file-signature"></i>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['required', 'id' => 'ctx-exam']) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-4 mb-2 mb-md-0">
                        <label class="filter-label">Class &amp; Section <span class="text-danger">*</span></label>
                        <div class="filter-field">
                            <i class="fas fa-school"></i>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['required', 'id' => 'ctx-class']) !!}
                        </div>
                    </div>
                    <div class="form-group col-md-4 mb-0">
                        <label class="filter-label">Subject <span class="text-danger">*</span></label>
                        <div class="filter-field">
                            <i class="fas fa-book"></i>
                            {!! Form::select('subject_id', $subjects, request('subject_id'), ['required', 'id' => 'ctx-subject']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STEP 2: ENTRY --}}
        <label class="filter-label mb-3"><span class="step-badge">2</span> Student &amp; Mark</label>
        <div class="card dash-panel mb-4" id="entry-card">
            <div class="card-body p-4">
                <div id="students-loading" class="d-none text-muted small mb-3">
                    <i class="fas fa-spinner fa-spin mr-1"></i>Loading students for this class...
                </div>

                <div class="form-row align-items-end">
                    <div class="form-group col-lg-5">
                        <label class="filter-label">Student <span class="text-danger">*</span></label>
                        <div class="filter-field">
                            <i class="fas fa-user-graduate"></i>
                            <select name="student_id" id="student-select" required disabled>
                                <option value="">Select class above first...</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group col-lg-3 col-6">
                        <label class="filter-label">Mark / <span id="max-marks-label">100</span></label>
                        <input type="number" name="marks_obtained" id="mark-input" class="mark-input"
                               step="0.01" min="0" placeholder="-" autocomplete="off" required>
                    </div>

                    <div class="form-group col-lg-4 col-6">
                        <label class="filter-label">&nbsp;</label>
                        <span class="grade-chip" id="grade-chip">Grade shows as you type</span>
                    </div>
                </div>

                <div class="alert alert-warning py-2 px-3 small d-none mb-0" id="existing-banner">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    This student already has <strong id="existing-mark"></strong> recorded - saving will overwrite it.
                </div>

                <div class="form-row mt-3">
                    <div class="form-group col-md-8 mb-0">
                        <label class="filter-label">Teacher Remarks</label>
                        <input type="text" name="remarks" id="remark-input" class="remark-input" placeholder="Optional..." maxlength="255">
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 pt-0 px-4 pb-4 d-flex flex-wrap align-items-center">
                <button type="submit" class="btn-dash btn-primary-dash mr-3" id="save-btn">
                    <i class="fas fa-save"></i> Save &amp; Next
                </button>
                <span id="save-feedback" class="small font-weight-bold text-muted"></span>
                <input type="hidden" name="created_by" value="{{ Auth::id() }}">
            </div>
        </div>
    </form>
</div>

<style>
    :root {
        --indigo: #4f46e5;
        --indigo-dark: #4338ca;
        --indigo-light: #eef2ff;
        --emerald: #10b981;
        --rose: #ef4444;
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

    .mark-input {
        width: 100%; border: 1.5px solid var(--border); border-radius: 12px;
        padding: 0.65rem 0.85rem; font-size: 1.15rem; font-weight: 800; color: var(--text);
        text-align: center; outline: none; background: #fff;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }
    .mark-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79,70,229,0.10); }
    .mark-input.invalid { border-color: var(--rose); background: #fef2f2; }

    .grade-chip {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 130px; height: 44px; padding: 0 14px;
        border-radius: 12px; font-size: 0.9rem; font-weight: 800;
        background: #f8fafc; color: #cbd5e1; border: 1px dashed var(--border);
    }
    .grade-chip.filled { background: var(--indigo-light); color: var(--indigo-dark); border-style: solid; border-color: #c7d2fe; }
    .grade-chip.fail { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    .remark-input {
        width: 100%; border: 1px solid var(--border); border-radius: 12px;
        padding: 0.55rem 0.85rem; font-size: 0.875rem; color: var(--text); outline: none;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }
    .remark-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 4px rgba(79,70,229,0.10); }

    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; border: 0; cursor: pointer; }
    .btn-dash i { margin-right: 7px; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-primary-dash[disabled] { opacity: 0.6; transform: none; }
    .btn-ghost { background: #fff; border: 1px solid var(--border) !important; color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
    }
</style>

@push('page_scripts')
<script>
(function () {
    'use strict';

    var GRADES = @json($grades);
    var CSRF = document.querySelector('#single-entry-form input[name=_token]').value;

    var examSel = document.getElementById('ctx-exam');
    var classSel = document.getElementById('ctx-class');
    var subjectSel = document.getElementById('ctx-subject');
    var studentSel = document.getElementById('student-select');
    var markInput = document.getElementById('mark-input');
    var remarkInput = document.getElementById('remark-input');
    var gradeChip = document.getElementById('grade-chip');
    var maxLabel = document.getElementById('max-marks-label');
    var loadingEl = document.getElementById('students-loading');
    var banner = document.getElementById('existing-banner');
    var feedback = document.getElementById('save-feedback');
    var saveBtn = document.getElementById('save-btn');
    var form = document.getElementById('single-entry-form');

    var MAX = parseInt(markInput.dataset.max || '100', 10);
    var studentsCache = [];

    function localGrade(mark) {
        if (mark === '' || isNaN(parseFloat(mark))) return null;
        var pct = (parseFloat(mark) / MAX) * 100;
        for (var i = 0; i < GRADES.length; i++) {
            if (pct >= parseFloat(GRADES[i].min_percentage) && pct <= parseFloat(GRADES[i].max_percentage)) {
                return GRADES[i];
            }
        }
        return null;
    }

    function renderChip() {
        var g = localGrade(markInput.value);
        if (!g && markInput.value === '') {
            gradeChip.className = 'grade-chip';
            gradeChip.textContent = 'Grade shows as you type';
            return;
        }
        var fail = g && g.name === 'E';
        gradeChip.className = 'grade-chip filled' + (fail ? ' fail' : '');
        gradeChip.innerHTML = g ? g.name + (g.grade_point ? ' &middot; ' + parseFloat(g.grade_point) + ' pts' : '') : '?';
    }

    function contextComplete() {
        return examSel.value && classSel.value && subjectSel.value;
    }

    function loadStudents() {
        if (!contextComplete()) return;

        loadingEl.classList.remove('d-none');
        studentSel.disabled = true;
        studentSel.innerHTML = '<option value="">Loading...</option>';

        var url = '{{ route('exam-results.students-by-class') }}'
            + '?class_section_id=' + encodeURIComponent(classSel.value)
            + '&exam_id=' + encodeURIComponent(examSel.value)
            + '&subject_id=' + encodeURIComponent(subjectSel.value);

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.ok) throw new Error(data.message || 'Failed');

                studentsCache = data.students;
                studentSel.innerHTML = '';

                if (!data.students.length) {
                    studentSel.appendChild(option('', 'No active students in this class'));
                    studentSel.disabled = true;
                    return;
                }

                studentSel.appendChild(option('', 'Select a student (' + data.students.length + ')'));

                data.students.forEach(function (s) {
                    var hasMark = Object.prototype.hasOwnProperty.call(data.existing, s.id);
                    var opt = option(s.id, s.name + ' (' + s.admission_no + ')' + (hasMark ? ' - already recorded' : ''));
                    studentSel.appendChild(opt);
                });

                studentSel.disabled = false;

                // Show real max marks from the sitting
                if (data.max_marks) {
                    MAX = parseInt(data.max_marks, 10);
                } else {
                    MAX = 100;
                }
                maxLabel.textContent = MAX;

                // Pre-select first student without a recorded mark
                pickNextUnmarked();
            })
            .catch(function () {
                resetStudents('Error loading students - try again');
            })
            .finally(function () {
                loadingEl.classList.add('d-none');
            });
    }

    function option(value, label) {
        var o = document.createElement('option');
        o.value = value;
        o.textContent = label;
        return o;
    }

    function resetStudents(message) {
        studentSel.innerHTML = '';
        studentSel.appendChild(option('', message || 'Select...'));
        studentSel.disabled = true;
    }

    function pickNextUnmarked() {
        var existingNow = {};
        // Re-fetch lightweight: use current selection list + known marks map
        var marked = getRecordedIds();
        for (var i = 0; i < studentsCache.length; i++) {
            if (marked.indexOf(String(studentsCache[i].id)) === -1) {
                studentSel.value = String(studentsCache[i].id);
                onStudentChange();
                return;
            }
        }
        // All marked - leave selection at prompt
        studentSel.value = '';
        onStudentChange();
    }

    var recordedMarks = {};

    function getRecordedIds() {
        return Object.keys(recordedMarks).filter(function (k) { return recordedMarks[k] !== null && recordedMarks[k] !== ''; });
    }

    function applyExistingFor(studentId) {
        var val = Object.prototype.hasOwnProperty.call(recordedMarks, studentId) ? recordedMarks[studentId] : null;
        if (val !== null && val !== undefined && val !== '') {
            markInput.value = parseFloat(val);
            banner.classList.remove('d-none');
            document.getElementById('existing-mark').textContent = parseFloat(val) + ' / ' + MAX;
        } else {
            markInput.value = '';
            banner.classList.add('d-none');
        }
        renderChip();
        markInput.focus();
        markInput.select();
    }

    function onStudentChange() {
        applyExistingFor(studentSel.value);
        feedback.textContent = '';
        feedback.className = 'small font-weight-bold text-muted';
    }

    [examSel, classSel, subjectSel].forEach(function (sel) {
        sel.addEventListener('change', function () {
            recordedMarks = {};
            loadStudents();
        });
    });
    studentSel.addEventListener('change', onStudentChange);

    markInput.addEventListener('input', renderChip);

    // Save via AJAX so entry can continue instantly; plain POST fallback otherwise.
    form.addEventListener('submit', function (e) {
        if (!contextComplete() || !studentSel.value) return;

        e.preventDefault();

        var markVal = markInput.value.trim();
        if (markVal === '' || isNaN(parseFloat(markVal)) || parseFloat(markVal) < 0 || parseFloat(markVal) > MAX) {
            markInput.classList.add('invalid');
            markInput.focus();
            return;
        }
        markInput.classList.remove('invalid');

        saveBtn.disabled = true;

        var fd = new FormData(form);
        fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); })
            .then(function (res) {
                if (res.status !== 200 || !res.json.ok) {
                    throw new Error((res.json.errors && Object.values(res.json.errors)[0][0]) || 'Save failed');
                }

                recordedMarks[studentSel.value] = markVal;

                var g = localGrade(markVal);
                feedback.textContent = 'Saved: ' +
                    (studentSel.options[studentSel.selectedIndex] ? studentSel.options[studentSel.selectedIndex].text.split('(')[0].trim() : '') +
                    ' - ' + markVal + '/' + MAX + (g ? ' (' + g.name + ')' : '');
                feedback.className = 'small font-weight-bold text-success';

                remarkInput.value = '';
                pickNextUnmarked();
            })
            .catch(function (err) {
                feedback.textContent = err.message || 'Save failed';
                feedback.className = 'small font-weight-bold text-danger';
            })
            .finally(function () {
                saveBtn.disabled = false;
            });
    });

    // Initial load when arriving with query params pre-filled
    if (contextComplete()) {
        loadStudents();
    }
})();
</script>
@endpush
@endsection
