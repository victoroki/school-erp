@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-chart-pie text-warning mr-2"></i>Student Reports
                </h1>
                <p class="text-muted small mb-0">Analytics and insights across your student body</p>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    {{-- Row 1: Core Reports --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.strength') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-warning-light">
                        <i class="fas fa-users text-warning"></i>
                    </div>
                    <h5 class="report-title">Student Strength</h5>
                    <p class="report-desc">Enrollment count by class &amp; section with gender breakdown</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.gender') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-info-light">
                        <i class="fas fa-venus-mars text-info"></i>
                    </div>
                    <h5 class="report-title">Gender Distribution</h5>
                    <p class="report-desc">Male vs female ratio with visual chart analysis</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.attendance') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-success-light">
                        <i class="fas fa-calendar-check text-success"></i>
                    </div>
                    <h5 class="report-title">Attendance Summary</h5>
                    <p class="report-desc">Present, absent, late &amp; half-day attendance overview</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.fee-status') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-danger-light">
                        <i class="fas fa-coins text-danger"></i>
                    </div>
                    <h5 class="report-title">Fee Status</h5>
                    <p class="report-desc">Outstanding balances, payments &amp; arrears per student</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Row 2: Analytics Reports --}}
    <div class="row">
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.age-distribution') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-primary-light">
                        <i class="fas fa-birthday-cake text-primary"></i>
                    </div>
                    <h5 class="report-title">Age Distribution</h5>
                    <p class="report-desc">Age group demographics and average age analysis</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.enrollment-trends') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-indigo-light">
                        <i class="fas fa-chart-line text-indigo"></i>
                    </div>
                    <h5 class="report-title">Enrollment Trends</h5>
                    <p class="report-desc">Year-over-year enrollment growth across academic years</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.medical') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-pink-light">
                        <i class="fas fa-heartbeat text-pink"></i>
                    </div>
                    <h5 class="report-title">Medical Records</h5>
                    <p class="report-desc">Students with conditions, allergies &amp; active medications</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ route('student-reports.transport-hostel') }}" class="text-decoration-none">
                <div class="report-card h-100">
                    <div class="report-icon bg-secondary-light">
                        <i class="fas fa-bus-alt text-secondary"></i>
                    </div>
                    <h5 class="report-title">Transport &amp; Hostel</h5>
                    <p class="report-desc">Students using school transport and hostel facilities</p>
                    <div class="report-footer">
                        <span class="report-link">View Report <i class="fas fa-arrow-right ml-1"></i></span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
    .report-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 1.5rem;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .report-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.1);
        border-color: #d1d5db;
    }
    .report-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    .report-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.35rem;
    }
    .report-desc {
        font-size: 0.78rem;
        color: #64748b;
        line-height: 1.4;
        margin-bottom: 1rem;
        flex-grow: 1;
    }
    .report-footer {
        margin-top: auto;
    }
    .report-link {
        font-size: 0.78rem;
        font-weight: 600;
        color: #4f46e5;
    }
    .report-card:hover .report-link {
        color: #4338ca;
    }

    /* Color variants */
    .bg-warning-light { background: #fffbeb; }
    .bg-info-light { background: #eff6ff; }
    .bg-success-light { background: #ecfdf5; }
    .bg-danger-light { background: #fef2f2; }
    .bg-primary-light { background: #eff6ff; }
    .bg-indigo-light { background: #eef2ff; }
    .bg-pink-light { background: #fdf2f8; }
    .bg-secondary-light { background: #f8fafc; }

    .text-indigo { color: #4f46e5; }
    .text-pink { color: #ec4899; }

    .text-decoration-none { text-decoration: none !important; }
</style>
@endsection
