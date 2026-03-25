@push('page_css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.1.2/dist/select2-bootstrap4.min.css">
    <style>
        .select2-container .select2-selection--single { height: 38px !important; }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px !important; }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px !important; }
    </style>
@endpush

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Class Section Field                                                      --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select(
        'class_section_id',
        ['' => 'Select Class Section'] + $classSections,
        old('class_section_id', $timetable->class_section_id ?? null),
        ['class' => 'form-control', 'id' => 'class_section_id', 'required']
    ) !!}
</div>

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Day Of Week Field                                                        --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    {!! Form::label('day_of_week', 'Day Of Week:') !!}
    {!! Form::select(
        'day_of_week',
        ['' => 'Select Day'] + $daysOfWeek,
        old('day_of_week', $timetable->day_of_week ?? null),
        ['class' => 'form-control', 'required']
    ) !!}
</div>

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Period Field                                                             --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    {!! Form::label('period_id', 'Period:') !!}
    {!! Form::select(
        'period_id',
        ['' => 'Select Period'] + $periods,
        old('period_id', $timetable->period_id ?? null),
        ['class' => 'form-control', 'required']
    ) !!}
</div>

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Academic Year Field                                                      --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select(
        'academic_year_id',
        ['' => 'Select Academic Year'] + $academicYears,
        old('academic_year_id', $timetable->academic_year_id ?? null),
        ['class' => 'form-control', 'id' => 'academic_year_id', 'required']
    ) !!}
</div>

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Subject Field  (drives the Teacher filter below)                        --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select(
        'subject_id',
        ['' => 'Select Subject'] + $subjects,
        old('subject_id', $timetable->subject_id ?? null),
        ['class' => 'form-control', 'id' => 'subject_id', 'required']
    ) !!}
    <small class="text-muted">Selecting a subject will filter the teacher list below.</small>
</div>

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Teacher Field  (filtered dynamically by the selected Subject)           --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    <label for="teacher_id">
        Teacher:
        {{-- Badge shown when the list is filtered --}}
        <span id="teacher-count-badge" class="badge badge-info ml-1 d-none"></span>
    </label>

    <div class="input-group">
        <select name="teacher_id"
                id="teacher_id"
                class="form-control select2"
                required>
            @if(isset($timetable) && $timetable->teacher_id)
                {{-- On edit, keep the current teacher pre-selected; JS will refine on load --}}
                @foreach($teachers as $tid => $tname)
                    <option value="{{ $tid }}" {{ $tid == $timetable->teacher_id ? 'selected' : '' }}>
                        {{ $tname }}
                    </option>
                @endforeach
            @else
                <option value="">— Select a subject first —</option>
            @endif
        </select>
        <div class="input-group-append">
            <span class="input-group-text" id="teacher-loading" style="display:none;">
                <i class="fas fa-spinner fa-spin"></i>
            </span>
        </div>
    </div>

    <small id="teacher-hint" class="text-muted d-none">
        Showing only teachers assigned to teach the selected subject.
        <a href="#" id="teacher-show-all" class="ml-1">Show all teachers</a>
    </small>
    <small id="teacher-no-result" class="text-warning d-none">
        <i class="fas fa-exclamation-triangle"></i>
        No teacher is assigned to this subject yet.
        <a href="{{ route('teacher-subjects.index') }}" target="_blank">Assign one</a>
    </small>
</div>

{{-- ─────────────────────────────────────────────────────────────────────── --}}
{{-- Classroom Field                                                          --}}
{{-- ─────────────────────────────────────────────────────────────────────── --}}
<div class="form-group col-sm-6">
    {!! Form::label('classroom_id', 'Classroom:') !!}
    {!! Form::select(
        'classroom_id',
        ['' => 'Select Classroom'] + $classrooms,
        old('classroom_id', $timetable->classroom_id ?? null),
        ['class' => 'form-control', 'required']
    ) !!}
