@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-book-open text-info mr-2"></i>Subject Details
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default mr-2" href="{{ route('subjects.index') }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a class="btn btn-info" href="{{ route('subjects.edit', $subject->subject_id) }}">
                        <i class="fas fa-edit"></i> Edit Subject
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-5">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">General Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center mb-4 text-center">
                            <div class="mb-3 bg-light elevation-1 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; border: 3px solid #17a2b8;">
                                <i class="fas fa-book-open fa-2x text-info"></i>
                            </div>
                            <h4 class="mb-0 font-weight-bold text-dark">{{ $subject->name }}</h4>
                            <span class="badge badge-info mt-2 px-3">{{ $subject->subject_code }}</span>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-4 font-weight-bold text-muted">Status:</div>
                            <div class="col-8">
                                @if($subject->is_elective)
                                    <span class="badge badge-warning"><i class="fas fa-star mr-1"></i> Elective</span>
                                @else
                                    <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Core/Compulsory</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-4 font-weight-bold text-muted">Created:</div>
                            <div class="col-8 small">{{ $subject->created_at ? $subject->created_at->format('M d, Y') : 'N/A' }}</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12 font-weight-bold text-muted mb-2">Description:</div>
                            <div class="col-12">
                                <p class="text-muted border p-2 rounded bg-light" style="min-height: 100px;">
                                    {{ $subject->description ?: 'No description provided for this subject.' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Assignments & Teachers -->
            <div class="col-md-7">
                <!-- Related Classes -->
                <div class="card card-outline card-primary elevation-2 mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold text-primary">
                            <i class="fas fa-chalkboard mr-1"></i> Assigned Classes
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ $subject->classSubjects->count() }} Classes</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="x-small text-uppercase text-muted bg-light">
                                <tr>
                                    <th>Class Name</th>
                                    <th>Academic Year</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subject->classSubjects as $cs)
                                    <tr>
                                        <td>
                                            <i class="fas fa-chevron-right text-xs text-primary mr-2"></i>
                                            <strong>{{ $cs->class->name ?? 'Unknown' }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-pill badge-light border">{{ $cs->academicYear->name ?? 'Current' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">No classes assigned to this subject.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Assigned Teachers -->
                <div class="card card-outline card-success elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold text-success">
                            <i class="fas fa-user-tie mr-1"></i> Teaching Staff
                        </h3>
                        <div class="card-tools">
                             <span class="badge badge-success">{{ $subject->teacherSubjects->count() }} Teachers</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="x-small text-uppercase text-muted bg-light">
                                <tr>
                                    <th>Teacher Name</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subject->teacherSubjects as $ts)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset('garikon-black.png') }}" alt="Staff" class="img-circle img-sm mr-3 elevation-1" style="width: 32px; height: 32px; object-fit: cover;">
                                                <div>
                                                    <div class="font-weight-bold text-dark">{{ $ts->staff->full_name ?? 'N/A' }}</div>
                                                    <div class="x-small text-muted">{{ $ts->staff->employee_id ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $ts->staff->department->name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center py-4 text-muted">No teachers assigned to this subject.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .elevation-2 { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06) !important; }
        .x-small { font-size: 0.75rem; }
    </style>
@endsection
