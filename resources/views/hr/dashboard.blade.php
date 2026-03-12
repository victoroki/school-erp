@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-tachometer-alt text-secondary"></i> HR Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">HR Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $totalStaff }}</h3>
                            <p>Total Staff</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('staff.index') }}" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $activeStaff }}</h3>
                            <p>Active Staff</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <a href="{{ route('staff.index', ['status' => 'active']) }}" class="small-box-footer">
                            More info <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $onLeaveToday }}</h3>
                            <p>On Leave Today</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <a href="{{ route('leave-applications.index') }}" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $pendingLeaveRequests }}</h3>
                            <p>Pending Leave Requests</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="{{ route('leave-applications.index', ['status' => 'pending']) }}" class="small-box-footer">
                            Approve <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Second Row of Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $vacantPositions }}</h3>
                            <p>Vacant Positions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-briefcase"></i>
                        </div>
                        <a href="{{ route('job-positions.index') }}" class="small-box-footer">
                            View Positions <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-orange">
                        <div class="inner">
                            <h3>{{ $contractsExpiringSoon }}</h3>
                            <p>Contracts Expiring Soon</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <a href="{{ route('staff.index', ['contract_expiring' => 1]) }}" class="small-box-footer">
                            Review <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-6 col-12">
                    <div class="small-box bg-gradient-secondary">
                        <div class="inner">
                            <h3>KES {{ number_format($thisMonthPayroll, 2) }}</h3>
                            <p>This Month's Payroll (Estimated)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-money-check-alt"></i>
                        </div>
                        <a href="{{ route('payroll-processing.index') }}" class="small-box-footer">
                            Process Payroll <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Staff by Department -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title"><i class="fas fa-building"></i> Staff by Department</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th class="text-right">Staff Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($staffByDepartment as $dept)
                                        <tr>
                                            <td>{{ $dept->name }}</td>
                                            <td class="text-right"><span class="badge badge-secondary">{{ $dept->staff_count }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No departments found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Staff on Leave Today -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title"><i class="fas fa-calendar-times"></i> Staff on Leave Today</h3>
                        </div>
                        <div class="card-body">
                            @forelse($staffOnLeaveToday as $leave)
                                <div class="d-flex align-items-center mb-3">
                                    <img src="{{ $leave->staff->photo_url ?? asset('images/default-avatar.png') }}" 
                                         class="img-circle elevation-2" 
                                         style="width: 40px; height: 40px;" 
                                         alt="Staff Photo">
                                    <div class="ml-3 flex-grow-1">
                                        <strong>{{ $leave->staff->full_name }}</strong><br>
                                        <small class="text-muted">{{ $leave->leaveType->name ?? 'Leave' }} | Returns: {{ $leave->end_date->format('M d') }}</small>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted">No staff on leave today</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Hires -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-success">
                            <h3 class="card-title"><i class="fas fa-user-plus"></i> Recent Hires (This Month)</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($recentHires as $staff)
                                    <li class="list-group-item">
                                        <strong>{{ $staff->full_name }}</strong><br>
                                        <small class="text-muted">{{ $staff->jobPosition->title ?? 'N/A' }} | {{ $staff->date_of_joining->format('M d, Y') }}</small>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">No recent hires</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Birthdays -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title"><i class="fas fa-birthday-cake"></i> Upcoming Birthdays</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @forelse($upcomingBirthdays as $staff)
                                    <li class="list-group-item">
                                        <strong>{{ $staff->full_name }}</strong><br>
                                        <small class="text-muted">{{ $staff->date_of_birth->format('M d') }}</small>
                                    </li>
                                @empty
                                    <li class="list-group-item text-center text-muted">No upcoming birthdays</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Alerts & Notifications -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header bg-danger">
                            <h3 class="card-title"><i class="fas fa-exclamation-triangle"></i> Alerts</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @if($documentsExpiringSoon->count() > 0)
                                    <li class="list-group-item">
                                        <i class="fas fa-file-alt text-danger"></i> 
                                        <strong>{{ $documentsExpiringSoon->count() }}</strong> documents expiring in 30 days
                                        <a href="{{ route('staff-documents.index', ['expiring' => 1]) }}" class="float-right">View</a>
                                    </li>
                                @endif
                                
                                @if($probationEndingSoon->count() > 0)
                                    <li class="list-group-item">
                                        <i class="fas fa-user-clock text-warning"></i> 
                                        <strong>{{ $probationEndingSoon->count() }}</strong> probation periods ending soon
                                        <a href="{{ route('staff.index', ['probation_ending' => 1]) }}" class="float-right">Review</a>
                                    </li>
                                @endif

                                @if($contractsExpiringSoon > 0)
                                    <li class="list-group-item">
                                        <i class="fas fa-file-contract text-orange"></i> 
                                        <strong>{{ $contractsExpiringSoon }}</strong> contracts expiring in 30 days
                                        <a href="{{ route('staff.index', ['contract_expiring' => 1]) }}" class="float-right">Review</a>
                                    </li>
                                @endif

                                @if($vacantPositions > 0)
                                    <li class="list-group-item">
                                        <i class="fas fa-briefcase text-danger"></i> 
                                        <strong>{{ $vacantPositions }}</strong> vacant positions
                                        <a href="{{ route('job-positions.index') }}" class="float-right">View</a>
                                    </li>
                                @endif

                                @if($documentsExpiringSoon->count() == 0 && $probationEndingSoon->count() == 0 && $contractsExpiringSoon == 0 && $vacantPositions == 0)
                                    <li class="list-group-item text-center text-muted">
                                        <i class="fas fa-check-circle text-success"></i> All clear!
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 col-6 text-center mb-3">
                                    <a href="{{ route('staff.create') }}" class="btn btn-app bg-success">
                                        <i class="fas fa-user-plus"></i> Add New Staff
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 text-center mb-3">
                                    <a href="{{ route('leave-applications.index', ['status' => 'pending']) }}" class="btn btn-app bg-warning">
                                        <span class="badge bg-danger">{{ $pendingLeaveRequests }}</span>
                                        <i class="fas fa-check"></i> Approve Leaves
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 text-center mb-3">
                                    <a href="{{ route('payroll-processing.index') }}" class="btn btn-app bg-secondary">
                                        <i class="fas fa-money-check-alt"></i> Process Payroll
                                    </a>
                                </div>
                                <div class="col-md-3 col-6 text-center mb-3">
                                    <a href="{{ route('hr.reports.headcount') }}" class="btn btn-app bg-info">
                                        <i class="fas fa-chart-bar"></i> View Reports
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection
