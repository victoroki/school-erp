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
                <form action="{{ route('exam-results.bulk') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Exam Session</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Exam', 'required']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Class & Section</label>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Subject</label>
                            {!! Form::select('subject_id', $subjects, request('subject_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Subject', 'required']) !!}
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-danger btn-block shadow-sm">
                                <i class="fas fa-search mr-1"></i> Retrieve Students
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
