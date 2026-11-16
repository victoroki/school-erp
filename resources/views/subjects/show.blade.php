@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1>
                        <i class="fas fa-book-open mr-2 text-primary"></i> {{ $subject->name }}
                    </h1>
                    <p class="text-muted small mb-0">
                        Subject code <strong>{{ $subject->subject_code }}</strong>
                        @if($subject->department)
                            &middot; {{ $subject->department->name }} department
                        @endif
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    @can('academics.settings.manage')
                        @if($subject->is_active)
                            {!! Form::open(['route' => ['subjects.archive', $subject->subject_id], 'method' => 'post', 'class' => 'd-inline mb-1 mr-1']) !!}
                                {!! Form::button('<i class="fas fa-archive mr-1"></i> Archive', [
                                    'type' => 'submit',
                                    'class' => 'btn btn-warning',
                                    'onclick' => $hasHistory
                                        ? "return confirm('Archive “" . addslashes($subject->name) . "\"?\n\nAll marks, exam sittings and timetable slots are kept. The subject simply stops being offered for new allocations.')"
                                        : '',
                                ]) !!}
                            {!! Form::close() !!}
                        @else
                            {!! Form::open(['route' => ['subjects.restore', $subject->subject_id], 'method' => 'post', 'class' => 'd-inline mb-1 mr-1']) !!}
                                {!! Form::button('<i class="fas fa-undo mr-1"></i> Restore', [
                                    'type' => 'submit',
                                    'class' => 'btn btn-success',
                                ]) !!}
                            {!! Form::close() !!}
                        @endif
                        <a class="btn btn-primary mb-1 mr-1" href="{{ route('subjects.edit', $subject->subject_id) }}">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    @endcan
                    <a class="btn btn-default mb-1" href="{{ route('subjects.index') }}">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        <div class="clearfix"></div>

        @unless($subject->is_active)
            <div class="alert alert-warning">
                <i class="fas fa-archive mr-1"></i>
                <strong>This subject is archived.</strong> Its full history is preserved and every past
                mark and timetable entry still resolves, but it is no longer offered when allocating
                subjects to classes. Use <em>Restore</em> to put it back into the active catalog.
            </div>
        @endunless

        @if($hasHistory)
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>This subject has academic history and cannot be deleted.</strong>
                Deleting it would orphan the records below. Archive it instead — that keeps every
                mark, sitting and slot intact.
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card card-outline card-primary elevation-2 mb-4 mb-lg-0">
                    <div class="card-header bg-light">
                        <h3 class="card-title text-uppercase small font-weight-bold">Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="text-uppercase text-muted small font-weight-bold mb-1">Type</div>
                            @if($subject->is_elective)
                                <span class="badge badge-info">Elective</span>
                            @else
                                <span class="badge badge-success">Core Curriculum</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="text-uppercase text-muted small font-weight-bold mb-1">Description</div>
                            <p class="mb-0">{{ $subject->description ?: 'No description provided for this subject.' }}</p>
                        </div>

                        <div class="mb-0">
                            <div class="text-uppercase text-muted small font-weight-bold mb-1">Registered</div>
                            {{ $subject->created_at ? $subject->created_at->format('d M Y') : '—' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card card-outline card-primary elevation-2 mb-4">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-uppercase small font-weight-bold mb-0">
                            <i class="fas fa-link mr-1 text-primary"></i> Where this subject is used
                        </h3>
                        <span class="badge badge-secondary">
                            {{ array_sum(array_column($usage, 'count')) }} {{ \Illuminate\Support\Str::plural('record', array_sum(array_column($usage, 'count'))) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($usage as $entry)
                                <div class="col-6 col-md-4 mb-3">
                                    <div class="border rounded p-2 h-100 text-center">
                                        <div class="h3 mb-0 {{ $entry['count'] > 0 ? 'text-primary' : 'text-muted' }}">
                                            {{ $entry['count'] }}
                                        </div>
                                        <div class="small text-muted">{{ $entry['label'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card card-outline card-primary elevation-2 mb-4">
                    <div class="card-header bg-light">
                        <h3 class="card-title text-uppercase small font-weight-bold">
                            <i class="fas fa-school mr-1 text-primary"></i> Assigned Classes
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        @if($subject->classSubjects->isEmpty())
                            <div class="card-body text-center text-muted py-4">
                                This subject is not allocated to any class.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>Class</th>
                                        <th>Academic year</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($subject->classSubjects as $classSubject)
                                        <tr>
                                            <td>{{ $classSubject->class->name ?? 'Unknown class' }}</td>
                                            <td>
                                                <span class="badge badge-light border">
                                                    {{ $classSubject->academicYear->name ?? '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card card-outline card-primary elevation-2">
                    <div class="card-header bg-light">
                        <h3 class="card-title text-uppercase small font-weight-bold">
                            <i class="fas fa-user-tie mr-1 text-success"></i> Teaching Staff
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        @if($subject->teacherSubjects->isEmpty())
                            <div class="card-body text-center text-muted py-4">
                                No teacher is allocated to this subject.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>Staff member</th>
                                        <th>Department</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($subject->teacherSubjects as $teacherSubject)
                                        <tr>
                                            <td>
                                                <div class="font-weight-bold">
                                                    {{ $teacherSubject->staff->full_name ?? 'Unknown staff member' }}
                                                </div>
                                                <div class="small text-muted">
                                                    {{ $teacherSubject->staff->employee_id ?? '—' }}
                                                </div>
                                            </td>
                                            <td>{{ $teacherSubject->staff->department->name ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
