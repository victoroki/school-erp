{{--
    Card shells only — one per class, no subject rows.

    The grid is scoped to the current academic year, so a class appears exactly
    once. Each card ships its counts, a search haystack and its destructive
    targets; the subject list arrives from class-subjects.curriculum the first
    time the card is opened, which is what keeps a 14-class school from
    rendering 336 <li> rows (and 336 forms) before anything is readable.
--}}
@if($classSubjects->isEmpty())
    <div class="cs-empty">
        <div class="cs-empty-icon"><i class="fas fa-book-reader"></i></div>
        <h2 class="cs-empty-title">No subjects assigned for {{ $currentYear->name }} yet</h2>
        <p class="cs-empty-text">
            Assign subjects to a class to build its curriculum. Each class keeps its
            own subject list per academic year.
        </p>
        <a href="{{ route('class-subjects.create') }}" class="btn-dash btn-primary-dash mt-3">
            <i class="fas fa-plus me-1"></i> Assign subjects
        </a>
    </div>
@else
    <div class="row" id="class-subjects-grid">
        @foreach($classSubjects as $group)
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 class-group mb-3"
                 data-group
                 data-class-id="{{ $group->class_id }}"
                 data-subject-count="{{ $group->subject_count }}"
                 data-search="{{ $group->search }}">

                {{-- <details> rather than a button + aria-expanded: the browser
                     owns the open state, so collapsing works without JS and the
                     disclosure is announced correctly by screen readers. --}}
                <details class="cs-disclosure" data-card>
                    <summary class="cs-header">
                        <span class="cs-chevron" aria-hidden="true"><i class="fas fa-chevron-down"></i></span>
                        <span class="cs-title">{{ $group->class_name }}</span>

                        <span class="cs-header-actions">
                            @if($group->archived_count > 0)
                                {{-- Archived subjects stay visible in the list, so the
                                     count has to stay visible with the card shut. --}}
                                <span class="cs-badge cs-badge-warn"
                                      title="{{ $group->archived_count }} archived subject(s) in this class">
                                    {{ $group->archived_count }} archived
                                </span>
                            @endif

                            <span class="cs-badge cs-badge-count">
                                {{ $group->subject_count }} {{ Str::plural('subject', $group->subject_count) }}
                            </span>

                            {{-- Data, not markup: one shared form at the foot of
                                 the page submits whichever card was pressed, and
                                 the confirmation is read in JS. An inline
                                 onclick also broke on any subject name
                                 containing an apostrophe. --}}
                            <button type="button" class="action-btn btn-delete" data-clear
                                    data-class-id="{{ $group->class_id }}"
                                    data-year-id="{{ $group->academic_year_id }}"
                                    data-confirm="Remove all {{ $group->subject_count }} subject(s) from {{ $group->class_name }}? Subjects in other years are not affected."
                                    title="Clear all {{ $group->subject_count }} subjects from {{ $group->class_name }}">
                                <i class="far fa-trash-alt"></i>
                                <span class="sr-only">Clear all subjects from {{ $group->class_name }}</span>
                            </button>
                        </span>
                    </summary>

                    <div class="cs-body" id="cs-body-{{ $group->class_id }}">
                        <ul class="cs-list" data-list>
                            <noscript>
                                <li class="cs-hint">Turn on JavaScript to load this class&rsquo;s subjects.</li>
                            </noscript>
                        </ul>
                    </div>

                    <footer class="cs-footer">
                        <span>{{ $group->weekly_periods }} periods / week</span>
                        <span>{{ $currentYear->name }}</span>
                    </footer>
                </details>
            </div>
        @endforeach
    </div>

    {{-- Two forms for the whole page instead of one per subject row: the delete
         target and the class/year pair are set by JS just before submitting. --}}
    {!! Form::open(['route' => ['class-subjects.destroy', 0], 'method' => 'delete', 'id' => 'classSubjectDeleteForm', 'class' => 'd-none']) !!}
    {!! Form::close() !!}

    {!! Form::open(['route' => 'class-subjects.bulk-delete', 'method' => 'post', 'id' => 'classSubjectClearForm', 'class' => 'd-none']) !!}
        {!! Form::hidden('class_id', '') !!}
        {!! Form::hidden('academic_year_id', '') !!}
    {!! Form::close() !!}
@endif
