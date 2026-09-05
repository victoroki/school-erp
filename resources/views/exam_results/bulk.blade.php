@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- HEADER --}}
    <div class="dash-header d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div>
            <h1 class="dash-heading"><i class="fas fa-pen-alt mr-2" style="color: var(--indigo);"></i>Marks Entry</h1>
            <p class="dash-sub mb-0">Type a mark, press Enter - it saves instantly and jumps to the next student</p>
        </div>
    </div>

    @include('flash::message')

    {{-- FILTER --}}
    <div class="card dash-panel mb-4 no-print">
        <div class="card-body p-4">
            <form action="{{ route('exam-results.bulk') }}" method="GET">
                <div class="form-row align-items-end">
                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-md-0">
                        <label class="filter-label">Exam Session</label>
                        <div class="filter-field">
                            <i class="fas fa-file-signature"></i>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['required']) !!}
                        </div>
                    </div>
                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-md-0">
                        <label class="filter-label">Class &amp; Section</label>
                        <div class="filter-field">
                            <i class="fas fa-school"></i>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['required']) !!}
                        </div>
                    </div>
                    <div class="form-group col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <label class="filter-label">Subject</label>
                        <div class="filter-field">
                            <i class="fas fa-book"></i>
                            {!! Form::select('subject_id', $subjects, request('subject_id'), ['required']) !!}
                        </div>
                    </div>
                    <div class="form-group col-lg-3 col-md-6 mb-0">
                        <button type="submit" class="btn-dash btn-primary-dash w-100">
                            <i class="fas fa-search"></i> Load Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(request()->filled(['exam_id', 'class_section_id', 'subject_id']) && auth()->user()->hasPermission('exams.import'))
        {{-- IMPORT TOOLS (collapsed by default to reduce clutter) --}}
        <div class="card dash-panel mb-4 no-print">
            <div class="card-header bg-white border-bottom-0 pt-3 px-4" data-toggle="collapse" data-target="#importTools" role="button" aria-expanded="false">
                <span class="font-weight-bold text-muted small text-uppercase"><i class="fas fa-file-csv mr-2 text-success"></i>Excel / CSV Import Tools</span>
                <i class="fas fa-chevron-down float-right text-muted"></i>
            </div>
            <div id="importTools" class="collapse {{ app('request')->has('imported') ? 'show' : '' }}">
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="row">
                        <div class="col-md-6 mb-2 mb-md-0 border-right-md">
                            <a href="{{ route('exam-results.import-template', request()->all()) }}" class="btn-dash btn-ghost w-100">
                                <i class="fas fa-download text-success"></i> Download CSV Template
                            </a>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('exam-results.import.store') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center">
                                @csrf
                                <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                                <input type="hidden" name="class_section_id" value="{{ request('class_section_id') }}">
                                <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                                <input type="file" name="excel_file" required accept=".csv"
                                       class="mr-2" style="font-size: 0.8rem; max-width: 220px;">
                                <button type="submit" class="btn-dash btn-ghost">
                                    <i class="fas fa-upload text-primary"></i> Import
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(count($students) > 0)
        <form method="POST" action="{{ route('exam-results.bulk.store') }}" id="marks-form">
            @csrf
            <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
            <input type="hidden" name="class_section_id" value="{{ request('class_section_id') }}">
            <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">

            {{-- STICKY ACTION BAR --}}
            <div class="entry-bar mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-wrap">
                        <span class="stat-chip mr-2">Recorded: <strong>{{ $totalRecorded }}/{{ $totalStudents }}</strong></span>
                        <span class="stat-chip mr-2">Max: <strong>{{ $maxMarks }}</strong></span>
                        <span class="stat-chip mr-2">This page: <strong id="entered-count">0</strong>/<strong>{{ $students->count() }}</strong> entered</span>
                        <span id="save-status" class="ml-2 small font-weight-bold text-muted"></span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="progress-wrap mr-3 d-none d-md-block">
                            <div class="progress-track"><div class="progress-fill" id="progress-fill" style="width:0%"></div></div>
                        </div>
                        <button type="submit" class="btn-dash btn-primary-dash">
                            <i class="fas fa-save"></i> Save Page
                        </button>
                    </div>
                </div>
            </div>

            <div class="card dash-panel overflow-hidden">
                <div class="table-responsive">
                    <table class="marks-table" id="marks-table">
                        <thead>
                            <tr>
                                <th style="width:44px">#</th>
                                <th style="width:130px">Admission No</th>
                                <th>Student</th>
                                <th style="width:110px">Mark / {{ $maxMarks }}</th>
                                <th style="width:150px">Grade</th>
                                <th style="width:30px"></th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $index => $student)
                                @php
                                    // Continuous numbering across pages
                                    $rowNo = ($students->currentPage() - 1) * $students->perPage() + $index + 1;
                                    $existing = $existingResults[$student->student_id] ?? null;
                                    $initials = strtoupper(mb_substr($student->first_name ?? '?', 0, 1)) . strtoupper(mb_substr($student->last_name ?? '', 0, 1));
                                    $avatarColors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a', '#f6a821', '#e9506d'];
                                    $avatarColor = $avatarColors[$student->student_id % count($avatarColors)];
                                @endphp
                                <tr data-student-id="{{ $student->student_id }}" class="mark-row {{ ($existing !== null && $existing !== '') ? 'row-saved' : '' }}">
                                    <td class="text-center text-muted">{{ $rowNo }}</td>
                                    <td><span class="adm-badge">{{ $student->admission_no }}</span></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="st-avatar" style="background: {{ $avatarColor }}">{{ $initials }}</div>
                                            <span class="st-name">{{ $student->full_name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number"
                                               class="mark-input"
                                               name="marks[{{ $student->student_id }}]"
                                               step="0.01" min="0" max="{{ $maxMarks }}"
                                               value="{{ $existing }}"
                                               placeholder="-"
                                               autocomplete="off"
                                               data-student="{{ $student->student_id }}">
                                    </td>
                                    <td><span class="grade-chip" data-grade-for="{{ $student->student_id }}"></span></td>
                                    <td class="text-center save-cell">
                                        <i class="fas fa-check-circle saved-tick" title="Saved"></i>
                                        <i class="fas fa-sync-alt saving-spin" title="Saving..."></i>
                                        <i class="fas fa-times-circle error-cross" title="Failed"></i>
                                    </td>
                                    <td>
                                        <input type="text" class="remark-input" name="remarks[{{ $student->student_id }}]"
                                               placeholder="Optional..." maxlength="255" data-student="{{ $student->student_id }}">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                    <div class="pagination-panel m-3 no-print">
                        <div class="d-flex flex-wrap align-items-center justify-content-between">
                            <div class="pagination-info mb-2 mb-md-0">
                                Showing <strong>{{ $students->firstItem() }}</strong>-<strong>{{ $students->lastItem() }}</strong>
                                of <strong>{{ $totalStudents }}</strong> students &mdash; marks save instantly as you type, so paging never loses entries.
                            </div>
                            <div class="pagination-links">
                                {{ $students->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </form>

    @elseif(request()->filled(['exam_id', 'class_section_id', 'subject_id']))
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-user-graduate"></i></div>
            <h4 class="empty-title">No Students Found</h4>
            <p class="empty-desc">No active enrollments for this class &amp; section.</p>
        </div>
    @endif
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

    /* Filters */
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

    /* Action bar (kept non-sticky so rows are never covered) */
    .entry-bar {
        background: #fff;
        border: 1px solid var(--border); border-radius: 14px;
        padding: 0.7rem 1rem; box-shadow: 0 4px 14px rgba(15,23,42,0.07);
    }
    .stat-chip { background: #fff; padding: 0.35rem 0.85rem; border-radius: 18px; border: 1px solid var(--border); font-size: 0.82rem; color: var(--muted); font-weight: 500; display: inline-block; }
    .stat-chip strong { color: var(--text); }
    .progress-wrap { width: 140px; }
    .progress-track { height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--indigo); border-radius: 10px; transition: width 300ms var(--ease-out); }

    /* Buttons */
    .btn-dash { display: inline-flex; align-items: center; justify-content: center; border-radius: 12px; font-size: 0.875rem; font-weight: 700; transition: all 200ms var(--ease-out); text-decoration: none !important; padding: 0.65rem 1.25rem; border: 0; cursor: pointer; }
    .btn-dash i { margin-right: 7px; }
    .btn-primary-dash { background: var(--indigo); color: #fff !important; border: 1px solid var(--indigo); }
    .btn-primary-dash:hover { background: var(--indigo-dark); transform: translateY(-1px); box-shadow: 0 6px 16px rgba(79, 70, 229, 0.28); }
    .btn-ghost { background: #fff; border: 1px solid var(--border) !important; color: var(--text) !important; }
    .btn-ghost:hover { background: #f8fafc; }

    /* Table */
    .marks-table { width: 100%; border-collapse: collapse; }
    .marks-table thead th {
        background: #fafbfd; font-size: 0.68rem; font-weight: 800; text-transform: uppercase;
        color: var(--slate); letter-spacing: 0.07em;
        padding: 0.7rem 0.9rem; border-bottom: 2px solid var(--indigo);
        text-align: left;
    }
    .marks-table tbody td { padding: 0.55rem 0.9rem; border-bottom: 1px solid #f6f8fb; vertical-align: middle; }
    .marks-table tbody tr:hover td { background: #fafbff; }

    .adm-badge { background: #fff; border: 1px solid var(--border); border-radius: 8px; padding: 2px 8px; font-size: 0.72rem; font-weight: 700; color: var(--slate); }
    .st-avatar {
        width: 34px; height: 34px; min-width: 34px; border-radius: 10px; margin-right: 11px;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff; font-weight: 800; font-size: 0.72rem;
    }
    .st-name { font-weight: 700; color: var(--text); font-size: 0.88rem; }

    .mark-input, .remark-input {
        width: 100%; border: 1.5px solid var(--border); border-radius: 10px;
        padding: 0.42rem 0.6rem; font-size: 0.92rem; font-weight: 700; color: var(--text);
        text-align: center; outline: none; background: #fff;
        transition: border-color 150ms ease, box-shadow 150ms ease;
    }
    .remark-input { font-weight: 500; text-align: left; font-size: 0.82rem; }
    .mark-input:focus, .remark-input:focus { border-color: var(--indigo); box-shadow: 0 0 0 3px rgba(79,70,229,0.10); }
    .mark-input.invalid { border-color: var(--rose); background: #fef2f2; }
    .mark-input.dirty { border-color: #f59e0b; }

    .grade-chip {
        display: inline-flex; align-items: center; gap: 5px;
        min-width: 74px; min-height: 26px; justify-content: center;
        border-radius: 16px; font-size: 0.72rem; font-weight: 800;
        background: #f8fafc; color: #cbd5e1; border: 1px dashed var(--border);
    }
    .grade-chip.filled { background: var(--indigo-light); color: var(--indigo-dark); border-style: solid; border-color: #c7d2fe; }
    .grade-chip .pts { opacity: 0.65; font-weight: 700; }
    .grade-chip.fail { background: #fef2f2; color: #b91c1c; border-color: #fecaca; }

    .saved-tick { color: var(--emerald); font-size: 1rem; display: none; }
    .saving-spin { color: var(--slate); display: none; }
    .error-cross { color: var(--rose); display: none; }
    tr.row-saving .saving-spin { display: inline-block; animation: spin 0.8s linear infinite; }
    tr.row-saved .saved-tick { display: inline-block; }
    tr.row-error .error-cross { display: inline-block; }
    tr.row-saved td { background: rgba(16, 185, 129, 0.035); }
    @keyframes spin { from { transform: rotate(0);} to { transform: rotate(360deg);} }

    .empty-state { background: #fff; border: 1px dashed var(--border); border-radius: 18px; padding: 3.5rem 2rem; text-align: center; }
    .empty-icon { width: 70px; height: 70px; margin: 0 auto 1rem; border-radius: 18px; background: var(--indigo-light); color: var(--indigo); font-size: 1.5rem; display: flex; align-items: center; justify-content: center; }
    .empty-title { font-weight: 800; color: var(--text); margin-bottom: 0.25rem; }
    .empty-desc { color: var(--muted); max-width: 420px; margin: 0 auto; }

    /* Pagination */
    .pagination-panel { background: #fff; padding: 1rem 1.25rem; border-radius: 14px; border: 1px solid var(--border); }
    .pagination-info { font-size: 0.84rem; color: var(--muted); }
    .pagination-info strong { color: var(--text); }
    .pagination { margin: 0; }
    .pagination .page-link {
        border-radius: 10px !important; margin: 0 3px; border: 1px solid var(--border);
        color: var(--slate); font-weight: 700; font-size: 0.85rem;
        min-width: 34px; text-align: center; padding: 0.35rem 0.6rem;
    }
    .pagination .page-item.active .page-link { background: var(--indigo); border-color: var(--indigo); color: #fff; }
    .pagination .page-link:focus { box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.08); }
    .pagination .disabled .page-link { opacity: 0.45; }

    @media print { .no-print { display: none !important; } }

    @media (max-width: 768px) {
        .dash-wrap { padding: 1.25rem 1rem; }
    }
</style>

@push('page_scripts')
<script>
(function () {
    'use strict';

    var MAX = {{ $maxMarks }};
    var CSRF = document.querySelector('#marks-form input[name=_token]').value;

    // Grading scale passed from server: newest-first by min percentage
    var GRADES = @json($grades);

    var rows = document.querySelectorAll('.mark-row');
    if (!rows.length) return;

    var saveUrl = '{{ route('exam-results.bulk.save-one') }}';
    var statusEl = document.getElementById('save-status');
    var enteredEl = document.getElementById('entered-count');
    var fillEl = document.getElementById('progress-fill');
    var dirtyCount = 0;

    function setStatus(text, cls) {
        statusEl.textContent = text || '';
        statusEl.className = 'ml-2 small font-weight-bold ' + (cls || 'text-muted');
    }

    function localGrade(mark) {
        if (mark === '' || mark === null || isNaN(mark)) return null;
        var pct = (parseFloat(mark) / MAX) * 100;
        for (var i = 0; i < GRADES.length; i++) {
            if (pct >= parseFloat(GRADES[i].min_percentage) && pct <= parseFloat(GRADES[i].max_percentage)) {
                return { name: GRADES[i].name, point: GRADES[i].grade_point, description: GRADES[i].description };
            }
        }
        return null;
    }

    function renderChip(row, mark, grade) {
        var chip = row.querySelector('.grade-chip');
        var g = grade || localGrade(mark);
        if (g === null && (mark === '' || mark === null)) {
            chip.className = 'grade-chip';
            chip.innerHTML = '';
            return;
        }
        var fail = g && g.name === 'E';
        chip.className = 'grade-chip filled' + (fail ? ' fail' : '');
        chip.innerHTML = g
            ? g.name + (g.point ? ' <span class="pts">' + parseFloat(g.point) + 'pts</span>' : '')
            : '?';
        chip.title = g && g.description ? g.description : '';
    }

    function updateProgress() {
        var entered = document.querySelectorAll('.mark-input').length &&
            Array.prototype.filter.call(document.querySelectorAll('.mark-input'), function (i) { return i.value !== ''; }).length;
        enteredEl.textContent = entered;
        fillEl.style.width = Math.round((entered / rows.length) * 100) + '%';
    }

    function setRowState(row, state) {
        row.classList.remove('row-saving', 'row-saved', 'row-error');
        if (state) row.classList.add(state);
    }

    function markDirty(row, dirty) {
        var input = row.querySelector('.mark-input');
        if (dirty) { input.classList.add('dirty'); dirtyCount++; }
        else { input.classList.remove('dirty'); dirtyCount = Math.max(0, dirtyCount - 1); }
    }

    function saveRow(row, thenFocusNext) {
        var input = row.querySelector('.mark-input');
        var studentId = row.dataset.studentId;

        var markVal = input.value.trim();
        if (markVal !== '' && (isNaN(parseFloat(markVal)) || parseFloat(markVal) < 0 || parseFloat(markVal) > MAX)) {
            input.classList.add('invalid');
            setRowState(row, 'row-error');
            return;
        }
        input.classList.remove('invalid');

        var fd = new FormData();
        fd.append('_token', CSRF);
        fd.append('exam_id', '{{ request("exam_id") }}');
        fd.append('class_section_id', '{{ request("class_section_id") }}');
        fd.append('subject_id', '{{ request("subject_id") }}');
        fd.append('student_id', studentId);
        fd.append('marks_obtained', markVal);
        fd.append('remarks', row.querySelector('.remark-input').value.trim());

        setRowState(row, 'row-saving');
        setStatus('Saving...');

        fetch(saveUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.json().then(function (j) { return { status: r.status, json: j }; }); })
            .then(function (res) {
                if (!res.json.ok) throw new Error(res.json.message || 'Save failed');

                renderChip(row, input.value, res.json.grade);
                setRowState(row, 'row-saved');
                if (input.classList.contains('dirty')) markDirty(row, false);

                updateProgress();
                var remaining = dirtyCount;
                setStatus(remaining > 0 ? remaining + ' unsaved' : 'All saved', remaining > 0 ? 'text-warning' : 'text-success');

                if (thenFocusNext) focusNext(row);
            })
            .catch(function () {
                setRowState(row, 'row-error');
                setStatus('Save failed - check the marked row and try again', 'text-danger');
            });
    }

    function focusNext(currentRow) {
        var allRows = Array.prototype.slice.call(rows);
        var idx = allRows.indexOf(currentRow);
        for (var i = idx + 1; i < allRows.length; i++) {
            var nextInput = allRows[i].querySelector('.mark-input');
            nextInput.focus();
            nextInput.select();
            allRows[i].scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            return;
        }
        // Wrapped past last student - refocus current
        currentRow.querySelector('.mark-input').blur();
    }

    rows.forEach(function (row) {
        var input = row.querySelector('.mark-input');
        var remark = row.querySelector('.remark-input');
        var originalMark = input.value;
        var originalRemark = remark.value;

        // Initial state + chip for already-saved marks
        if (originalMark !== '') {
            row.classList.add('row-saved');
            renderChip(row, originalMark);
        }
        updateProgress();

        // Live validation + grade preview while typing
        input.addEventListener('input', function () {
            var v = input.value.trim();
            input.classList.toggle('invalid', v !== '' && (isNaN(parseFloat(v)) || parseFloat(v) < 0 || parseFloat(v) > MAX));
            renderChip(row, v);
            if (!input.classList.contains('dirty') && v !== originalMark) markDirty(row, true);
            if (input.classList.contains('dirty') && v === originalMark) markDirty(row, false);
        });

        // Enter = save this row and jump to the next one
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveRow(row, true);
            }
        });

        // Blur with unsaved changes = auto-save quietly
        input.addEventListener('blur', function () {
            if (input.classList.contains('dirty')) saveRow(row, false);
        });

        remark.addEventListener('change', function () {
            if (remark.value.trim() !== originalRemark.trim()) saveRow(row, false);
        });
    });

    // Warn before leaving with unsaved edits
    window.addEventListener('beforeunload', function (e) {
        if (dirtyCount > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });
})();
</script>
@endpush
@endsection
