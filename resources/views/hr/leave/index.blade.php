@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-times text-secondary"></i> Leave Applications</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Leave Applications</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Leave Applications</h3>
                    <div class="card-tools">
                        <a href="{{ route('leave-applications.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Apply for Leave
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <select name="status" class="form-control form-control-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="leave_type_id" class="form-control form-control-sm">
                                    <option value="">All Leave Types</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type->leave_type_id }}" {{ request('leave_type_id') == $type->leave_type_id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                                <a href="{{ route('leave-applications.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Days</th>
                                    <th>Status</th>
                                    <th>HOD Approval</th>
                                    <th>HR Approval</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $app)
                                    <tr>
                                        <td>{{ $app->staff->full_name ?? 'N/A' }}</td>
                                        <td>{{ $app->leaveType->name ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($app->start_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($app->end_date)->format('M d, Y') }}</td>
                                        <td><span class="badge badge-info">{{ $app->working_days }} days</span></td>
                                        <td>
                                            @if($app->application_status == 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif($app->application_status == 'approved')
                                                <span class="badge badge-success">Approved</span>
                                            @elseif($app->application_status == 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($app->application_status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($app->hod_approval_status == 'approved')
                                                <i class="fas fa-check-circle text-success"></i>
                                            @elseif($app->hod_approval_status == 'rejected')
                                                <i class="fas fa-times-circle text-danger"></i>
                                            @else
                                                <i class="fas fa-clock text-warning"></i>
                                            @endif
                                        </td>
                                        <td>
                                            @if($app->hr_approval_status == 'approved')
                                                <i class="fas fa-check-circle text-success"></i>
                                            @elseif($app->hr_approval_status == 'rejected')
                                                <i class="fas fa-times-circle text-danger"></i>
                                            @else
                                                <i class="fas fa-clock text-warning"></i>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('leave-applications.show', $app->id) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($app->application_status == 'pending')
                                                <form action="{{ route('leave-applications.approve', $app->id) }}" method="POST" style="display: inline-block;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No leave applications found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $applications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
