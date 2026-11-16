@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-file-pdf mr-2"></i> Generate Report Cards
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-outline card-danger elevation-2 border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold">Selection Criteria</h3>
                    </div>
                    <form action="{{ route('exam-reports.bulk') }}" method="GET">
                        <div class="card-body">
                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Select Examination</label>
                                {!! Form::select('exam_id', $exams, null, ['class' => 'form-control select2', 'placeholder' => 'Choose Exam...', 'required']) !!}
                                <small class="text-muted">Only exams with approved marks should be reported to parents.</small>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold">Select Class / Stream</label>
                                {!! Form::select('class_section_id', $classSections, null, ['class' => 'form-control select2', 'placeholder' => 'Choose Class...', 'required']) !!}
                            </div>

                            <div class="bg-light p-3 rounded mb-0">
                                <h6 class="font-weight-bold"><i class="fas fa-cog mr-2"></i> Options</h6>
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="include_attendance" name="options[]" value="attendance" checked>
                                    <label class="custom-control-label small" for="include_attendance">Include Attendance Summary (for the exam period)</label>
                                </div>
                                <div class="custom-control custom-checkbox ml-2">
                                    <input type="checkbox" class="custom-control-input" id="include_fee" name="options[]" value="fee">
                                    <label class="custom-control-label small" for="include_fee">Include Fee Balance Statement</label>
                                </div>
                            </div>

                            <div class="alert alert-info border-0 mt-3 mb-0 py-2 small">
                                <i class="fas fa-info-circle mr-1"></i>
                                CBC/CBE learners automatically receive performance levels
                                (<b>EE · ME · AE · BE</b> with KJSEA points); 8-4-4 learners receive KCSE-style grades.
                            </div>
                        </div>
                        <div class="card-footer bg-white text-center py-4">
                            <button type="submit" class="btn btn-danger btn-lg px-5 elevation-2">
                                <i class="fas fa-sync-alt mr-2"></i> GENERATE BULK REPORTS
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
