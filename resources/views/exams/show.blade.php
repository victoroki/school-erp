@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-file-invoice mr-2"></i> Session: {{ $exam->name }}
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary px-4 mr-2" href="{{ route('exams.index') }}">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                    <a class="btn btn-danger px-4 elevation-2" href="{{ route('exams.edit', $exam->exam_id) }}">
                        <i class="fas fa-edit mr-1"></i> Edit Session
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-4">
        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card elevation-1 border-0 rounded-lg overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-danger p-4 text-white">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <div class="px-3">
                                <h6 class="text-muted font-weight-bold mb-0">START DATE</h6>
                                <h4 class="mb-0 font-weight-bold">{{ $exam->start_date->format('d M, Y') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card elevation-1 border-0 rounded-lg overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-warning p-4 text-white">
                                <i class="fas fa-tasks fa-2x"></i>
                            </div>
                            <div class="px-3">
                                <h6 class="text-muted font-weight-bold mb-0">TOTAL RESULTS</h6>
                                <h4 class="mb-0 font-weight-bold">{{ number_format($totalResults) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card elevation-1 border-0 rounded-lg overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-info p-4 text-white">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                            <div class="px-3">
                                <h6 class="text-muted font-weight-bold mb-0">AVG SCORE</h6>
                                <h4 class="mb-0 font-weight-bold">{{ number_format($averageScore, 1) }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card elevation-1 border-0 rounded-lg overflow-hidden">
                    <div class="card-body p-0">
                        <div class="d-flex align-items-center">
                            <div class="bg-success p-4 text-white">
                                <i class="fas fa-check-double fa-2x"></i>
                            </div>
                            <div class="px-3">
                                <h6 class="text-muted font-weight-bold mb-0">PASS RATE</h6>
                                <h4 class="mb-0 font-weight-bold">{{ $passPercentage }}%</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-danger elevation-2 border-0">
                    <div class="card-header p-0 border-bottom-0">
                        <ul class="nav nav-tabs" id="exam-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active font-weight-bold px-4 py-3" id="overview-tab" data-toggle="pill" href="#overview" role="tab">
                                    <i class="fas fa-info-circle mr-2"></i> Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold px-4 py-3" id="timetable-tab" data-toggle="pill" href="#timetable" role="tab">
                                    <i class="fas fa-clock mr-2"></i> Timetable / Schedule
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link font-weight-bold px-4 py-3" id="results-tab" data-toggle="pill" href="#results" role="tab">
                                    <i class="fas fa-trophy mr-2"></i> Performance Analysis
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="exam-tabs-content">
                            <!-- Overview Tab -->
                            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="font-weight-bold mb-4">Basic Information</h5>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th class="bg-light w-25">Session Name</th>
                                                <td>{{ $exam->name }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Category</th>
                                                <td><span class="badge badge-danger">{{ $exam->examType->name ?? 'N/A' }}</span></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Academic Year</th>
                                                <td>{{ $exam->academicYear->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Start Date</th>
                                                <td>{{ $exam->start_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">End Date</th>
                                                <td>{{ $exam->end_date->format('d/m/Y') }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Status</th>
                                                <td>
                                                    @if($exam->publish_result)
                                                        <span class="text-success"><i class="fas fa-check-circle mr-1"></i> Results Published</span>
                                                    @else
                                                        <span class="text-warning"><i class="fas fa-clock mr-1"></i> Marks Entry Ongoing</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="font-weight-bold mb-4">Description / Instructions</h5>
                                        <div class="bg-light p-4 rounded min-vh-25">
                                            {!! nl2br(e($exam->description)) ?: '<i class="text-muted">No description provided.</i>' !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Timetable Tab -->
                            <div class="tab-pane fade" id="timetable" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="font-weight-bold mb-0">Scheduled Papers</h5>
                                    <a href="{{ route('exam-schedules.create', ['exam_id' => $exam->exam_id]) }}" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-plus mr-1"></i> Add Schedule
                                    </a>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped border">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Subject</th>
                                                <th>Class / Section</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Room</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($exam->examSchedules as $schedule)
                                            <tr>
                                                <td class="font-weight-bold text-primary">{{ $schedule->subject->name ?? 'Unknown' }}</td>
                                                <td>{{ $schedule->classSection->schoolClass->name ?? '' }} - {{ $schedule->classSection->section->name ?? '' }}</td>
                                                <td>{{ $schedule->exam_date->format('d M, Y') }}</td>
                                                <td>{{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}</td>
                                                <td><span class="badge badge-light border">Room 102</span></td>
                                                <td>
                                                    <a href="{{ route('exam-schedules.edit', $schedule->schedule_id) }}" class="btn btn-sm btn-light shadow-sm">
                                                        <i class="fas fa-edit text-info"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No schedules found for this session.</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Results Tab -->
                            <div class="tab-pane fade" id="results" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border shadow-none rounded-lg">
                                            <div class="card-header bg-white font-weight-bold">Summary Statistics</div>
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span>Total Candidates</span>
                                                    <b class="text-dark">{{ $totalResults }}</b>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span>Mean Score</span>
                                                    <b class="text-primary">{{ number_format($averageScore, 2) }}%</b>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                                    <span>Highest Score</span>
                                                    <b class="text-success">98.0%</b>
                                                </div>
                                                <div class="d-flex justify-content-between mb-3">
                                                    <span>Lowest Score</span>
                                                    <b class="text-danger">12.0%</b>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="font-weight-bold mb-0 text-success">Latest Results Entries</h5>
                                            <a href="{{ route('exam-results.index', ['exam_id' => $exam->exam_id]) }}" class="btn btn-sm btn-success px-3">
                                                <i class="fas fa-edit mr-1"></i> Manage Marks
                                            </a>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm border">
                                                <thead class="bg-light small">
                                                    <tr>
                                                        <th>Student</th>
                                                        <th>Subject</th>
                                                        <th>Marks</th>
                                                        <th>Grade</th>
                                                        <th>Date</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($exam->examResults()->with('student', 'subject', 'grade')->latest()->limit(8)->get() as $res)
                                                    <tr>
                                                        <td class="font-weight-bold small">{{ $res->student->full_name }}</td>
                                                        <td class="small">{{ $res->subject->name }}</td>
                                                        <td><span class="badge badge-primary px-2">{{ $res->marks_obtained }}</span></td>
                                                        <td><b class="text-danger">{{ $res->grade->name ?? '-' }}</b></td>
                                                        <td class="small text-muted">{{ $res->created_at->format('d/m H:i') }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">No results found yet.</td>
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
        </div>
    </div>

    <style>
        .nav-tabs .nav-link.active {
            color: #dc3545 !important;
            border-bottom: 3px solid #dc3545 !important;
            border-top: 0;
            border-left: 0;
            border-right: 0;
        }
        .nav-tabs .nav-link {
            color: #6c757d;
            border: 0;
        }
        .min-vh-25 { min-height: 200px; }
        .bg-light-danger { background-color: rgba(220, 53, 69, 0.1); }
    </style>
@endsection
