@php
    /**
     * A subject may only be deleted outright while nothing references it. When
     * it does carry history the row gets an Archive action instead, so the UI
     * offers the operation the server will actually accept.
     */
    $usageColumns = collect(\App\Services\SubjectLifecycleService::DEPENDENTS)
        ->map(fn ($meta) => $meta['label'])
        ->values();
@endphp

@if($subjects->isEmpty())
    <div class="card-body text-center p-5">
        <i class="fas fa-book-open fa-3x mb-3 text-muted"></i>
        @if(request('status') === 'archived')
            <p class="mb-0 text-muted">No archived subjects. Subjects you retire will appear here.</p>
        @elseif(request('q') || request('department_id'))
            <p class="mb-2 text-muted">No subjects match the current filters.</p>
            <a href="{{ route('subjects.index') }}" class="btn btn-light border">
                <i class="fas fa-undo mr-1"></i> Clear filters
            </a>
        @else
            <p class="mb-2 text-muted">No subjects have been created yet.</p>
            <a href="{{ route('subjects.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Add the first subject
            </a>
        @endif
    </div>
@else
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
            <tr>
                <th>Code</th>
                <th>Subject</th>
                <th>Type</th>
                <th>Department</th>
                <th class="text-center">In use</th>
                <th class="text-center">Status</th>
                <th class="text-right pr-3 text-nowrap">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($subjects as $subject)
                @php $inUse = (int) $subject->class_subjects_count
                    + (int) $subject->teacher_subjects_count
                    + (int) $subject->assignments_count
                    + (int) $subject->exam_results_count
                    + (int) $subject->exam_schedules_count
                    + (int) $subject->timetable_count; @endphp
                <tr class="{{ $subject->is_active ? '' : 'table-warning' }}">
                    <td><span class="badge badge-light border">{{ $subject->subject_code }}</span></td>
                    <td>
                        <a href="{{ route('subjects.show', $subject->subject_id) }}" class="font-weight-bold">
                            {{ $subject->name }}
                        </a>
                        @if($subject->description)
                            <div class="small text-muted">
                                {{ \Illuminate\Support\Str::limit($subject->description, 70) }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($subject->is_elective)
                            <span class="badge badge-info">Elective</span>
                        @else
                            <span class="badge badge-success">Core</span>
                        @endif
                    </td>
                    <td>{{ $subject->department?->name ?? '—' }}</td>
                    <td class="text-center">
                        @if($inUse > 0)
                            <span class="badge badge-warning" title="{{ $usageColumns->implode(', ') }}">
                                {{ $inUse }} {{ \Illuminate\Support\Str::plural('record', $inUse) }}
                            </span>
                        @else
                            <span class="badge badge-secondary">Not in use</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($subject->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-warning">Archived</span>
                        @endif
                    </td>
                    <td class="text-right pr-3 text-nowrap">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('subjects.show', $subject->subject_id) }}"
                               class="btn btn-light border" title="View subject">
                                <i class="fas fa-eye text-primary"></i>
                            </a>
                            @can('academics.settings.manage')
                                <a href="{{ route('subjects.edit', $subject->subject_id) }}"
                                   class="btn btn-light border" title="Edit subject">
                                    <i class="fas fa-edit text-info"></i>
                                </a>

                                @if($subject->is_active)
                                    @if($inUse > 0)
                                        {{-- Carries history: deleting is refused, so offer Archive. --}}
                                        {!! Form::open(['route' => ['subjects.archive', $subject->subject_id], 'method' => 'post', 'class' => 'd-inline']) !!}
                                            {!! Form::button('<i class="fas fa-archive text-warning"></i>', [
                                                'type' => 'submit',
                                                'class' => 'btn btn-light border',
                                                'title' => 'Archive subject (history is preserved)',
                                                'onclick' => "return confirm('Archive “" . addslashes($subject->name) . "\”?\n\nAll marks, exam sittings and timetable slots are kept. The subject simply stops being offered for new allocations.')",
                                            ]) !!}
                                        {!! Form::close() !!}
                                    @else
                                        {!! Form::open(['route' => ['subjects.destroy', $subject->subject_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                            {!! Form::button('<i class="fas fa-trash-alt text-danger"></i>', [
                                                'type' => 'submit',
                                                'class' => 'btn btn-light border',
                                                'title' => 'Delete subject',
                                                'onclick' => "return confirm('Delete “" . addslashes($subject->name) . "\"?\n\nThis subject is not referenced anywhere, so nothing will be lost. This cannot be undone.')",
                                            ]) !!}
                                        {!! Form::close() !!}
                                    @endif
                                @else
                                    {!! Form::open(['route' => ['subjects.restore', $subject->subject_id], 'method' => 'post', 'class' => 'd-inline']) !!}
                                        {!! Form::button('<i class="fas fa-undo text-success"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-light border',
                                            'title' => 'Restore subject to the active catalog',
                                        ]) !!}
                                    {!! Form::close() !!}
                                @endif
                            @endcan
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-light clearfix">
        <div class="float-left small text-muted pt-1">
            Showing <strong>{{ $subjects->firstItem() }}</strong> to <strong>{{ $subjects->lastItem() }}</strong>
            of <strong>{{ $subjects->total() }}</strong> {{ \Illuminate\Support\Str::plural('subject', $subjects->total()) }}
        </div>
        <div class="float-right">
            {!! $subjects->links() !!}
        </div>
    </div>
@endif
