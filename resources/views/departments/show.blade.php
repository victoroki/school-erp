@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6 text-dark">
                    <h1 class="font-weight-bold" style="font-size: 1.75rem;">
                        <i class="fas fa-sitemap text-primary mr-2"></i> Department: {{ $department->name }}
                    </h1>
                    <p class="text-muted mb-0">Overview of faculty members, staff, and assigned curriculum.</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <a class="btn px-4 py-2 shadow-sm mr-2"
                       href="{{ route('departments.index') }}"
                       style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                    <a class="btn btn-primary px-4 py-2 shadow-sm"
                       href="{{ route('departments.edit', $department->department_id) }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-edit mr-2"></i> Edit Department
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        <div class="row">
            <!-- Left Column: Department Profile -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-0">
                        <!-- Gradient Header -->
                        <div class="p-4 text-center" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
                            <div class="bg-white shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 20px;">
                                <i class="fas fa-building fa-3x text-primary"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-1">{{ $department->name }}</h4>
                            <p class="text-blue small font-weight-bold mb-0">Academic Management</p>
                        </div>
                        
                        <div class="p-4">
                            <!-- HOD Profile -->
                            <div class="mb-4">
                                <label class="text-muted d-block small mb-2" style="font-weight: 700; text-transform: uppercase;">Head of Department</label>
                                @if($department->hod)
                                    <div class="d-flex align-items-center p-3 rounded" style="background-color: #f8fafc; border: 1px solid #eef2f6;">
                                        <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center bg-white shadow-sm text-primary font-weight-bold" style="width: 45px; height: 45px; border: 1px solid #e2e8f0;">
                                            {{ strtoupper(substr($department->hod->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-weight-bold text-dark">{{ $department->hod->full_name }}</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">HOD / Senior Faculty</div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center p-3 rounded border border-dashed text-muted small">
                                        No Head of Department assigned.
                                    </div>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Description</label>
                                <p class="text-dark small mb-0" style="line-height: 1.6;">
                                    {{ $department->description ?: 'No detailed description available for this department.' }}
                                </p>
                            </div>

                            <div class="row pt-3 border-top text-center mt-4">
                                <div class="col-6 border-right">
                                    <h5 class="font-weight-bold text-dark mb-0">{{ $department->staff->count() }}</h5>
                                    <small class="text-muted font-weight-bold">Staff</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="font-weight-bold text-primary mb-0">{{ $department->subjects->count() }}</h5>
                                    <small class="text-muted font-weight-bold">Subjects</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Tabs -->
            <div class="col-lg-8 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-white p-0 border-bottom-0">
                        <ul class="nav nav-tabs nav-fill" id="departmentTab" role="tablist" style="border-bottom: 2px solid #f1f5f9;">
                            <li class="nav-item">
                                <a class="nav-link active py-3 font-weight-bold border-0" id="staff-tab" data-toggle="pill" href="#staff-content" role="tab" style="color: #475569; border-bottom: 2px solid transparent !important;">
                                    <i class="fas fa-users-cog mr-2"></i> Faculty & Staff
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link py-3 font-weight-bold border-0" id="subjects-tab" data-toggle="pill" href="#subjects-content" role="tab" style="color: #475569; border-bottom: 2px solid transparent !important;">
                                    <i class="fas fa-book-open mr-2"></i> Assigned Subjects
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="departmentTabContent">
                            <!-- Staff List -->
                            <div class="tab-pane fade show active" id="staff-content" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead style="background-color: #f8fafc;">
                                            <tr>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-bold text-uppercase">Staff Member</th>
                                                <th class="border-0 text-muted py-3 small font-weight-bold text-uppercase">Position</th>
                                                <th class="border-0 text-muted py-3 small font-weight-bold text-uppercase text-right px-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($department->staff as $staffMember)
                                                <tr>
                                                    <td class="px-4 py-3 align-middle text-dark">
                                                        <div class="font-weight-bold">{{ $staffMember->full_name }}</div>
                                                        <small class="text-muted">ID: {{ $staffMember->employee_number }}</small>
                                                    </td>
                                                    <td class="py-3 align-middle">
                                                        <span class="badge" style="background-color: #f1f5f9; color: #475569;">{{ $staffMember->jobPosition->name ?? 'Staff' }}</span>
                                                    </td>
                                                    <td class="py-3 align-middle text-right px-4">
                                                        <a href="{{ route('staff.show', $staffMember->staff_id) }}" class="btn btn-sm btn-light border text-primary font-weight-bold shadow-xs">
                                                            View Profile
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-5 text-muted small">No staff members found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Subjects List -->
                            <div class="tab-pane fade" id="subjects-content" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead style="background-color: #f8fafc;">
                                            <tr>
                                                <th class="border-0 text-muted px-4 py-3 small font-weight-bold text-uppercase">Subject</th>
                                                <th class="border-0 text-muted py-3 small font-weight-bold text-uppercase text-center">Type</th>
                                                <th class="border-0 text-muted py-3 small font-weight-bold text-uppercase text-right px-4">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($department->subjects as $subject)
                                                <tr>
                                                    <td class="px-4 py-3 align-middle text-dark">
                                                        <div class="font-weight-bold">{{ $subject->name }}</div>
                                                        <code class="text-primary small">{{ $subject->subject_code }}</code>
                                                    </td>
                                                    <td class="py-3 align-middle text-center">
                                                        @if($subject->is_elective)
                                                            <span class="badge" style="background-color: #fdf4ff; color: #a21caf;">Elective</span>
                                                        @else
                                                            <span class="badge" style="background-color: #f0fdf4; color: #166534;">Core</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-3 align-middle text-right px-4">
                                                        <a href="{{ route('subjects.show', $subject->subject_id) }}" class="btn btn-sm btn-light border text-primary font-weight-bold shadow-xs">
                                                            Details
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="text-center py-5 text-muted small">No subjects assigned.</td>
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
    </div>
    
    <style>
        .nav-tabs .nav-link.active {
            color: #2563eb !important;
            border-bottom: 2px solid #2563eb !important;
            background: transparent;
        }
        .text-blue { color: #2563eb; }
    </style>
@endsection
