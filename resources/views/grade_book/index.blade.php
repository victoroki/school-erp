@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-book mr-2"></i> Grade Book
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <!-- Filter Bar -->
        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('grade-book.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Exam Session</label>
                            {!! Form::select('exam_id', $exams, request('exam_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Exam', 'required']) !!}
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Class & Section</label>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required']) !!}
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger btn-block shadow-sm">
                                <i class="fas fa-search mr-1"></i> View Book
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-success btn-block shadow-sm" onclick="window.print()">
                                <i class="fas fa-print mr-1"></i> Print
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(count($students) > 0)
        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold">
                    Class Performance Matrix
                </h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="bg-light text-center small uppercase">
                            <tr>
                                <th rowspan="2" class="align-middle border-right">Student Name</th>
                                @foreach($subjects as $subject)
                                    <th colspan="1">{{ $subject->name }}</th>
                                @endforeach
                                <th rowspan="2" class="align-middle bg-light-danger border-left">Total</th>
                                <th rowspan="2" class="align-middle bg-light-danger">Mean</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            @php
                                $totalMarks = 0;
                                $subjectCount = 0;
                            @endphp
                            <tr>
                                <td class="font-weight-bold pl-3 border-right">{{ $student->full_name }}</td>
                                @foreach($subjects as $subject)
                                    @php
                                        $markInfo = $results[$student->student_id][$subject->subject_id] ?? null;
                                        if($markInfo) {
                                            $totalMarks += $markInfo['marks'];
                                            $subjectCount++;
                                        }
                                    @endphp
                                    <td class="text-center">
                                        @if($markInfo)
                                            <span class="font-weight-bold">{{ number_format($markInfo['marks'], 0) }}</span>
                                            <small class="text-danger ml-1">({{ $markInfo['grade'] }})</small>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center bg-light border-left font-weight-bold">{{ $totalMarks ?: '-' }}</td>
                                <td class="text-center bg-light font-weight-bold text-danger">
                                    {{ $subjectCount > 0 ? number_format($totalMarks / $subjectCount, 1) . '%' : '-' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @elseif(request()->filled(['exam_id', 'class_section_id']))
        <div class="alert alert-info border-0 elevation-1">
            <i class="fas fa-info-circle mr-2"></i> No results found for the selected criteria.
        </div>
        @endif
    </div>

    <style>
        @media print {
            .main-sidebar, .main-header, .content-header, .card-body form, .btn, .breadcrumb { display: none !important; }
            .content-wrapper { margin-left: 0 !important; }
            .card { border: 0 !important; box-shadow: none !important; }
            .table-bordered th, .table-bordered td { border: 1px solid #000 !important; }
        }
        .bg-light-danger { background-color: rgba(220, 53, 69, 0.05); }
    </style>
@endsection
