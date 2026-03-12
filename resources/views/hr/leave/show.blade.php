@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-alt text-info"></i> Leave Application Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave-applications.index') }}">Leave</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">Application Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Staff:</strong><br>
                                    {{ $leaveApplication->staff->full_name ?? 'N/A' }}<br>
                                    <small class="text-muted">{{ $leaveApplication->staff->employee_number ?? '' }}</small>
                                </div>
                                <div class="col-md-6">
                                    <strong>Department:</strong><br>
                                    {{ $leaveApplication->staff->department->name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Leave Type:</strong><br>
                                    <span class="badge badge-primary">{{ $leaveApplication->leaveType->name ?? 'N/A' }}</span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Duration:</strong><br>
                                    <span class="badge badge-info">{{ $leaveApplication->working_days }} working days</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Start Date:</strong><br>
                                    {{ \Carbon\Carbon::parse($leaveApplication->start_date)->format('l, F d, Y') }}
                                </div>
                                <div class="col-md-6">
                                    <strong>End Date:</strong><br>
                                    {{ \Carbon\Carbon::parse($leaveApplication->end_date)->format('l, F d, Y') }}
                                </div>
                            </div>

                            <div class="mb-3">
                                <strong>Reason:</strong><br>
                                <p class="border p-2 bg-light">{{ $leaveApplication->reason }}</p>
                            </div>

                            @if($leaveApplication->relief_staff_id)
                                <div class="mb-3">
                                    <strong>Relief Staff:</strong><br>
                                    {{ $leaveApplication->reliefStaff->full_name ?? 'N/A' }}
                                </div>
                            @endif

                            @if($leaveApplication->handover_notes)
                                <div class="mb-3">
                                    <strong>Handover Notes:</strong><br>
                                    <p class="border p-2 bg-light">{{ $leaveApplication->handover_notes }}</p>
                                </div>
                            @endif

                            @if($leaveApplication->supporting_document)
                                <div class="mb-3">
                                    <strong>Supporting Document:</strong><br>
                                    <a href="{{ asset('storage/' . $leaveApplication->supporting_document) }}" target="_blank" class="btn btn-sm btn-primary">
                                        <i class="fas fa-download"></i> Download Document
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Status Card -->
                    <div class="card">
                        <div class="card-header bg-{{ $leaveApplication->application_status == 'approved' ? 'success' : ($leaveApplication->application_status == 'rejected' ? 'danger' : 'warning') }}">
                            <h3 class="card-title">Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Application Status:</strong><br>
                                @if($leaveApplication->application_status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($leaveApplication->application_status == 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($leaveApplication->application_status == 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </div>

                            <div class="mb-3">
                                <strong>Submitted:</strong><br>
                                {{ $leaveApplication->submitted_date ? \Carbon\Carbon::parse($leaveApplication->submitted_date)->format('M d, Y h:i A') : 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <!-- Approval Workflow -->
                    <div class="card">
                        <div class="card-header bg-secondary">
                            <h3 class="card-title">Approval Workflow</h3>
                        </div>
                        <div class="card-body">
                            <!-- HOD Approval -->
                            <div class="mb-3">
                                <strong>HOD Approval:</strong><br>
                                @if($leaveApplication->hod_approval_status == 'approved')
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Approved</span><br>
                                    <small class="text-muted">{{ $leaveApplication->hod_approval_date ? \Carbon\Carbon::parse($leaveApplication->hod_approval_date)->format('M d, Y') : '' }}</small>
                                @elseif($leaveApplication->hod_approval_status == 'rejected')
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                @endif
                                
                                @if($leaveApplication->hod_comments)
                                    <br><small>{{ $leaveApplication->hod_comments }}</small>
                                @endif
                            </div>

                            <!-- HR Approval -->
                            <div class="mb-3">
                                <strong>HR Approval:</strong><br>
                                @if($leaveApplication->hr_approval_status == 'approved')
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> Approved</span><br>
                                    <small class="text-muted">{{ $leaveApplication->hr_approval_date ? \Carbon\Carbon::parse($leaveApplication->hr_approval_date)->format('M d, Y') : '' }}</small>
                                @elseif($leaveApplication->hr_approval_status == 'rejected')
                                    <span class="badge badge-danger"><i class="fas fa-times-circle"></i> Rejected</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pending</span>
                                @endif

                                @if($leaveApplication->hr_comments)
                                    <br><small>{{ $leaveApplication->hr_comments }}</small>
                                @endif
                            </div>

                            @if($leaveApplication->rejection_reason)
                                <div class="alert alert-danger">
                                    <strong>Rejection Reason:</strong><br>
                                    {{ $leaveApplication->rejection_reason }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($leaveApplication->application_status == 'pending')
                        <div class="card">
                            <div class="card-header bg-warning">
                                <h3 class="card-title">Actions</h3>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('leave-applications.approve', $leaveApplication->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <div class="form-group">
                                        <label>Comments (Optional)</label>
                                        <textarea name="comments" class="form-control" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger btn-block" data-toggle="modal" data-target="#rejectModal">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('leave-applications.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </section>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('leave-applications.reject', $leaveApplication->id) }}" method="POST">
                    @csrf
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">Reject Leave Application</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="3" required 
                                      placeholder="Please provide a detailed reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
