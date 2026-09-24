@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-clock text-info"></i> Attendance Record</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff-attendance.index') }}">Attendance</a></li>
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
                            <h3 class="card-title">Attendance Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Staff</small>
                                    <div class="font-weight-bold">{{ $staffAttendance->staff->full_name ?? 'N/A' }}</div>
                                    <small class="text-muted">{{ $staffAttendance->staff->employee_number ?? '' }}</small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Department</small>
                                    <div class="font-weight-bold">{{ $staffAttendance->staff->department->name ?? 'N/A' }}</div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Date</small>
                                        <div class="font-weight-bold">{{ $staffAttendance->date->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Status</small>
                                        <div>
                                            @php
                                                $badges = [
                                                    'present' => 'badge-success',
                                                    'absent' => 'badge-danger',
                                                    'late' => 'badge-warning',
                                                    'half_day' => 'badge-info',
                                                    'on_leave' => 'badge-secondary',
                                                ];
                                            @endphp
                                            <span class="badge {{ $badges[$staffAttendance->status] ?? 'badge-secondary' }} p-2">
                                                {{ ucfirst(str_replace('_', ' ', $staffAttendance->status)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-3 bg-light rounded">
                                        <small class="text-muted">Marked By</small>
                                        <div class="font-weight-bold">{{ $staffAttendance->markedBy->name ?? 'System' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Time In</small>
                                    <div class="font-weight-bold">{{ $staffAttendance->time_in ? $staffAttendance->time_in->format('H:i') : '—' }}</div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Time Out</small>
                                    <div class="font-weight-bold">{{ $staffAttendance->time_out ? $staffAttendance->time_out->format('H:i') : '—' }}</div>
                                </div>
                            </div>

                            @if($staffAttendance->notes)
                            <div class="mt-4">
                                <small class="text-muted text-uppercase" style="font-size: 0.75rem;">Notes</small>
                                <div class="p-3 bg-light rounded">{{ $staffAttendance->notes }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('staff-attendance.edit', $staffAttendance) }}" class="btn btn-primary">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <a href="{{ route('staff-attendance.index') }}" class="btn btn-secondary">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
