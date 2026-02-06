@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-building text-info mr-2"></i>Department: {{ $department->name }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default mr-2" href="{{ route('departments.index') }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a class="btn btn-info" href="{{ route('departments.edit', $department->department_id) }}">
                        <i class="fas fa-edit"></i> Edit Department
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Department Profile -->
            <div class="col-md-4">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Department Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info elevation-1 mb-3 shadow-sm" style="width: 80px; height: 80px; border-radius: 12px; border-bottom: 4px solid rgba(0,0,0,0.1);">
                                <i class="fas fa-building fa-2x"></i>
                            </div>
                            <h4 class="font-weight-bold mb-0">{{ $department->name }}</h4>
                            <p class="text-muted small">Academic Management Unit</p>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <label class="text-muted small text-uppercase d-block mb-1">Head of Department (HOD)</label>
                            @if($department->hod)
                                <div class="d-flex align-items-center p-2 rounded bg-light border">
                                    <img src="{{ asset('garikon-black.png') }}" alt="Staff" class="img-circle img-sm mr-3 elevation-1" style="width: 40px; height: 40px;">
                                    <div>
                                        <div class="font-weight-bold text-dark">{{ $department->hod->full_name }}</div>
                                        <div class="x-small text-info text-uppercase font-weight-bold">{{ $department->hod->designation ?? 'HOD' }}</div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center p-3 border border-dashed rounded bg-light">
                                    <span class="text-muted small italic">No HOD assigned</span>
                                </div>
                            @endif
                        </div>

                        <div class="mb-3 text-muted">
                            <label class="text-muted small text-uppercase d-block mb-1">Description</label>
                            <p class="bg-light p-2 rounded small" style="min-height: 80px;">
                                {{ $department->description ?: 'No description provided for this department.' }}
                            </p>
                        </div>

                        <div class="row mb-2">
                            <div class="col-6">
                                <span class="text-muted small text-uppercase d-block">Total Staff</span>
                                <h5 class="font-weight-bold">{{ $department->staff->count() }}</h5>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small text-uppercase d-block">Total Subjects</span>
                                <h5 class="font-weight-bold text-info">{{ $department->subjects->count() }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Tabs -->
            <div class="col-md-8">
                <div class="card card-primary card-outline card-outline-tabs elevation-2">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="depTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="staff-tab" data-toggle="pill" href="#staff" role="tab" aria-controls="staff" aria-selected="true">
                                    <i class="fas fa-users mr-1"></i> Department Staff
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="subjects-tab" data-toggle="pill" href="#subjects" role="tab" aria-controls="subjects" aria-selected="false">
                                    <i class="fas fa-book mr-1"></i> Assigned Subjects
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="depTabContent">
                            <!-- Staff Tab -->
                            <div class="tab-pane fade show active" id="staff" role="tabpanel" aria-labelledby="staff-tab">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="bg-light x-small text-uppercase text-muted">
                                        <tr>
                                            <th>Staff Member</th>
                                            <th>Designation</th>
                                            <th>Joining Date</th>
                                            <th class="text-right px-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($department->staff as $s)
                                            <tr>
                                                <td class="align-middle px-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="mr-3 font-weight-bold text-dark">{{ $s->full_name }}</div>
                                                        <div class="x-small text-muted">ID: {{ $s->employee_id }}</div>
                                                    </div>
                                                </td>
                                                <td class="align-middle"><span class="small">{{ $s->designation }}</span></td>
                                                <td class="align-middle"><span class="small text-muted">{{ $s->joining_date ? $s->joining_date->format('M d, Y') : 'N/A' }}</span></td>
                                                <td class="align-middle text-right px-4">
                                                    <a href="{{ route('staff.show', $s->staff_id) }}" class="btn btn-xs btn-outline-info">Profile</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-users-slash fa-3x mb-3 d-block opacity-50"></i>
                                                    No staff members in this department.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Subjects Tab -->
                            <div class="tab-pane fade" id="subjects" role="tabpanel" aria-labelledby="subjects-tab">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="bg-light x-small text-uppercase text-muted">
                                        <tr>
                                            <th>Subject Name</th>
                                            <th>Code</th>
                                            <th class="text-center">Primary Type</th>
                                            <th class="text-right px-4">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($department->subjects as $sub)
                                            <tr>
                                                <td class="align-middle px-4">
                                                    <i class="fas fa-book text-muted mr-2"></i>
                                                    <strong>{{ $sub->name }}</strong>
                                                </td>
                                                <td class="align-middle"><code>{{ $sub->subject_code }}</code></td>
                                                <td class="text-center align-middle">
                                                    @if($sub->is_elective)
                                                        <span class="badge badge-warning">Elective</span>
                                                    @else
                                                        <span class="badge badge-success">Core</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle text-right px-4">
                                                    <a href="{{ route('subjects.show', $sub->subject_id) }}" class="btn btn-xs btn-outline-info">View</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-5 text-muted">
                                                    <i class="fas fa-book-open fa-3x mb-3 d-block opacity-50"></i>
                                                    No subjects assigned to this department.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .elevation-2 { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06) !important; }
        .x-small { font-size: 0.75rem; }
        .shadow-sm { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important; }
        .border-dashed { border-style: dashed !important; border-width: 1px !important; }
    </style>
@endsection
