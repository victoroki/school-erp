@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-file-invoice mr-2"></i> Examinations Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Examinations</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <!-- Dashboard Summary Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-white elevation-2 border-left-danger">
                    <div class="inner">
                        <h3 class="text-danger">{{ $upcomingExamsCount }}</h3>
                        <p class="text-muted font-weight-bold text-uppercase small">Upcoming Exams</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt text-danger opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-white elevation-2 border-left-warning">
                    <div class="inner">
                        <h3 class="text-warning">{{ $ongoingExamsCount }}</h3>
                        <p class="text-muted font-weight-bold text-uppercase small">Ongoing Exams</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-play-circle text-warning opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-white elevation-2 border-left-info">
                    <div class="inner">
                        <h3 class="text-info">{{ $marksEntryProgress }}%</h3>
                        <p class="text-muted font-weight-bold text-uppercase small">Marks Entry Progress</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tasks text-info opacity-25"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-white elevation-2 border-left-success">
                    <div class="inner">
                        <h3 class="text-success">{{ $overallPassRate }}%</h3>
                        <p class="text-muted font-weight-bold text-uppercase small">Overall Pass Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-double text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Alerts -->
        <div class="row">
            <div class="col-md-8">
                <div class="card card-outline card-danger elevation-2 border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-bolt mr-2 text-danger"></i> Quick Exam Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('exams.create') }}" class="btn btn-outline-danger btn-block py-3 shadow-sm border-2">
                                    <i class="fas fa-plus-circle fa-2x mb-2 d-block"></i>
                                    <span class="font-weight-bold">New Session</span>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('exam-results.index') }}" class="btn btn-outline-primary btn-block py-3 shadow-sm border-2">
                                    <i class="fas fa-edit fa-2x mb-2 d-block"></i>
                                    <span class="font-weight-bold">Enter Marks</span>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('exam-schedules.index') }}" class="btn btn-outline-info btn-block py-3 shadow-sm border-2">
                                    <i class="fas fa-clock fa-2x mb-2 d-block"></i>
                                    <span class="font-weight-bold">Timetable</span>
                                </a>
                            </div>
                            <div class="col-md-3 col-6 mb-3">
                                <a href="{{ route('exam-reports.generate') }}" class="btn btn-outline-success btn-block py-3 shadow-sm border-2">
                                    <i class="fas fa-file-pdf fa-2x mb-2 d-block"></i>
                                    <span class="font-weight-bold">Report Cards</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Chart -->
                <div class="card card-outline card-info elevation-2 border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-chart-line mr-2 text-info"></i> Performance Trends
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Next Exam Alert -->
                @if($nextExam)
                <div class="card bg-danger elevation-2">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <i class="fas fa-exclamation-circle fa-3x"></i>
                            </div>
                            <div class="col">
                                <h5 class="font-weight-bold mb-1">Upcoming Exam</h5>
                                <p class="mb-0">{{ $nextExam->name }}</p>
                                <small class="text-white-50">Starts: {{ $nextExam->start_date->format('d M, Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Grade Distribution -->
                <div class="card card-outline card-warning elevation-2 border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-chart-pie mr-2 text-warning"></i> Grade Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="gradeChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>

                <!-- Recent Mark Entries -->
                <div class="card card-outline card-success elevation-2 border-0">
                    <div class="card-header bg-white">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-history mr-2 text-success"></i> Recent Results
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse($recentResults as $res)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <b class="text-primary">{{ $res->student->full_name }}</b>
                                    <div class="small text-muted">{{ $res->subject->name }} - {{ $res->exam->name }}</div>
                                </div>
                                <span class="badge badge-success badge-pill p-2">{{ $res->marks_obtained }}</span>
                            </li>
                            @empty
                            <li class="list-group-item text-center text-muted py-4">No recent results found</li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="card-footer bg-white text-center">
                        <a href="{{ route('exam-results.index') }}" class="small text-uppercase font-weight-bold">View All Results</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-danger { border-left: 5px solid #dc3545 !important; }
        .border-left-warning { border-left: 5px solid #ffc107 !important; }
        .border-left-info { border-left: 5px solid #17a2b8 !important; }
        .border-left-success { border-left: 5px solid #28a745 !important; }
        .small-box { transition: transform .3s; }
        .small-box:hover { transform: translateY(-5px); }
        .btn-outline-danger:hover, .btn-outline-primary:hover, .btn-outline-info:hover, .btn-outline-success:hover { color: #fff !important; }
        .opacity-25 { opacity: 0.25; }
    </style>
@endsection

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Performance Trend Chart
        var perfCtx = document.getElementById('performanceChart').getContext('2d');
        var performanceChart = new Chart(perfCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($performanceTrends->pluck('name')) !!},
                datasets: [{
                    label: 'Mean Score',
                    data: {!! json_encode($performanceTrends->pluck('exam_results_avg_marks_obtained')) !!},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#dc3545',
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { borderDash: [5, 5] }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        // Grade Distribution Chart
        var gradeCtx = document.getElementById('gradeChart').getContext('2d');
        var gradeChart = new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($gradeDistribution->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($gradeDistribution->pluck('count')) !!},
                    backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#fd7e14', '#dc3545', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    });
</script>
@endpush
