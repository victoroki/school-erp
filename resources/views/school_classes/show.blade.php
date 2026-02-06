@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-chalkboard text-info mr-2"></i>Class Details: {{ $schoolClass->name }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default mr-2" href="{{ route('school-classes.index') }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a class="btn btn-info" href="{{ route('school-classes.edit', $schoolClass->class_id) }}">
                        <i class="fas fa-edit"></i> Edit Class
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Class Overview Stats -->
            <div class="col-12">
                <div class="card elevation-1 border-0">
                    <div class="card-body p-0">
                        <div class="row text-center py-4">
                            <div class="col-md-3 border-right">
                                <h2 class="text-info font-weight-bold mb-0">{{ $schoolClass->numeric_value }}</h2>
                                <span class="text-muted text-uppercase small font-weight-bold">Numeric Value</span>
                            </div>
                            <div class="col-md-3 border-right">
                                <h2 class="text-success font-weight-bold mb-0">{{ $schoolClass->classSections->count() }}</h2>
                                <span class="text-muted text-uppercase small font-weight-bold">Active Sections</span>
                            </div>
                            <div class="col-md-3 border-right">
                                <h2 class="text-primary font-weight-bold mb-0">{{ $studentCount }}</h2>
                                <span class="text-muted text-uppercase small font-weight-bold">Total Students</span>
                            </div>
                            <div class="col-md-3">
                                <h2 class="text-warning font-weight-bold mb-0">{{ $schoolClass->classSubjects->count() }}</h2>
                                <span class="text-muted text-uppercase small font-weight-bold">Curriculum Subjects</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <!-- Class Profile -->
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Class Profile</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-info elevation-1 mb-3" style="width: 70px; height: 70px; border-radius: 12px;">
                                <i class="fas fa-chalkboard fa-2x"></i>
                            </div>
                            <h4 class="font-weight-bold">{{ $schoolClass->name }}</h4>
                            <p class="text-muted small">Academic Management Unit</p>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase">Description</label>
                            <p class="text-dark bg-light p-2 rounded small" style="min-height: 80px;">
                                {{ $schoolClass->description ?: 'No description provided for this class.' }}
                            </p>
                        </div>

                        <div class="row mb-2">
                            <div class="col-5 text-muted small">Created:</div>
                            <div class="col-7 small font-weight-bold">{{ $schoolClass->created_at ? $schoolClass->created_at->format('M d, Y') : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <button class="btn btn-sm btn-outline-info btn-block"><i class="fas fa-file-pdf mr-1"></i> Export Class Profile</button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Tabs for Details -->
                <div class="card card-primary card-outline card-outline-tabs elevation-2">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="classTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="sections-tab" data-toggle="pill" href="#sections" role="tab" aria-controls="sections" aria-selected="true">
                                    <i class="fas fa-layer-group mr-1"></i> Class Sections
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="subjects-tab" data-toggle="pill" href="#subjects" role="tab" aria-controls="subjects" aria-selected="false">
                                    <i class="fas fa-book-open mr-1"></i> Subjects Catalog
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-0">
                        <div class="tab-content" id="classTabContent">
                            <!-- Sections Tab -->
                            <div class="tab-pane fade show active" id="sections" role="tabpanel" aria-labelledby="sections-tab">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="bg-light x-small text-uppercase text-muted">
                                        <tr>
                                            <th>Section Name</th>
                                            <th>Academic Year</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schoolClass->classSections as $cs)
                                            <tr>
                                                <td class="align-middle px-4">
                                                    <i class="fas fa-tag text-info mr-2 small"></i>
                                                    <strong>{{ $cs->name }}</strong>
                                                    @if($cs->section)
                                                        <span class="text-muted small font-italic ml-2">({{ $cs->section->name }})</span>
                                                    @endif
                                                </td>
                                                <td class="align-middle">
                                                    <span class="badge badge-pill badge-light border">{{ $cs->academicYear->name ?? 'N/A' }}</span>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <a href="{{ route('class-sections.show', $cs->class_section_id) }}" class="btn btn-xs btn-outline-info">View Details</a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-muted">
                                                    <i class="fas fa-info-circle fa-2x mb-3 d-block opacity-50"></i>
                                                    No sections configured for this class.
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
                                            <th>Subject</th>
                                            <th>Code</th>
                                            <th class="text-center">Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($schoolClass->classSubjects as $csub)
                                            <tr>
                                                <td class="align-middle px-4">
                                                    <i class="fas fa-book text-muted mr-2 small"></i>
                                                    <strong>{{ $csub->subject->name ?? 'Unknown' }}</strong>
                                                </td>
                                                <td class="align-middle">
                                                    <code>{{ $csub->subject->subject_code ?? 'N/A' }}</code>
                                                </td>
                                                <td class="text-center align-middle">
                                                    @if($csub->subject && $csub->subject->is_elective)
                                                        <span class="badge badge-warning">Elective</span>
                                                    @else
                                                        <span class="badge badge-success">Core</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-5 text-muted">
                                                    <i class="fas fa-info-circle fa-2x mb-3 d-block opacity-50"></i>
                                                    No subjects assigned to this class.
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
        .elevation-1 { box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24) !important; }
        .elevation-2 { box-shadow: 0 3px 6px rgba(0,0,0,0.16), 0 3px 6px rgba(0,0,0,0.23) !important; }
        .x-small { font-size: 0.75rem; }
    </style>
@endsection
