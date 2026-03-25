@extends('layouts.app')

@section('content')
    <div class="dashboard-wrapper p-4" style="background: #fdfdfd; min-height: 100vh; font-family: 'Inter', sans-serif;">
        <!-- Header Section -->
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <!-- Search removed as requested -->
            </div>
            <div class="col-md-6 text-right d-flex align-items-center justify-content-end">
                <h4 class="font-weight-bold mb-0 text-dark" style="letter-spacing: -0.5px;">Examination Dashboard</h4>
            </div>
        </div>

        <!-- Stat Cards Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card p-4 rounded-xl shadow-sm bg-white h-100 border-0">
                    <p class="text-muted font-weight-bold small text-uppercase mb-2" style="letter-spacing: 0.5px;">Upcoming Exams</p>
                    <div class="d-flex align-items-end">
                        <h1 class="mb-0 font-weight-bold mr-3" style="color: #0c1e35;">{{ $upcomingExamsCount }}</h1>
                        <span class="badge badge-soft-success px-2 py-1 rounded-pill mb-2 small" style="background: #e6f4ea; color: #1e7e34;">Active</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card p-4 rounded-xl shadow-sm bg-white h-100 border-0">
                    <p class="text-muted font-weight-bold small text-uppercase mb-2" style="letter-spacing: 0.5px;">Ongoing Exams</p>
                    <div class="d-flex align-items-end">
                        <h1 class="mb-0 font-weight-bold mr-3" style="color: #0c1e35;">{{ str_pad($ongoingExamsCount, 2, '0', STR_PAD_LEFT) }}</h1>
                        <span class="badge badge-soft-primary px-2 py-1 rounded-pill mb-2 small" style="background: #e8f0fe; color: #1a73e8;">Live</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card p-4 rounded-xl shadow-sm bg-white h-100 border-0">
                    <p class="text-muted font-weight-bold small text-uppercase mb-2" style="letter-spacing: 0.5px;">Marks Entry Progress</p>
                    <div class="d-flex align-items-center">
                        <h1 class="mb-0 font-weight-bold mr-3" style="color: #0c1e35;">{{ $marksEntryProgress }}%</h1>
                        <div class="progress-container flex-grow-1">
                            <div class="progress rounded-pill shadow-inner" style="height: 10px; background: #f0f2f5;">
                                <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: {{ $marksEntryProgress }}%" aria-valuenow="{{ $marksEntryProgress }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="stat-card p-4 rounded-xl shadow-lg h-100 border-0 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0047b3 0%, #003366 100%);">
                    <p class="text-white-50 font-weight-bold small text-uppercase mb-2" style="letter-spacing: 0.5px;">Overall Pass Rate</p>
                    <div class="d-flex align-items-center justify-content-between">
                        <h1 class="mb-0 font-weight-bold" style="font-size: 2.5rem;">{{ $overallPassRate }}%</h1>
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
                    </div>
                    <div class="position-absolute" style="bottom: -10px; right: -10px; opacity: 0.1;">
                        <i class="fas fa-award fa-5x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column: Quick Actions & Performance Trends -->
            <div class="col-lg-8">
                <!-- NEW FEATURE ALERT -->
                <div class="row mb-4">
                    <div class="col-12">
                        <a href="{{ route('exam-results.bulk') }}" class="btn btn-success btn-lg btn-block shadow elevation-2 rounded-xl py-3 border-0" style="background: linear-gradient(135deg, #1e7e34 0%, #28a745 100%);">
                            <div class="d-flex align-items-center justify-content-center">
                                <div class="icon-circle bg-white text-success rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 45px; height: 45px;">
                                    <i class="fas fa-file-excel fs-3"></i>
                                </div>
                                <div class="text-left">
                                    <h5 class="mb-0 font-weight-bold text-white">Bulk Results Import Tool</h5>
                                    <p class="mb-0 small text-white-50">Upload spreadsheet results in one click</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Quick Command Center -->
                <div class="premium-card p-4 rounded-xl shadow-sm bg-white border-0 mb-4 h-auto">
                    <div class="d-flex align-items-center mb-4 pt-2">
                        <i class="fas fa-bolt text-primary mr-3 fs-3"></i>
                        <h5 class="mb-0 font-weight-bold text-dark">Quick Command Center</h5>
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('exams.create') }}" class="btn command-btn w-100 rounded-xl p-4 shadow-sm h-100 transition-all active-command" style="background: #002d72; color: white;">
                                <div class="command-content text-center">
                                    <div class="icon-circle bg-white text-dark rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 48px; height: 48px;">
                                        <i class="fas fa-plus"></i>
                                    </div>
                                    <p class="mb-0 font-weight-bold small">New Session</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('exam-results.index') }}" class="btn command-btn w-100 rounded-xl p-4 h-100 transition-all border-0" style="background: #dae7ff; color: #002d72;">
                                <div class="command-content text-center">
                                    <div class="icon-circle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                        <i class="fas fa-pen-nib fs-3"></i>
                                    </div>
                                    <p class="mb-0 font-weight-bold small">Enter Marks</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('exam-schedules.index') }}" class="btn command-btn w-100 rounded-xl p-4 h-100 transition-all border-0 bg-white shadow-sm" style="color: #002d72;">
                                <div class="command-content text-center">
                                    <div class="icon-circle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                        <i class="far fa-calendar-alt fs-3"></i>
                                    </div>
                                    <p class="mb-0 font-weight-bold small">Timetable</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 col-sm-6 mb-3">
                            <a href="{{ route('exam-reports.generate') }}" class="btn command-btn w-100 rounded-xl p-4 h-100 transition-all border-0 bg-white shadow-sm" style="color: #002d72;">
                                <div class="command-content text-center">
                                    <div class="icon-circle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                                        <i class="far fa-file-alt fs-3"></i>
                                    </div>
                                    <p class="mb-0 font-weight-bold small">Report Cards</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Historical Performance Trends -->
                <div class="premium-card p-4 rounded-xl shadow-sm bg-white border-0 mb-4 h-auto">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div class="title-group">
                            <h5 class="mb-1 font-weight-bold text-dark">Historical Performance Trends</h5>
                            <p class="text-muted small mb-0">Trending analytics for {{ $performanceTrends->first()->name ?? '' }} - {{ $performanceTrends->last()->name ?? '' }}</p>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Class Diversity: Performance Rankings -->
                <div class="premium-card p-4 rounded-xl shadow-sm bg-white border-0 mb-4 h-auto">
                    <div class="d-flex align-items-center justify-content-between mb-4 pt-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-layer-group text-primary mr-3 fs-3"></i>
                            <h5 class="mb-0 font-weight-bold text-dark">Class Performance Diversity</h5>
                        </div>
                        <span class="badge badge-soft-info px-3 py-2 rounded-pill small" style="background: #e0f2fe; color: #0369a1;">{{ count($classPerformance) }} Classes</span>
                    </div>
                    <div class="row">
                        @foreach($classPerformance->take(4) as $class)
                        <div class="col-md-6 mb-3">
                            <div class="p-3 rounded-lg border" style="background: #fcfdfe; border-color: #eef2f7 !important;">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="font-weight-bold text-dark mb-0">{{ $class->name }}</h6>
                                    <span class="font-weight-bold" style="color: #003399;">{{ $class->avg_marks }}%</span>
                                </div>
                                <div class="progress rounded-pill mb-2" style="height: 6px; background: #eef2f7;">
                                    <div class="progress-bar rounded-pill" style="width: {{ $class->avg_marks }}%; background: #003399;"></div>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted">Pass Rate: <span class="text-success font-weight-bold">{{ $class->pass_rate }}%</span></span>
                                    <span class="text-muted">{{ $class->total_results }} Entries</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column: Grade Distribution & Recent Results -->
            <div class="col-lg-4">
                <!-- Grade Distribution -->
                <div class="premium-card p-4 rounded-xl shadow-sm bg-white border-0 mb-4 h-auto">
                    <h5 class="mb-4 font-weight-bold text-dark">Grade Distribution</h5>
                    @php
                        $topGrade = $gradeDistribution->sortByDesc('count')->first();
                        $totalGrades = $gradeDistribution->sum('count');
                    @endphp
                    <div class="position-relative mb-4" style="height: 250px;">
                        <canvas id="gradeChart"></canvas>
                        <div class="chart-center position-absolute d-flex flex-column align-items-center justify-content-center w-100 h-100" style="top: 0; pointer-events: none;">
                            <h2 class="mb-0 font-weight-bold" style="color: #0c1e35;">{{ $topGrade->name ?? 'N/A' }}</h2>
                            <span class="text-muted font-weight-bold small text-uppercase">Dominant</span>
                        </div>
                    </div>
                    <div class="grade-legend">
                        @php
                            $colors = ['#003399', '#c2d1f0', '#0c1e35', '#f0f2f5', '#dae7ff', '#0047b3'];
                        @endphp
                        @foreach($gradeDistribution as $index => $grade)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle mr-2" style="width: 10px; height: 10px; background: {{ $colors[$index % count($colors)] }};"></span>
                                <span class="text-muted font-weight-bold small">{{ $grade->name }}</span>
                            </div>
                            <span class="font-weight-bold text-dark small">{{ $totalGrades > 0 ? round(($grade->count / $totalGrades) * 100) : 0 }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Recent Results -->
                <div class="premium-card p-4 rounded-xl shadow-sm bg-white border-0 h-auto">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h5 class="mb-0 font-weight-bold text-dark">Recent Results</h5>
                        <div class="d-flex">
                            <a href="{{ route('exam-results.bulk') }}" class="text-success font-weight-bold small mr-3">
                                <i class="fas fa-file-import mr-1"></i> Bulk Import
                            </a>
                            <a href="{{ route('exam-results.index') }}" class="text-primary font-weight-bold small">View All</a>
                        </div>
                    </div>
                    <div class="results-list">
                        @forelse($recentResults as $res)
                        @php
                            $initials = collect(explode(' ', $res->student->full_name))->map(fn($n) => $n[0])->take(2)->join('');
                            $isPass = $res->marks_obtained >= 40; // Assuming 40 as pass mark
                        @endphp
                        <div class="result-item d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle rounded-lg d-flex align-items-center justify-content-center font-weight-bold mr-3" style="width: 44px; height: 44px; background: #f0f2f5; color: #44566c;">
                                    {{ $initials }}
                                </div>
                                <div class="student-info">
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $res->student->full_name }}</h6>
                                    <p class="mb-0 text-muted small">{{ $res->subject->name }} + {{ $res->exam->name }}</p>
                                </div>
                            </div>
                            <div class="score-info text-right">
                                <h6 class="mb-0 font-weight-bold" style="color: #003399;">{{ $res->marks_obtained }}/100</h6>
                                <span class="{{ $isPass ? 'text-success' : 'text-danger' }}" style="font-size: 10px; font-weight: 700; text-transform: uppercase;">{{ $isPass ? 'Pass' : 'Fail' }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5 text-muted small">No recent results found</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        
        .fs-3 { font-size: 1.5rem; }
        .fs-5 { font-size: 1.25rem; }
        .rounded-xl { border-radius: 16px !important; }
        .transition-all { transition: all 0.3s ease; }
        .pointer { cursor: pointer; }
        
        .stat-card { border-radius: 16px; border: 0; }
        .premium-card { border-radius: 16px !important; border: 0; outline: none; }
        
        .command-btn { border: 0 !important; cursor: pointer; }
        .command-btn:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }
        .active-command { box-shadow: 0 10px 20px rgba(0, 45, 114, 0.2) !important; }
        
        .result-item:last-child { mb: 0; }
        
        .shadow-inner { box-shadow: inset 0 2px 4px rgba(0,0,0,0.06); }
        .btn-light { background: #f8f9fa; color: #6c757d; }
        
        input::placeholder { font-weight: 500; color: #adb5bd; }
        
        /* Nav Icons animation removed as icons were deleted */
        
        /* Custom Scrollbar for list if needed */
        .results-list::-webkit-scrollbar { width: 5px; }
        .results-list::-webkit-scrollbar-thumb { background: #e0e0e0; border-radius: 10px; }
    </style>
@endsection

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Performance Trend Chart - Polished Version
        var perfCtx = document.getElementById('performanceChart').getContext('2d');
        var grad = perfCtx.createLinearGradient(0, 0, 0, 400);
        grad.addColorStop(0, 'rgba(0, 51, 153, 0.15)');
        grad.addColorStop(1, 'rgba(255, 255, 255, 0)');

        var performanceChart = new Chart(perfCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($performanceTrends->pluck('name')) !!},
                datasets: [{
                    label: 'Mean Score',
                    data: {!! json_encode($performanceTrends->pluck('exam_results_avg_marks_obtained')) !!},
                    borderColor: '#003399',
                    backgroundColor: grad,
                    borderWidth: 4,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#003399',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { 
                            display: true,
                            color: '#f0f2f5',
                            drawBorder: false
                        },
                        ticks: {
                            font: { family: "'Inter', sans-serif", weight: 500 },
                            color: '#9ba4b4'
                        }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: {
                            font: { family: "'Inter', sans-serif", weight: 600 },
                            color: '#1a1a1a'
                        }
                    }
                }
            }
        });

        // Grade Distribution Chart - Doughnut Version
        var gradeCtx = document.getElementById('gradeChart').getContext('2d');
        var gradeChart = new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($gradeDistribution->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($gradeDistribution->pluck('count')) !!},
                    backgroundColor: ['#003399', '#c2d1f0', '#0c1e35', '#f0f2f5', '#dae7ff', '#0047b3'],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush

