@extends('layouts.app')

@section('content')
<div class="dash-wrap">
    {{-- ① HEADER --}}
    <div class="row align-items-center mb-4">
        <div class="col-md-7">
            <h1 class="dash-heading">Examination Dashboard</h1>
            <p class="dash-sub">Monitor student performance, manage assessments, and track grading progress</p>
        </div>
        <div class="col-md-5 text-md-end mt-2 mt-md-0">
            <div class="d-flex gap-2 justify-content-md-end">
                <a href="{{ route('exam-results.bulk') }}" class="btn-dash btn-emerald-dash">
                    <i class="fas fa-file-import me-1"></i> Bulk Import
                </a>
                <a href="{{ route('exams.create') }}" class="btn-dash btn-indigo-dash">
                    <i class="fas fa-plus me-1"></i> New Session
                </a>
            </div>
        </div>
    </div>

    {{-- ② STAT CARDS --}}
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-indigo-light text-indigo">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Upcoming Exams</span>
                    <h2 class="stat-value">{{ $upcomingExamsCount }}</h2>
                    <span class="stat-trend text-emerald">
                        <i class="fas fa-arrow-up"></i> {{ $ongoingExamsCount }} Live Now
                    </span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-emerald-light text-emerald">
                    <i class="fas fa-pen-nib"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Grading Progress</span>
                    <h2 class="stat-value">{{ $marksEntryProgress }}%</h2>
                    <div class="progress mt-2" style="height: 4px; background: #f1f5f9;">
                        <div class="progress-bar bg-emerald" style="width: {{ $marksEntryProgress }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon bg-amber-light text-amber">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label">Pending Approval</span>
                    <h2 class="stat-value">{{ $pendingApprovalsCount }}</h2>
                    <span class="stat-trend text-muted">Sessions to Publish</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="stat-card bg-indigo text-white">
                <div class="stat-icon bg-white-transparent text-white">
                    <i class="fas fa-award"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-label text-white-50">Overall Pass Rate</span>
                    <h2 class="stat-value text-white">{{ $overallPassRate }}%</h2>
                    <span class="stat-trend text-white-50">School Average</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ③ MAIN CONTENT --}}
    <div class="row g-4">
        {{-- Left: Performance Trends --}}
        <div class="col-lg-8">
            <div class="dash-panel h-100">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-wrap bg-indigo-light text-indigo">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3 class="dash-panel-title">Academic Performance Trends</h3>
                    </div>
                </div>
                <div class="dash-panel-body">
                    <div style="height: 350px;">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Grade Distribution --}}
        <div class="col-lg-4">
            <div class="dash-panel h-100">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-wrap bg-amber-light text-amber">
                            <i class="fas fa-pie-chart"></i>
                        </div>
                        <h3 class="dash-panel-title">Grade Distribution</h3>
                    </div>
                </div>
                <div class="dash-panel-body d-flex flex-column">
                    <div class="position-relative mb-4" style="height: 200px;">
                        <canvas id="gradeChart"></canvas>
                        <div class="chart-center text-center">
                            @php $topGrade = $gradeDistribution->sortByDesc('count')->first(); @endphp
                            <h4 class="mb-0 fw-bold">{{ $topGrade->name ?? 'N/A' }}</h4>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem;">Dominant</small>
                        </div>
                    </div>
                    
                    <div class="grade-list mt-auto">
                        @php
                            $colors = ['#4f46e5', '#10b981', '#f59e0b', '#f43f5e', '#6366f1', '#8b5cf6'];
                            $totalGrades = $gradeDistribution->sum('count');
                        @endphp
                        @foreach($gradeDistribution->take(5) as $index => $grade)
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="dot" style="background: {{ $colors[$index % count($colors)] }}"></span>
                                <span class="text-muted fw-500 small">{{ $grade->name }}</span>
                            </div>
                            <span class="fw-bold small">{{ $totalGrades > 0 ? round(($grade->count / $totalGrades) * 100) : 0 }}%</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Left: Class Performance --}}
        <div class="col-lg-7">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-wrap bg-indigo-light text-indigo">
                            <i class="fas fa-users"></i>
                        </div>
                        <h3 class="dash-panel-title">Top Performing Classes</h3>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th class="ps-4">Class</th>
                                <th>Avg Marks</th>
                                <th>Pass Rate</th>
                                <th class="text-end pe-4">Entries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($classPerformance->take(5) as $class)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark small">{{ $class->name }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-600 small">{{ $class->avg_marks }}%</span>
                                        <div class="progress flex-grow-1" style="height: 4px; width: 60px;">
                                            <div class="progress-bar bg-indigo" style="width: {{ $class->avg_marks }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-emerald-light text-emerald rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                        {{ $class->pass_rate }}% Pass
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <span class="text-muted small fw-500">{{ $class->total_results }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Bottom Right: Recent Results --}}
        <div class="col-lg-5">
            <div class="dash-panel">
                <div class="dash-panel-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="icon-wrap bg-emerald-light text-emerald">
                            <i class="fas fa-history"></i>
                        </div>
                        <h3 class="dash-panel-title">Recent Results</h3>
                    </div>
                    <a href="{{ route('exam-results.index') }}" class="btn-dash btn-ghost py-1 px-2">
                        View All
                    </a>
                </div>
                <div class="dash-panel-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recentResults as $res)
                        @php $isPass = $res->marks_obtained >= 40; @endphp
                        <div class="list-group-item border-0 border-bottom px-4 py-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar bg-slate-light text-slate fw-bold">
                                        {{ substr($res->student->full_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark small">{{ $res->student->full_name }}</h6>
                                        <p class="mb-0 text-muted" style="font-size: 0.7rem;">{{ $res->subject->name }} ({{ $res->exam->name }})</p>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="d-block fw-bold {{ $isPass ? 'text-indigo' : 'text-rose' }} small">{{ $res->marks_obtained }}/100</span>
                                    <span class="text-muted text-uppercase fw-bold" style="font-size: 0.6rem;">{{ $isPass ? 'Pass' : 'Fail' }}</span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <p class="text-muted small mb-0">No recent results found</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ── Emil Kowalski Utility Suite ── */
:root {
    --indigo: #4f46e5; --indigo-light: #eef2ff;
    --emerald: #10b981; --emerald-light: #ecfdf5;
    --amber: #f59e0b; --amber-light: #fffbeb;
    --rose: #f43f5e; --rose-light: #fff1f2;
    --slate: #64748b; --slate-light: #f1f5f9;
    --text: #0f172a;
    --muted: #64748b;
    --border: #e2e8f0;
    --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
}

.dash-wrap { padding: 1.5rem; background: #fafafa; min-height: 100vh; }
.dash-heading { font-size: 1.5rem; font-weight: 800; color: var(--text); letter-spacing: -0.03em; margin-bottom: 0.25rem; }
.dash-sub { font-size: 0.875rem; color: var(--muted); font-weight: 500; margin-bottom: 0; }

/* Stat Cards */
.stat-card { background: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; position: relative; overflow: hidden; transition: all 200ms var(--ease-out); }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.stat-card.bg-indigo { background: var(--indigo); border-color: var(--indigo); }
.stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.125rem; flex-shrink: 0; }
.bg-indigo-light { background: var(--indigo-light); color: var(--indigo); }
.bg-emerald-light { background: var(--emerald-light); color: var(--emerald); }
.bg-amber-light { background: var(--amber-light); color: var(--amber); }
.bg-white-transparent { background: rgba(255,255,255,0.2); }
.stat-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--muted); display: block; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.5rem; font-weight: 800; color: var(--text); margin: 0; line-height: 1; }
.stat-trend { font-size: 0.688rem; font-weight: 600; display: block; margin-top: 0.5rem; }

/* Panels */
.dash-panel { background: #fff; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
.dash-panel-header { padding: 1rem 1.5rem; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; }
.dash-panel-title { font-size: 0.875rem; font-weight: 700; color: var(--text); margin: 0; }
.dash-panel-body { padding: 1.5rem; flex-grow: 1; }

.icon-wrap { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }

/* Buttons */
.btn-dash { display: inline-flex; align-items: center; justify-content: center; padding: .625rem 1.25rem; border-radius: 8px; font-size: .813rem; font-weight: 600; transition: all 150ms var(--ease-out); border: 1px solid transparent; text-decoration: none !important; }
.btn-indigo-dash { background: var(--indigo); color: #fff; box-shadow: 0 1px 2px rgba(79, 70, 229, 0.2); }
.btn-indigo-dash:hover { background: #4338ca; transform: translateY(-1px); color: #fff; }
.btn-emerald-dash { background: var(--emerald); color: #fff; box-shadow: 0 1px 2px rgba(16, 185, 129, 0.2); }
.btn-emerald-dash:hover { background: #059669; transform: translateY(-1px); color: #fff; }
.btn-ghost { background: transparent; color: var(--muted); border-color: var(--border); }
.btn-ghost:hover { background: var(--slate-light); color: var(--text); }

/* Table */
.table thead th { background: #f8fafc; border-bottom: 1px solid var(--border); border-top: 0; font-size: .688rem; font-weight: 700; text-transform: uppercase; color: var(--slate); letter-spacing: 0.05em; padding: .75rem 1.5rem; }
.table tbody td { padding: .75rem 1.5rem; vertical-align: middle; border-bottom: 1px solid #f8fafc; border-top: 0; }

/* Misc */
.avatar { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; }
.chart-center { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none; }
.dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
.fw-500 { font-weight: 500; }
.fw-600 { font-weight: 600; }
.bg-emerald { background: var(--emerald); }
.bg-indigo { background: var(--indigo); }
</style>
@endsection

@push('page_scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Line Chart Performance
        const perfCtx = document.getElementById('performanceChart').getContext('2d');
        const grad = perfCtx.createLinearGradient(0, 0, 0, 400);
        grad.addColorStop(0, 'rgba(79, 70, 229, 0.1)');
        grad.addColorStop(1, 'rgba(255, 255, 255, 0)');

        new Chart(perfCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($performanceTrends->pluck('name')) !!},
                datasets: [{
                    label: 'Average Score',
                    data: {!! json_encode($performanceTrends->pluck('exam_results_avg_marks_obtained')) !!},
                    borderColor: '#4f46e5',
                    backgroundColor: grad,
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: '#4f46e5',
                    pointHoverBorderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { font: { size: 11, weight: '500' }, color: '#94a3b8' }
                    },
                    x: { grid: { display: false }, ticks: { font: { size: 11, weight: '600' }, color: '#64748b' } }
                }
            }
        });

        // Doughnut Grade Chart
        const gradeCtx = document.getElementById('gradeChart').getContext('2d');
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($gradeDistribution->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($gradeDistribution->pluck('count')) !!},
                    backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#f43f5e', '#6366f1', '#8b5cf6'],
                    borderWidth: 0,
                    cutout: '80%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    });
</script>
@endpush

