@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-chart-pie text-warning mr-2"></i>Student Reports
                </h1>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    <div class="row">
        <!-- Student Strength -->
        <div class="col-md-4">
            <div class="card card-outline card-warning elevation-2 h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-users fa-3x text-warning"></i>
                    </div>
                    <h5 class="font-weight-bold">Student Strength</h5>
                    <p class="text-muted small">Total students count by class and section, including gender breakdown.</p>
                    <a href="{{ route('student-reports.strength') }}" class="btn btn-warning btn-sm btn-block mt-auto shadow-sm">
                        Generate Report <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Gender Ratio -->
        <div class="col-md-4">
            <div class="card card-outline card-info elevation-2 h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-venus-mars fa-3x text-info"></i>
                    </div>
                    <h5 class="font-weight-bold">Gender Distribution</h5>
                    <p class="text-muted small">Detailed analysis of male vs female distribution across the institution.</p>
                    <a href="{{ route('student-reports.gender') }}" class="btn btn-info btn-sm btn-block mt-auto shadow-sm">
                        Generate Report <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="col-md-4">
            <div class="card card-outline card-success elevation-2 h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-calendar-check fa-3x text-success"></i>
                    </div>
                    <h5 class="font-weight-bold">Attendance Summary</h5>
                    <p class="text-muted small">Overview of student attendance trends and overall percentages.</p>
                    <a href="{{ route('student-reports.attendance') }}" class="btn btn-success btn-sm btn-block mt-auto shadow-sm">
                        Generate Report <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Admission Trends -->
        <div class="col-md-4">
            <div class="card card-outline card-primary elevation-2 h-100 hover-shadow">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-chart-line fa-3x text-primary"></i>
                    </div>
                    <h5 class="font-weight-bold">Admission Trends</h5>
                    <p class="text-muted small">Analyze student admission rates over the last few academic years.</p>
                    <a href="#" class="btn btn-primary btn-sm btn-block mt-auto shadow-sm disabled">
                        Coming Soon
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-shadow { transition: all 0.3s ease; }
    .hover-shadow:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important; }
</style>
@endsection
