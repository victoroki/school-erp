@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-trophy mr-2"></i> Student Rankings
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('exam-analysis.rankings') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Exam Session</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Exam', 'required']) !!}
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Class & Section</label>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required']) !!}
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-danger shadow-sm">
                                <i class="fas fa-chart-line mr-1"></i> Generate Rankings
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(count($rankings) > 0)
        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold text-danger">Merit List</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-outline-success" onclick="window.print()">
                        <i class="fas fa-file-excel mr-1"></i> Export Excel
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 80px">Rank</th>
                                <th>Student Name</th>
                                <th class="text-center">Subjects</th>
                                <th class="text-center">Total Marks</th>
                                <th class="text-center">Mean Score</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rankings as $index => $rank)
                            <tr>
                                <td class="text-center">
                                    @if($index == 0) <span class="badge badge-warning p-2"><i class="fas fa-crown"></i> 1st</span>
                                    @elseif($index == 1) <span class="badge badge-secondary p-2">2nd</span>
                                    @elseif($index == 2) <span class="badge badge-bronze p-2">3rd</span>
                                    @else <span class="font-weight-bold">{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="font-weight-bold">{{ $rank->student->full_name }}</td>
                                <td class="text-center">{{ $rank->subjects_count }}</td>
                                <td class="text-center"><b class="text-primary">{{ number_format($rank->total_marks, 0) }}</b></td>
                                <td class="text-center"><b class="text-danger">{{ number_format($rank->mean_score, 1) }}%</b></td>
                                <td class="text-center">
                                    <span class="badge badge-success">Passed</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @elseif(request()->filled(['exam_id', 'class_section_id']))
        <div class="alert alert-info border-0 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> No ranking data available for this selection.
        </div>
        @endif
    </div>

    <style>
        .badge-bronze { background-color: #cd7f32; color: white; }
    </style>
@endsection
