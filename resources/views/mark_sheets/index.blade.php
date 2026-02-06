@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-table mr-2"></i> Subject Mark Sheets
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('mark-sheets.index') }}" method="GET">
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
                                <i class="fas fa-search mr-1"></i> Retrieve Sheet
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(count($results) > 0)
        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">Mark Sheet: {{ count($results) }} entries</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i> Print Sheet
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Admission No</th>
                            <th>Student Name</th>
                            <th class="text-center">Marks Obtained</th>
                            <th class="text-center">Grade</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $res)
                        <tr>
                            <td class="pl-4">{{ $res->student->admission_no }}</td>
                            <td class="font-weight-bold">{{ $res->student->full_name }}</td>
                            <td class="text-center"><b>{{ number_format($res->marks_obtained, 0) }}</b></td>
                            <td class="text-center"><span class="badge badge-danger">{{ $res->grade->name ?? '-' }}</span></td>
                            <td class="small">{{ $res->remarks ?: 'No remarks' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @elseif(request()->filled(['exam_id', 'class_section_id', 'subject_id']))
        <div class="alert alert-info border-0 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> No marks found for this subject in the selected exam/class.
        </div>
        @endif
    </div>
@endsection