</div>

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
(function () {
    'use strict';

    /* ── DOM references ─────────────────────────────────────────────────── */
    var subjectSel      = document.getElementById('subject_id');
    var classSectSel    = document.getElementById('class_section_id');
    var acadYearSel     = document.getElementById('academic_year_id');
    var teacherSel      = document.getElementById('teacher_id');
    var loadingSpinner  = document.getElementById('teacher-loading');
    var countBadge      = document.getElementById('teacher-count-badge');
    var hintEl          = document.getElementById('teacher-hint');
    var noResultEl      = document.getElementById('teacher-no-result');
    var showAllLink     = document.getElementById('teacher-show-all');

    /* ── Full teacher list (PHP → JS, used when "show all" is clicked) ──── */
    var allTeachers = @json($teachers);   // { id: name, ... }

    /* ── Helpers ─────────────────────────────────────────────────────────── */
    function setLoading(on) {
        $(teacherSel).prop('disabled', on);
        loadingSpinner.style.display = on ? 'flex' : 'none';
        // Refresh select2 state
        if (typeof $.fn.select2 !== 'undefined') {
            $(teacherSel).select2({ theme: 'bootstrap4', placeholder: 'Loading...' });
        }
    }

    function showHint(filteredCount) {
        countBadge.textContent = filteredCount + ' teacher' + (filteredCount !== 1 ? 's' : '');
        countBadge.classList.remove('d-none');
        hintEl.classList.remove('d-none');
        noResultEl.classList.add('d-none');
    }

    function showNoResult() {
        countBadge.classList.add('d-none');
        hintEl.classList.add('d-none');
        noResultEl.classList.remove('d-none');
    }

    function clearHints() {
        countBadge.classList.add('d-none');
        hintEl.classList.add('d-none');
        noResultEl.classList.add('d-none');
    }

    function buildOptions(list, preselectId) {
        teacherSel.innerHTML = '';

        var placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = list.length === 0
            ? '— No teachers found for this subject —'
            : 'Select Teacher';
        teacherSel.appendChild(placeholder);

        list.forEach(function (t) {
            var opt       = document.createElement('option');
            opt.value     = t.id;
            opt.textContent = t.name;
            if (preselectId && String(t.id) === String(preselectId)) {
                opt.selected = true;
            }
            teacherSel.appendChild(opt);
        });

        // Trigger Select2 update
        if (typeof $.fn.select2 !== 'undefined') {
            $(teacherSel).select2({ theme: 'bootstrap4' }).trigger('change');
        }
    }

    function populateAllTeachers(preselectId) {
        var list = Object.entries(allTeachers).map(function (e) {
            return { id: e[0], name: e[1] };
        });
        buildOptions(list, preselectId);
        clearHints();
    }

    /* ── Core AJAX fetch ─────────────────────────────────────────────────── */
    function fetchTeachersForSubject(subjectId, preselectId) {
        var classSectionId = classSectSel ? classSectSel.value : '';
        var academicYearId = acadYearSel  ? acadYearSel.value  : '';

        var url = '{{ url("api/subjects") }}/' + encodeURIComponent(subjectId) + '/teachers'
            + '?class_section_id=' + encodeURIComponent(classSectionId)
            + '&academic_year_id=' + encodeURIComponent(academicYearId);

        setLoading(true);

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
        })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            // data is now an array of { id, name }
            buildOptions(data, preselectId);

            if (data.length === 0) {
                showNoResult();
            } else {
                showHint(data.length);
            }
        })
        .catch(function (err) {
            console.error('Teacher fetch error:', err);
            // Fall back to full list so the form is still usable
            populateAllTeachers(preselectId);
        })
        .finally(function () {
            setLoading(false);
        });
    }

    /* ── Event listeners ─────────────────────────────────────────────────── */
    subjectSel.addEventListener('change', function () {
        clearHints();
        if (!this.value) {
            populateAllTeachers(null);
            return;
        }
        fetchTeachersForSubject(this.value, null);
    });

    // Re-filter when class section changes (narrows teachers further)
    if (classSectSel) {
        classSectSel.addEventListener('change', function () {
            if (subjectSel.value) {
                fetchTeachersForSubject(subjectSel.value, teacherSel.value || null);
            }
        });
    }

    // "Show all teachers" link
    showAllLink.addEventListener('click', function (e) {
        e.preventDefault();
        populateAllTeachers(teacherSel.value || null);
    });

    /* ── On page load: if a subject is already selected (Edit form), filter ─ */
    (function init() {
        var selectedSubject = subjectSel.value;
        var selectedTeacher = teacherSel.value;   // already set by server on Edit

        if (selectedSubject) {
            fetchTeachersForSubject(selectedSubject, selectedTeacher);
        }
        // On Create with no subject, show the "select subject first" placeholder
        if (!selectedSubject) {
            teacherSel.innerHTML = '<option value="">— Select a subject first —</option>';
        }

        // Global init for all select2 on the page
        if (typeof $.fn.select2 !== 'undefined') {
            $('.select2').select2({ theme: 'bootstrap4' });
        }
    })();

})();
</script>
@endpush
