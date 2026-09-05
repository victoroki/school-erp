@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-check-double text-success"></i> Mark Attendance</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff-attendance.index') }}">Attendance</a></li>
                        <li class="breadcrumb-item active">Mark</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('staff-attendance.store') }}" method="POST">
                @csrf
                <div class="card">
                    <div class="card-header bg-success">
                        <h3 class="card-title">Mark Attendance for {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</h3>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="date" value="{{ $date }}">
                        
                        <!-- Quick Actions -->
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-success" onclick="markAll('present')">
                                <i class="fas fa-check-circle"></i> Mark All Present
                            </button>
                            <button type="button" class="btn btn-sm btn-danger" onclick="markAll('absent')">
                                <i class="fas fa-times-circle"></i> Mark All Absent
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Staff</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Time In</th>
                                        <th>Time Out</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staff as $index => $employee)
                                        @if(!in_array($employee->staff_id, $existingAttendance))
                                            <tr>
                                                <td>
                                                    {{ $employee->full_name }}<br>
                                                    <small class="text-muted">{{ $employee->employee_number }}</small>
                                                </td>
                                                <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                                <td>
                                                    <input type="hidden" name="attendance[{{ $index }}][staff_id]" value="{{ $employee->staff_id }}">
                                                    <select name="attendance[{{ $index }}][status]" class="form-select form-select-sm status-select" required>
                                                        <option value="present">Present</option>
                                                        <option value="absent">Absent</option>
                                                        <option value="late">Late</option>
                                                        <option value="half_day">Half Day</option>
                                                        <option value="on_leave">On Leave</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <input type="time" name="attendance[{{ $index }}][time_in]" class="form-control form-control-sm">
                                                </td>
                                                <td>
                                                    <input type="time" name="attendance[{{ $index }}][time_out]" class="form-control form-control-sm">
                                                </td>
                                                <td>
                                                    <input type="text" name="attendance[{{ $index }}][notes]" class="form-control form-control-sm" placeholder="Optional notes">
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Attendance
                        </button>
                        <a href="{{ route('staff-attendance.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        function markAll(status) {
            document.querySelectorAll('.status-select').forEach(function(select) {
                select.value = status;
            });
        }
    </script>
@endsection
