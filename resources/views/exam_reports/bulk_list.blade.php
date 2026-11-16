@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-7">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-list-ol mr-2"></i> Report Cards: {{ $exam->name }}
                    </h1>
                    <h6 class="text-muted font-weight-normal">
                        {{ $classSection->schoolClass->name ?? '' }} - {{ $classSection->section->name ?? '' }}
                        · {{ $students->count() }} learner(s)
                    </h6>
                </div>
                <div class="col-sm-5 text-right">
                    <a href="{{ route('exam-reports.pdf', array_merge(request()->only(['exam_id', 'class_section_id', 'options']))) }}"
                       class="btn btn-danger px-4 shadow-sm">
                        <i class="fas fa-file-pdf mr-2"></i> Download All as PDF
                    </a>
                    <a href="{{ route('exam-reports.generate') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Change Selection
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-header bg-white d-flex align-items-center flex-wrap">
                <h3 class="card-title font-weight-bold">Learner Reports</h3>
                <small class="text-muted ml-auto">
                    Readiness = papers with marks ÷ papers scheduled ({{ $expected }} for this exam)
                </small>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4" style="width: 130px">Admission No</th>
                            <th>Learner Name</th>
                            <th style="width: 70px" class="text-center">Sex</th>
                            <th class="text-center" style="width: 160px">Readiness</th>
                            <th class="text-center" style="width: 120px">Approval</th>
                            <th class="text-right pr-4" style="width: 220px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $student)
                        @php
                            $done = (int) ($recorded[$student->student_id] ?? 0);
                            $approvedCount = (int) ($approved[$student->student_id] ?? 0);
                            $pctDone = min(100, (int) round(($done / max(1, $expected)) * 100));
                        @endphp
                        <tr>
                            <td class="pl-4">{{ $student->admission_no }}</td>
                            <td class="font-weight-bold">{{ $student->full_name }}</td>
                            <td class="text-center">
                                <span class="badge badge-pill {{ strtolower((string) $student->gender) === 'female' ? 'badge-info' : 'badge-primary' }}">
                                    {{ strtoupper(substr((string) $student->gender, 0, 1)) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($done === 0)
                                    <span class="badge badge-secondary px-2 py-1">No marks yet</span>
                                @elseif($done >= $expected)
                                    <span class="badge badge-success px-2 py-1">Complete ({{ $done }}/{{ $expected }})</span>
                                @else
                                    <span class="badge badge-warning px-2 py-1">{{ $done }}/{{ $expected }} papers</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($approvedCount >= $expected && $expected > 0)
                                    <span class="badge badge-success px-2 py-1">Approved</span>
                                @elseif($approvedCount > 0)
                                    <span class="badge badge-warning px-2 py-1">{{ $approvedCount }}/{{ $expected }}</span>
                                @else
                                    <span class="badge badge-light border px-2 py-1">Pending</span>
                                @endif
                            </td>
                            <td class="text-right pr-4">
                                @if($done > 0)
                                    <a href="{{ route('exam-reports.individual', [$exam->exam_id, $student->student_id]) . '?' . http_build_query(['class_section_id' => $classSection->class_section_id, 'options' => $options]) }}"
                                       class="btn btn-sm btn-light border shadow-sm" target="_blank">
                                        <i class="fas fa-eye text-primary mr-1"></i> View Card
                                    </a>
                                @else
                                    <button class="btn btn-sm btn-light border" disabled title="Record marks first">
                                        <i class="fas fa-eye text-muted mr-1"></i> View Card
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-light border elevation-1 small">
            <i class="fas fa-lightbulb text-warning mr-1"></i>
            The PDF download skips learners with no marks at all. Cards show the learner's position in class,
            CBE performance levels, and auto-generated teacher/principal remarks.
        </div>
    </div>
@endsection
