@extends('layouts.app')

@section('content')
<style>
    .student-profile-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 10px 10px 0 0;
        margin: -15px -15px 0 -15px;
    }
    
    .student-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        object-fit: cover;
    }
    
    .info-card {
        border-left: 4px solid #667eea;
        transition: all 0.3s ease;
    }
    
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .stat-card {
        text-align: center;
        padding: 20px;
        border-radius: 8px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    
    .stat-card h3 {
        font-size: 32px;
        font-weight: bold;
        margin: 10px 0 5px;
    }
    
    .quick-action-btn {
        margin: 5px;
    }
    
    .timeline-item {
        border-left: 2px solid #667eea;
        padding-left: 20px;
        margin-left: 10px;
        padding-bottom: 20px;
        position: relative;
    }
    
    .timeline-item::before {
        content: '';
        width: 12px;
        height: 12px;
        background: #667eea;
        border-radius: 50%;
        position: absolute;
        left: -7px;
        top: 5px;
    }
    
    .document-category-badge {
        font-size: 11px;
        padding: 3px 8px;
    }
    
    .nav-tabs .nav-link {
        color: #666;
        font-weight: 500;
    }
    
    .nav-tabs .nav-link.active {
        color: #667eea;
        border-bottom: 3px solid #667eea;
    }
</style>

<div class="content">
    <!-- Student Profile Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="student-profile-header">
            <div class="row align-items-center">
                <div class="col-md-auto text-center">
                    <img src="{{ $student->avatar_url }}" alt="{{ $student->full_name }}" class="student-avatar">
                </div>
                <div class="col-md">
                    <h2 class="mb-1">{{ $student->full_name }}</h2>
                    <p class="mb-2 opacity-90">
                        <i class="fas fa-id-card mr-2"></i> {{ $student->admission_no }}
                        @if($student->roll_number)
                            <span class="ml-3"><i class="fas fa-hashtag mr-2"></i> Roll: {{ $student->roll_number }}</span>
                        @endif
                    </p>
                    <div>
                        {!! $student->enrollment_status_badge !!}
                        {!! $student->status_badge !!}
                        @if($student->is_scholarship_holder)
                            <span class="badge badge-warning"><i class="fas fa-award"></i> Scholarship Holder</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-auto text-right">
                    <div class="btn-group">
                        <a href="{{ route('students.edit', $student->student_id) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                        <button type="button" class="btn btn-light btn-sm" onclick="window.print()">
                            <i class="fas fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card bg-white border">
                        <i class="fas fa-calendar-alt text-primary fa-2x"></i>
                        <h3>{{ $student->age ?? 'N/A' }}</h3>
                        <p class="text-muted mb-0">Years Old</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-white border">
                        <i class="fas fa-check-circle text-success fa-2x"></i>
                        <h3>{{ $student->attendance_percentage }}%</h3>
                        <p class="text-muted mb-0">Attendance</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-white border">
                        <i class="fas fa-money-bill-wave text-warning fa-2x"></i>
                        <h3>KES {{ number_format($student->balance_fee) }}</h3>
                        <p class="text-muted mb-0">Fee Balance</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card bg-white border">
                        <i class="fas fa-book text-info fa-2x"></i>
                        <h3>{{ $student->studentDocuments->count() }}</h3>
                        <p class="text-muted mb-0">Documents</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card-footer bg-white">
            <div class="text-center">
                <a href="{{ route('fee-management.show', $student->student_id) }}" class="btn btn-outline-success quick-action-btn">
                    <i class="fas fa-money-check-alt"></i> View Fees
                </a>
                <a href="{{ route('student-documents.create') }}?student_id={{ $student->student_id }}" class="btn btn-outline-primary quick-action-btn">
                    <i class="fas fa-file-upload"></i> Upload Document
                </a>
                <a href="#" class="btn btn-outline-info quick-action-btn">
                    <i class="fas fa-chart-line"></i> View Performance
                </a>
                <a href="#" class="btn btn-outline-warning quick-action-btn">
                    <i class="fas fa-calendar-check"></i> Attendance Record
                </a>
            </div>
        </div>
    </div>

    <!-- Tabbed Content -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-line mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#overview" role="tab">
                        <i class="fas fa-user mr-2"></i> Overview
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#academic" role="tab">
                        <i class="fas fa-graduation-cap mr-2"></i> Academic
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#family" role="tab">
                        <i class="fas fa-users mr-2"></i> Family
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#medical" role="tab">
                        <i class="fas fa-heartbeat mr-2"></i> Medical
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#disciplinary" role="tab">
                        <i class="fas fa-gavel mr-2"></i> Disciplinary
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#documents" role="tab">
                        <i class="fas fa-folder-open mr-2"></i> Documents
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#fees" role="tab">
                        <i class="fas fa-dollar-sign mr-2"></i> Fees
                    </a>
                </li>
            </ul>

            <div class="tab-content">
                <!-- Overview Tab -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    @include('students.tabs.overview')
                </div>

                <!-- Academic Tab -->
                <div class="tab-pane fade" id="academic" role="tabpanel">
                    @include('students.tabs.academic')
                </div>

                <!-- Family Tab -->
                <div class="tab-pane fade" id="family" role="tabpanel">
                    @include('students.tabs.family')
                </div>

                <!-- Medical Tab -->
                <div class="tab-pane fade" id="medical" role="tabpanel">
                    @include('students.tabs.medical')
                </div>

                <!-- Disciplinary Tab -->
                <div class="tab-pane fade" id="disciplinary" role="tabpanel">
                    @include('students.tabs.disciplinary')
                </div>

                <!-- Documents Tab -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
                    @include('students.tabs.documents')
                </div>

                <!-- Fees Tab -->
                <div class="tab-pane fade" id="fees" role="tabpanel">
                    @include('students.tabs.fees')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
