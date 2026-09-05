@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-check text-secondary"></i> Staff Attendance</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Attendance</li>
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
                            <h3>{{ $summary['total'] }}</h3>
                            <p>Total Staff</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $summary['present'] }}</h3>
                            <p>Present</p>
                        </div>
                        <div class="icon"><i class="fas fa-check"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $summary['absent'] }}</h3>
                            <p>Absent</p>
                        </div>
                        <div class="icon"><i class="fas fa-times"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $summary['on_leave'] }}</h3>
                            <p>On Leave</p>
                        </div>
                        <div class="icon"><i class="fas fa-calendar-times"></i></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Attendance for {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff-attendance.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-check-double"></i> Mark Attendance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <input type="date" name="date" class="form-control form-control-sm" value="{{ $date }}">
                            </div>
                            <div class="col-md-3">
                                <select name="department_id" class="form-select form-select-sm">
                                    <option value="">All Departments</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->department_id }}" {{ $departmentId == $dept->department_id ? 'selected' : '' }}>
                                            {{ $dept->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
                                <a href="{{ route('staff-attendance.index') }}" class="btn btn-secondary btn-sm">Clear</a>
                            </div>
                        </div>
                    </form>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Staff</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($attendances as $att)
                                    <tr>
                                        <td>{{ $att->staff->full_name ?? 'N/A' }}</td>
                                        <td>{{ $att->staff->department->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($att->status == 'present')
                                                <span class="badge badge-success">Present</span>
                                            @elseif($att->status == 'absent')
                                                <span class="badge badge-danger">Absent</span>
                                            @elseif($att->status == 'late')
                                                <span class="badge badge-warning">Late</span>
                                            @elseif($att->status == 'on_leave')
                                                <span class="badge badge-info">On Leave</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($att->status) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $att->time_in ? \Carbon\Carbon::parse($att->time_in)->format('h:i A') : '-' }}</td>
                                        <td>{{ $att->time_out ? \Carbon\Carbon::parse($att->time_out)->format('h:i A') : '-' }}</td>
                                        <td>{{ $att->notes ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('staff-attendance.edit', $att->attendance_id) }}" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No attendance records for this date</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
