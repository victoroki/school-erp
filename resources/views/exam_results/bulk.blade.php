@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-edit mr-2"></i> Bulk Marks Entry
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- Filter Card -->
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('exam-results.bulk') }}" method="GET" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase text-muted">Exam Session</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Exam', 'required', 'id' => 'exam_id']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase text-muted">Class & Section</label>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required', 'id' => 'class_section_id']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase text-muted">Subject</label>
                            {!! Form::select('subject_id', $subjects, request('subject_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Subject', 'required', 'id' => 'subject_id']) !!}
                        </div>
                        <div class="col-md-3 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-danger btn-block shadow-sm">
                                <i class="fas fa-search mr-1"></i> Retrieve Students
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(request()->filled(['exam_id', 'class_section_id', 'subject_id']))
        <!-- Excel Import Tools -->
        <div class="row mb-4 animate__animated animate__fadeIn">
            <div class="col-md-6">
                <div class="card elevation-1 border-0 h-100 bg-light">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-section mr-4">
                            <div class="rounded-circle bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                <i class="fas fa-file-csv fa-2x text-success"></i>
                            </div>
                        </div>
                        <div>
                            <h5 class="font-weight-bold mb-1 text-dark">Step 1: Download Template</h5>
                            <p class="small text-muted mb-2">Get the ready-to-use Excel template with student details.</p>
                            <a href="{{ route('exam-results.import-template', request()->all()) }}" class="btn btn-outline-success btn-sm font-weight-bold">
                                <i class="fas fa-download mr-1"></i> Download Template
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mt-3 mt-md-0">
                <div class="card elevation-1 border-0 h-100 bg-white">
                    <div class="card-body">
                        <h5 class="font-weight-bold mb-2"><i class="fas fa-file-import text-primary mr-2"></i> Step 2: Upload Filled File</h5>
                        <form action="{{ route('exam-results.import.store') }}" method="POST" enctype="multipart/form-data" class="form-inline">
                            @csrf
                            <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                            <input type="hidden" name="class_section_id" value="{{ request('class_section_id') }}">
                            <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                            
                            <div class="custom-file mb-2 mr-2" style="max-width: 250px;">
                                <input type="file" name="excel_file" class="custom-file-input" id="excel_file" required accept=".csv">
                                <label class="custom-file-label" for="excel_file">Choose CSV...</label>
                            </div>
                            <button type="submit" class="btn btn-primary mb-2 shadow-sm font-weight-bold">
                                <i class="fas fa-upload mr-1"></i> Import Marks
                            </button>
                        </form>
                        <small class="text-danger">* Ensure you use the downloaded template for compatibility.</small>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(count($students) > 0)
        <!-- Entry Card -->
        <div class="card card-outline card-danger elevation-2 border-0">
            <form action="{{ route('exam-results.bulk.store') }}" method="POST">
                @csrf
                <input type="hidden" name="exam_id" value="{{ request('exam_id') }}">
                <input type="hidden" name="class_section_id" value="{{ request('class_section_id') }}">
                <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">

                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-users mr-2 text-primary"></i> 
                        Student List ({{ count($students) }} candidates)
                    </h3>
                    <div class="card-tools">
                        <button type="submit" class="btn btn-success px-4 elevation-1">
                            <i class="fas fa-save mr-1"></i> Save All Marks
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px" class="text-center">#</th>
                                    <th>Admission No</th>
                                    <th>Student Name</th>
                                    <th style="width: 200px">Marks (Max: 100)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $index => $student)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td><span class="badge badge-light border">{{ $student->admission_no }}</span></td>
                                    <td class="font-weight-bold">{{ $student->full_name }}</td>
                                    <td>
                                        <input type="number" 
                                               name="marks[{{ $student->student_id }}]" 
                                               class="form-control form-control-sm text-center font-weight-bold {{ isset($existingResults[$student->student_id]) ? 'is-valid border-success' : '' }}" 
                                               step="0.01" 
                                               min="0" 
                                               max="100"
                                               value="{{ $existingResults[$student->student_id] ?? '' }}"
                                               placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="text" name="remarks[{{ $student->student_id }}]" class="form-control form-control-sm" placeholder="Optional remarks...">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-right">
                    <button type="submit" class="btn btn-success px-5 elevation-2 btn-lg">
                        <i class="fas fa-save mr-2"></i> FINAL SAVE & UPDATE
                    </button>
                </div>
            </form>
        </div>
        @elseif(request()->filled(['exam_id', 'class_section_id', 'subject_id']))
        <div class="alert alert-info elevation-1 border-0">
            <i class="fas fa-info-circle mr-2"></i> No active student enrollments found for the selected Class/Section.
        </div>
        @endif
    </div>

    <style>
        .is-valid { background-color: rgba(40, 167, 69, 0.05) !important; }
    </style>
@endsection
