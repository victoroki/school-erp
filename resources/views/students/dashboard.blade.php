@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-chart-line text-warning mr-2"></i>Student Management Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('students.create') }}" class="btn btn-warning shadow-sm">
                            <i class="fas fa-user-plus mr-1"></i> Admit New Student
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-3">
        @include('flash::message')

        {{-- Key Metrics Cards --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="info-box shadow-sm border-left border-warning" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Total Students</span>
                        <span class="info-box-number h3 mb-0">{{ number_format($totalStudents) }}</span>
                        @if($studentTrend != 0)
                            <small class="text-{{ $studentTrend > 0 ? 'success' : 'danger' }}">
                                <i class="fas fa-arrow-{{ $studentTrend > 0 ? 'up' : 'down' }}"></i>
                                {{ number_format(abs($studentTrend), 1) }}% vs last month
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box shadow-sm border-left border-success" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user-plus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">New This Month</span>
                        <span class="info-box-number h3 mb-0">{{ number_format($newAdmissions) }}</span>
                        <small class="text-muted">Recent admissions</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box shadow-sm border-left border-info" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-venus-mars"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Gender Ratio</span>
                        <span class="info-box-number h3 mb-0">{{ $maleCount }}M / {{ $femaleCount }}F</span>
                        <small class="text-muted">Male to Female</small>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="info-box shadow-sm border-left border-danger" style="border-left-width: 4px !important;">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text text-uppercase x-small font-weight-bold">Fee Defaulters</span>
                        <span class="info-box-number h3 mb-0">{{ number_format($feeDefaulters) }}</span>
                        <small class="text-muted">Overdue payments</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- Secondary Metrics --}}
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-success shadow-sm">
                    <div class="inner">
                        <h3>{{ number_format($activeStudents) }}</h3>
                        <p>Active Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-secondary shadow-sm">
                    <div class="inner">
                        <h3>{{ number_format($inactiveStudents) }}</h3>
                        <p>Inactive Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-primary shadow-sm">
                    <div class="inner">
                        <h3>{{ number_format($graduatedStudents) }}</h3>
                        <p>Graduated Students</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="small-box bg-gradient-warning shadow-sm">
                    <div class="inner">
                        <h3>{{ number_format($studentsWithPendingDocs) }}</h3>
                        <p>Pending Documents</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Students by Class Chart --}}
            <div class="col-lg-6">
                <div class="card card-outline card-warning elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-chart-bar mr-1"></i> Students by Class
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="studentsByClassChart" height="250"></canvas>
                    </div>
                </div>
            </div>

            {{-- Gender Distribution Pie Chart --}}
            <div class="col-lg-6">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-chart-pie mr-1"></i> Gender Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="genderDistributionChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Admission Trend --}}
            <div class="col-lg-8">
                <div class="card card-outline card-success elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-chart-line mr-1"></i> Admission Trend (Last 6 Months)
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="admissionTrendChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="col-lg-4">
                <div class="card card-outline card-warning elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-bolt mr-1"></i> Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('students.create') }}" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-user-plus mr-2"></i> Admit New Student
                            </a>
                            <a href="{{ route('students.index') }}" class="btn btn-outline-warning btn-block mb-2">
                                <i class="fas fa-users mr-2"></i> View All Students
                            </a>
                            <a href="{{ route('student-class-enrollments.index') }}" class="btn btn-outline-info btn-block mb-2">
                                <i class="fas fa-clipboard-list mr-2"></i> Manage Enrollments
                            </a>
                            <a href="{{ route('student-documents.index') }}" class="btn btn-outline-secondary btn-block mb-2">
                                <i class="fas fa-file-alt mr-2"></i> Student Documents
                            </a>
                            <a href="{{ route('parents.index') }}" class="btn btn-outline-primary btn-block mb-2">
                                <i class="fas fa-user-friends mr-2"></i> Parents/Guardians
                            </a>
                            <button class="btn btn-outline-success btn-block" disabled>
                                <i class="fas fa-file-pdf mr-2"></i> Generate Reports
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Admissions --}}
        <div class="row">
            <div class="col-12">
                <div class="card card-outline card-primary elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-history mr-1"></i> Recent Admissions
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('students.index') }}" class="btn btn-sm btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Admission No.</th>
                                    <th>Student Name</th>
                                    <th>Gender</th>
                                    <th>Class</th>
                                    <th>Date Admitted</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAdmissions as $student)
                                    @php
                                        $currentEnrollment = $student->studentClassEnrollments->where('is_current', true)->first();
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">{{ $student->admission_no }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $student->photo_url ?? asset('garikon-black.png') }}" 
                                                     class="img-circle img-sm mr-2 border shadow-sm" 
                                                     style="width: 32px; height: 32px; object-fit: cover;">
                                                <span>{{ $student->first_name }} {{ $student->last_name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $student->gender == 'male' ? 'info' : 'pink' }}">
                                                {{ ucfirst($student->gender ?? 'N/A') }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($currentEnrollment && $currentEnrollment->classSection)
                                                {{ $currentEnrollment->classSection->schoolClass->name ?? 'N/A' }}
                                                {{ $currentEnrollment->classSection->section->name ?? '' }}
                                            @else
                                                <span class="text-muted">Not Enrolled</span>
                                            @endif
                                        </td>
                                        <td>{{ $student->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <span class="badge badge-{{ $student->status == 'active' ? 'success' : 'secondary' }}">
                                                {{ ucfirst($student->status) }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('students.show', $student->student_id) }}" 
                                               class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            No recent admissions found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .x-small { font-size: 0.70rem; }
        .badge-pink { background-color: #e83e8c; color: white; }
        .bg-light-soft { background-color: rgba(0, 0, 0, 0.02); }
    </style>

    @push('page_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Students by Class Chart
        const classCtx = document.getElementById('studentsByClassChart').getContext('2d');
        new Chart(classCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($studentsByClass->pluck('class_name')) !!},
                datasets: [{
                    label: 'Number of Students',
                    data: {!! json_encode($studentsByClass->pluck('total')) !!},
                    backgroundColor: 'rgba(255, 193, 7, 0.7)',
                    borderColor: 'rgba(255, 193, 7, 1)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10
                        }
                    }
                }
            }
        });

        // Gender Distribution Chart
        const genderCtx = document.getElementById('genderDistributionChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Male', 'Female'],
                datasets: [{
                    data: [{{ $maleCount }}, {{ $femaleCount }}],
                    backgroundColor: ['rgba(23, 162, 184, 0.7)', 'rgba(232, 62, 140, 0.7)'],
                    borderColor: ['rgba(23, 162, 184, 1)', 'rgba(232, 62, 140, 1)'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Admission Trend Chart
        const trendCtx = document.getElementById('admissionTrendChart').getContext('2d');
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($admissionTrend->pluck('month')) !!},
                datasets: [{
                    label: 'New Admissions',
                    data: {!! json_encode($admissionTrend->pluck('total')) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @endpush
@endsection
