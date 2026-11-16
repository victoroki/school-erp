@extends('layouts.app')

@section('content')
    <div class="content-header py-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark mb-0" style="font-size: 1.85rem;">
                        <i class="fas fa-user-graduate mr-2 text-primary"></i>
                        Student Intelligence
                    </h1>
                    <p class="text-muted mb-0">Overview of enrollment, demographics and admission patterns</p>
                </div>
                <div class="col-sm-6 text-right mt-3 mt-sm-0">
                    <a href="{{ route('students.create') }}" class="btn btn-primary shadow-sm px-4 py-2" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-user-plus mr-2"></i> Admitt Student
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-4">
        @include('flash::message')

        <!-- Primary Metric Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-primary-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Total Students</div>
                        <h2 class="font-weight-bold mb-1">{{ number_format($totalStudents) }}</h2>
                        @if($studentTrend != 0)
                            <div class="mt-2 d-flex align-items-center">
                                <span class="badge {{ $studentTrend > 0 ? 'bg-white-opacity' : 'bg-red-soft' }} x-small px-2 py-1">
                                    <i class="fas fa-arrow-{{ $studentTrend > 0 ? 'up' : 'down' }} mr-1"></i>
                                    {{ number_format(abs($studentTrend), 1) }}%
                                </span>
                                <span class="ml-2 extra-small opacity-75">vs last month</span>
                            </div>
                        @else
                            <div class="mt-2 extra-small opacity-75">Steady enrollment</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-emerald-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Monthly Admissions</div>
                        <h2 class="font-weight-bold mb-1">{{ number_format($newAdmissions) }}</h2>
                        <div class="mt-2 extra-small opacity-75">Recent term enrollments</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-indigo-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-venus-mars"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Gender Balance</div>
                        <h2 class="font-weight-bold mb-1">{{ $maleCount }}M : {{ $femaleCount }}F</h2>
                        <div class="mt-2 extra-small opacity-75">Ratio {{ $femaleCount > 0 ? number_format($maleCount/$femaleCount, 1) : $maleCount }}:1</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm overflow-hidden h-100 summary-card bg-rose-gradient">
                    <div class="card-body p-4 position-relative text-white">
                        <div class="summary-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="extra-small text-uppercase font-weight-bold mb-1" style="opacity: 0.85;">Fee Defaulters</div>
                        <h2 class="font-weight-bold mb-1">{{ number_format($feeDefaulters) }}</h2>
                        <div class="mt-2 extra-small opacity-75 text-white">Unpaid accounts</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Admission Chart Column -->
            <div class="col-lg-8 mb-4">
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between">
                        <h5 class="font-weight-bold text-dark m-0">Admission Trends</h5>
                        <div class="extra-small text-muted font-weight-bold pt-1 text-uppercase">Monthly Velocity</div>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 250px;">
                            <canvas id="admissionTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Students By Class Chart -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h5 class="font-weight-bold text-dark m-0">Students by Class</h5>
                    </div>
                    <div class="card-body p-4">
                        <div style="height: 250px;">
                            <canvas id="studentsByClassChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Admissions Table -->
                <div class="card border-0 shadow-sm" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 d-flex justify-content-between">
                        <h5 class="font-weight-bold text-dark m-0">Recent Enrollments (Last 5)</h5>
                        <a href="{{ route('students.index') }}" class="text-primary font-weight-bold extra-small text-uppercase">View Full Directory <i class="fas fa-chevron-right ml-1"></i></a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light-soft">
                                    <tr>
                                        <th class="border-0 px-4 py-3 extra-small font-weight-bold text-muted">STUDENT</th>
                                        <th class="border-0 px-4 py-3 extra-small font-weight-bold text-muted text-center">CLASS</th>
                                        <th class="border-0 px-4 py-3 extra-small font-weight-bold text-muted text-center">GENDER</th>
                                        <th class="border-0 px-4 py-3 extra-small font-weight-bold text-muted text-right">PROFILE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAdmissions as $student)
                                        @php $enroll = $student->studentClassEnrollments->where('is_current', true)->first(); @endphp
                                        <tr>
                                            <td class="px-4 py-3 align-middle">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle-sm bg-blue-soft text-blue mr-3 font-weight-bold small">
                                                        {{ strtoupper(substr($student->first_name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold text-dark small mb-0">{{ $student->first_name }} {{ $student->last_name }}</div>
                                                        <div class="extra-small text-muted">{{ $student->admission_no }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-center small font-weight-bold text-muted">
                                                {{ $enroll->classSection->schoolClass->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 align-middle text-center">
                                                <span class="badge badge-{{ $student->gender == 'male' ? 'primary' : 'rose' }}-soft x-small px-2 py-1 font-weight-bold">
                                                    {{ strtoupper($student->gender ?? 'N/A') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 align-middle text-right">
                                                <a href="{{ route('students.show', $student->student_id) }}" class="btn btn-white btn-sm shadow-xs border px-3 extra-small font-weight-bold text-primary" style="border-radius: 8px;">
                                                    DETAILS
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-5 text-muted small italic">No recent admissions to display.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Gender Dist. Pie -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0 text-center">
                        <h5 class="font-weight-bold text-dark m-0">Gender Ratio</h5>
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div style="height: 180px;">
                            <canvas id="genderDistributionChart"></canvas>
                        </div>
                        <div class="d-flex justify-content-center mt-4 border-top pt-3">
                            <div class="px-3 border-right text-center">
                                <h6 class="font-weight-bold text-dark mb-0">{{ number_format($maleCount) }}</h6>
                                <span class="extra-small text-muted">MALE</span>
                            </div>
                            <div class="px-3 text-center">
                                <h6 class="font-weight-bold text-dark mb-0">{{ number_format($femaleCount) }}</h6>
                                <span class="extra-small text-muted">FEMALE</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Summary -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                        <h6 class="font-weight-bold text-dark text-uppercase extra-small">Enrollment Status</h6>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold text-dark">Active Students</span>
                            <span class="small font-weight-bold text-success">{{ number_format($activeStudents) }}</span>
                        </div>
                        <div class="progress progress-sm rounded-pill mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold text-dark">Graduated</span>
                            <span class="small font-weight-bold text-primary">{{ number_format($graduatedStudents) }}</span>
                        </div>
                        <div class="progress progress-sm rounded-pill mb-3" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: 12%"></div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small font-weight-bold text-dark">Pending Docs</span>
                            <span class="small font-weight-bold text-warning">{{ number_format($studentsWithPendingDocs) }}</span>
                        </div>
                        <div class="progress progress-sm rounded-pill" style="height: 6px;">
                            <div class="progress-bar bg-warning" style="width: 3%"></div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="card border-0 shadow-sm bg-primary-gradient" style="border-radius: 20px;">
                    <div class="card-body p-4 text-white">
                        <h6 class="font-weight-bold mb-4">Quick Control Panel</h6>
                        <div class="row no-gutters">
                            <div class="col-6 p-1">
                                <a href="{{ route('students.index') }}" class="btn action-btn w-100 py-3">
                                    <i class="fas fa-address-book mb-2 fa-lg d-block"></i>
                                    <span class="x-small font-weight-bold">ENROLLMENT</span>
                                </a>
                            </div>
                            <div class="col-6 p-1">
                                <a href="{{ route('parents.index') }}" class="btn action-btn w-100 py-3">
                                    <i class="fas fa-users-cog mb-2 fa-lg d-block"></i>
                                    <span class="x-small font-weight-bold">PARENTS</span>
                                </a>
                            </div>
                            <div class="col-6 p-1">
                                <a href="{{ route('student-documents.index') }}" class="btn action-btn w-100 py-3">
                                    <i class="fas fa-folder-open mb-2 fa-lg d-block"></i>
                                    <span class="x-small font-weight-bold">DOSSIERS</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .extra-small { font-size: 0.7rem; letter-spacing: 0.4px; }
        .x-small { font-size: 0.65rem; letter-spacing: 0.4px; }
        .bg-light-soft { background-color: #f8fafc; }
        
        .bg-primary-gradient { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important; }
        .bg-emerald-gradient { background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important; }
        .bg-indigo-gradient { background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important; }
        .bg-rose-gradient { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%) !important; }
        
        .summary-card { border: none !important; border-radius: 20px; color: white !important; }
        .summary-card h2, .summary-card span, .summary-card div, .summary-card i { color: white !important; }
        .summary-icon { position: absolute; right: -10px; bottom: -10px; font-size: 4.5rem; color: white !important; opacity: 0.2; }
        
        .avatar-circle-sm { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .bg-blue-soft { background-color: #f0f9ff; }
        .text-blue { color: #3b82f6; }
        
        .badge-primary-soft { background-color: #dbeafe; color: #1e40af; }
        .badge-rose-soft { background-color: #ffe4e6; color: #be123c; }

        .action-btn { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.1); color: white !important; border-radius: 12px; transition: all 0.2s; }
        .action-btn:hover { background: rgba(255,255,255,0.25); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        
        .shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
    </style>

    @push('page_scripts')
    <!-- Robust Chart.js Implementation -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Make charts globally accessible to prevent re-declaration errors if layout pushes multiple times
        window.studentDashCharts = window.studentDashCharts || {};

        function initStudentDashboardCharts() {
            try {
                // 1. Admission Trend
                const trendEl = document.getElementById('admissionTrendChart');
                if (trendEl && !window.studentDashCharts.trend) {
                    window.studentDashCharts.trend = new Chart(trendEl.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($admissionTrend->pluck('month')) !!},
                            datasets: [{
                                data: {!! json_encode($admissionTrend->pluck('total')) !!},
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                                pointRadius: 4,
                                pointBackgroundColor: '#3b82f6'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, grid: { borderDash: [2, 2], drawBorder: false } },
                                x: { grid: { display: false } }
                            }
                        }
                    });
                }

                // 2. Students By Class (Horizontal Bar)
                const classEl = document.getElementById('studentsByClassChart');
                if (classEl && !window.studentDashCharts.class) {
                    window.studentDashCharts.class = new Chart(classEl.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($studentsByClass->pluck('class_name')) !!},
                            datasets: [{
                                data: {!! json_encode($studentsByClass->pluck('total')) !!},
                                backgroundColor: '#6366f1',
                                borderRadius: 6,
                                barThickness: 20
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { beginAtZero: true, grid: { display: false } },
                                y: { grid: { display: false } }
                            }
                        }
                    });
                }

                // 3. Gender Distribution
                const genderEl = document.getElementById('genderDistributionChart');
                if (genderEl && !window.studentDashCharts.gender) {
                    window.studentDashCharts.gender = new Chart(genderEl.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Male', 'Female'],
                            datasets: [{
                                data: [{{ $maleCount }}, {{ $femaleCount }}],
                                backgroundColor: ['#3b82f6', '#f43f5e'],
                                borderWidth: 0,
                                hoverOffset: 10
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '72%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            } catch (error) {
                console.error("Dashboard Chart Error:", error);
            }
        }

        // Run on load
        if (typeof Chart !== 'undefined') {
            initStudentDashboardCharts();
        } else {
            document.addEventListener('DOMContentLoaded', initStudentDashboardCharts);
        }
    </script>
    @endpush
@endsection
