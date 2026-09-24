@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-edit text-primary"></i> Edit Attendance</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff-attendance.index') }}">Attendance</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title">
                        Edit record for {{ $staffAttendance->staff->full_name ?? 'N/A' }} — {{ $staffAttendance->date->format('d/m/Y') }}
                    </h3>
                </div>
                <form action="{{ route('staff-attendance.update', $staffAttendance) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        @foreach(['present', 'absent', 'late', 'half_day', 'on_leave'] as $status)
                                            <option value="{{ $status }}" {{ old('status', $staffAttendance->status) == $status ? 'selected' : '' }}>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Time In</label>
                                    <input type="time" name="time_in" class="form-control" value="{{ old('time_in', $staffAttendance->time_in?->format('H:i')) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Time Out</label>
                                    <input type="time" name="time_out" class="form-control" value="{{ old('time_out', $staffAttendance->time_out?->format('H:i')) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes">{{ old('notes', $staffAttendance->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Record
                        </button>
                        <a href="{{ route('staff-attendance.show', $staffAttendance) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
