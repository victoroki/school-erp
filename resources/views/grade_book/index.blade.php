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

        @if($students->count() > 0)
        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white d-flex align-items-center flex-wrap">
                <h3 class="card-title font-weight-bold">Class Performance Matrix</h3>
                @if($stats)
                    <div class="ml-auto small text-right">
                        <span class="badge badge-light border px-2 py-1">Learners with marks: <b>{{ $stats['learners_with_results'] }} / {{ $students->total() }}</b></span>
                        <span class="badge badge-light border px-2 py-1 ml-1">Class mean: <b>{{ number_format($stats['class_mean_percent'], 1) }}%</b></span>
                    </div>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="bg-light text-center small uppercase">
                            <tr>
                                <th rowspan="2" class="align-middle border-right" style="min-width: 190px;">Student Name</th>
                                @foreach($subjects as $subject)
                                    <th title="{{ $subject->name }}">
                                        {{ \Illuminate\Support\Str::limit($subject->name, 14) }}
                                        @isset($subjectMax[$subject->subject_id])
                                            <small class="d-block text-muted">/{{ $subjectMax[$subject->subject_id] }}</small>
                                        @endisset
                                    </th>
                                @endforeach
                                <th rowspan="2" class="align-middle bg-light-danger border-left">Total</th>
                                <th rowspan="2" class="align-middle bg-light-danger">Mean %</th>
                                <th rowspan="2" class="align-middle bg-light-danger">Pos.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td class="font-weight-bold pl-3 border-right">
                                    {{ $student->full_name }}
                                    <small class="text-muted d-block">{{ $student->admission_no }}</small>
                                </td>
                                @foreach($subjects as $subject)
                                    @php $markInfo = $results[$student->student_id][$subject->subject_id] ?? null; @endphp
                                    <td class="text-center">
                                        @if($markInfo)
                                            <span class="font-weight-bold">{{ number_format($markInfo['marks'], 0) }}</span><br>
                                            <small style="color: {{ $markInfo['color'] }}; font-weight: 600;">
                                                {{ number_format($markInfo['percent'], 0) }}% · {{ $markInfo['level'] }}
                                            </small>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center bg-light border-left font-weight-bold">
                                    {{ isset($studentStats[$student->student_id]) ? number_format($studentStats[$student->student_id]['total'], 0) : '—' }}
                                    @isset($studentStats[$student->student_id])
                                        <small class="text-muted d-block">/ {{ number_format($studentStats[$student->student_id]['out_of'], 0) }}</small>
                                    @endisset
                                </td>
                                <td class="text-center bg-light font-weight-bold text-danger">
                                    {{ isset($studentStats[$student->student_id]) ? number_format($studentStats[$student->student_id]['mean'], 1) . '%' : '—' }}
                                </td>
                                <td class="text-center font-weight-bold">
                                    {{ $ranks[$student->student_id] ?? '—' }}
                                </td>
                            </tr>
                            @endforeach
                            {{-- Subject averages footer --}}
                            <tr class="bg-light font-weight-bold">
                                <td class="pl-3 border-right">Subject Average</td>
                                @foreach($subjects as $subject)
                                    <td class="text-center">
                                        {{ $subjectAverages[$subject->subject_id] !== null ? number_format($subjectAverages[$subject->subject_id], 1) . '%' : '—' }}
                                    </td>
                                @endforeach
                                <td colspan="3"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white clearfix">
                <div class="float-right">
                    {{ $students->withQueryString()->links() }}
                </div>
                <small class="text-muted pt-2 d-inline-block">
                    Showing {{ $students->count() }} of {{ $students->total() }} learners ·
                    Performance levels: EE ≥75 · ME 41–74 · AE 21–40 · BE ≤20 (KJSEA scale)
                </small>
            </div>
        </div>
        @elseif(request()->filled(['exam_id', 'class_section_id']))
        <div class="alert alert-info border-0 elevation-1">
            <i class="fas fa-info-circle mr-2"></i> No learners enrolled in the selected class stream.
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
