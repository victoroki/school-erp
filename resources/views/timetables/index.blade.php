@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Timetables</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('timetables.create') }}">
                        Add Lesson
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card mb-3">
            <div class="card-body">
                {!! Form::open(['route' => 'timetables.index', 'method' => 'GET', 'id' => 'timetable-filter-form']) !!}
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        {!! Form::label('academic_year_id', 'Academic Year') !!}
                        {!! Form::select(
                            'academic_year_id',
                            $academicYearOptions,
                            $selectedAcademicYearId,
                            ['class' => 'form-control', 'id' => 'academic_year_id', 'placeholder' => 'Select academic year']
                        ) !!}
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('class_section_id', 'Class and Section') !!}
                        <select name="class_section_id" id="class_section_id" class="form-control">
                            @if($classSectionOptions->isEmpty())
                                <option value="">— Select academic year first —</option>
                            @else
                                <option value="">Select class and section</option>
                                @foreach($classSectionOptions as $id => $label)
                                    <option value="{{ $id }}" {{ $id == $selectedClassSectionId ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        {{-- Loading spinner (hidden by default) --}}
                        <small id="class-section-loading" class="text-muted d-none">
                            <i class="fas fa-spinner fa-spin"></i> Loading classes…
                        </small>
                    </div>
                    <div class="form-group col-md-4 d-flex">
                        <div class="ml-md-auto mt-4">
                            <button type="submit" id="apply-btn" class="btn btn-primary mr-2">
                                Apply
                            </button>
                            <a href="{{ route('timetables.index') }}" class="btn btn-default">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        @if($academicYearOptions->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted">
                    Set up at least one academic year before creating timetables.
                </div>
            </div>
        @elseif($classSectionOptions->isEmpty())
            <div class="card" id="no-sections-msg">
                <div class="card-body text-center text-muted">
                    No class sections found for the selected academic year.
                </div>
            </div>
        @elseif($periods->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted">
                    Define periods before building timetables.
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th style="width: 140px;">Day / Period</th>
                            @foreach($periods as $period)
                                <th class="text-center">
                                    <div>{{ $period->name }}</div>
                                    <div class="text-muted small">
                                        {{ $period->start_time }} – {{ $period->end_time }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                            <tr>
                                <td><strong>{{ $dayLabel }}</strong></td>
                                @foreach($periods as $period)
                                    @php
                                        $entry = $schedule[$dayKey][$period->period_id] ?? null;
                                    @endphp
                                    <td class="align-top">
                                        @if($entry)
                                            <div class="font-weight-bold">
                                                {{ $entry->subject->name }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $entry->teacher ? trim(($entry->teacher->first_name ?? '') . ' ' . ($entry->teacher->last_name ?? '')) : '' }}
                                            </div>
                                            <div class="text-muted small">
                                                Room {{ $entry->classroom->room_number }}
                                            </div>
                                            <div class="mt-1">
                                                <a href="{{ route('timetables.edit', $entry->timetable_id) }}"
                                                   class="btn btn-outline-primary btn-xs">
                                                    Edit
                                                </a>
                                                <a href="{{ route('timetables.show', $entry->timetable_id) }}"
                                                   class="btn btn-outline-secondary btn-xs">
                                                    Details
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted small">Free</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('page_scripts')
<script>
(function () {
    'use strict';

    var academicYearSelect  = document.getElementById('academic_year_id');
    var classSectionSelect  = document.getElementById('class_section_id');
    var loadingIndicator    = document.getElementById('class-section-loading');
    var filterForm          = document.getElementById('timetable-filter-form');

    /**
     * Fetch class sections for the given academic year via AJAX and
     * populate the class_section_id <select> element.
     *
     * @param {string|number} academicYearId
     * @param {string|number|null} preselectId  – value to pre-select after fetching
     * @param {boolean} autoSubmit              – submit the form after populating
     */
    function loadClassSections(academicYearId, preselectId, autoSubmit) {
        if (!academicYearId) {
            resetClassSectionDropdown('— Select academic year first —');
            return;
        }

        // Show loading state
        classSectionSelect.disabled = true;
        loadingIndicator.classList.remove('d-none');
        resetClassSectionDropdown('Loading…');

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

                // Auto-select the first real option if nothing was pre-selected
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
            resetClassSectionDropdown('Error loading classes — please try again');
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

    // React to academic year selection changes
    academicYearSelect.addEventListener('change', function () {
        var selectedYear = this.value;
        // Fetch sections and auto-submit the filter form so the timetable grid refreshes
        loadClassSections(selectedYear, null, true);
    });

})();
</script>
@endpush

