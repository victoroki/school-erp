@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-calendar-plus text-success"></i> Apply for Leave</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave-applications.index') }}">Leave</a></li>
                        <li class="breadcrumb-item active">Apply</li>
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
                        <div class="card-header bg-success">
                            <h3 class="card-title">Leave Application Form</h3>
                        </div>
                        <form action="{{ route('leave-applications.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Leave Type <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" class="form-control @error('leave_type_id') is-invalid @enderror" required>
                                        <option value="">Select Leave Type</option>
                                        @foreach($leaveTypes as $type)
                                            <option value="{{ $type->leave_type_id }}" {{ old('leave_type_id') == $type->leave_type_id ? 'selected' : '' }}>
                                                {{ $type->name }} ({{ $type->days_allocated }} days/year)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('leave_type_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Start Date <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                                   value="{{ old('start_date') }}" min="{{ date('Y-m-d') }}" required>
                                            @error('start_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>End Date <span class="text-danger">*</span></label>
                                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                                   value="{{ old('end_date') }}" min="{{ date('Y-m-d') }}" required>
                                            @error('end_date')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Reason for Leave <span class="text-danger">*</span></label>
                                    <textarea name="reason" rows="3" class="form-control @error('reason') is-invalid @enderror" 
                                              placeholder="Please provide detailed reason for your leave..." required>{{ old('reason') }}</textarea>
                                    @error('reason')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Relief/Substitute Staff (Optional)</label>
                                    <select name="relief_staff_id" class="form-control">
                                        <option value="">Select Relief Staff</option>
                                        @foreach($reliefStaff as $staff)
                                            <option value="{{ $staff->staff_id }}" {{ old('relief_staff_id') == $staff->staff_id ? 'selected' : '' }}>
                                                {{ $staff->full_name }} - {{ $staff->jobPosition->title ?? 'N/A' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>Handover Notes (Optional)</label>
                                    <textarea name="handover_notes" rows="3" class="form-control" 
                                              placeholder="What needs to be done while you're away...">{{ old('handover_notes') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Supporting Document (Optional)</label>
                                    <input type="file" name="supporting_document" class="form-control-file @error('supporting_document') is-invalid @enderror" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">Upload medical certificate if sick leave > 3 days. Max 2MB.</small>
                                    @error('supporting_document')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-paper-plane"></i> Submit Application
                                </button>
                                <a href="{{ route('leave-applications.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-info">
                            <h3 class="card-title">Your Leave Balance</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th class="text-right">Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaveBalances as $balance)
                                        <tr>
                                            <td>{{ $balance->leaveType->name ?? 'N/A' }}</td>
                                            <td class="text-right">
                                                <span class="badge badge-{{ $balance->remaining > 5 ? 'success' : 'warning' }}">
                                                    {{ $balance->remaining }} days
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center text-muted">No leave balance found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-warning">
                            <h3 class="card-title">Important Notes</h3>
                        </div>
                        <div class="card-body">
                            <ul class="pl-3">
                                <li>Leave applications require approval from HOD and HR</li>
                                <li>Apply at least 7 days in advance for annual leave</li>
                                <li>Medical certificate required for sick leave > 3 days</li>
                                <li>Check your leave balance before applying</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
