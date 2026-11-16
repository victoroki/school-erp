@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-slash text-danger"></i> Initiate Staff Exit</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.exit') }}">Exit Management</a></li>
                        <li class="breadcrumb-item active">Initiate</li>
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
                        <div class="card-header bg-danger">
                            <h3 class="card-title">Exit Details — {{ $staff->full_name }}</h3>
                        </div>
                        <form action="{{ route('hr.exit.store') }}" method="POST">
                            @csrf
                            <div class="card-body">
                                <input type="hidden" name="staff_id" value="{{ $staff->staff_id }}">

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    This will set the staff member's status to <strong>resigned</strong> or <strong>terminated</strong>,
                                    record the exit, and <strong>deactivate their user account</strong>.
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Exit Type <span class="text-danger">*</span></label>
                                            <select name="exit_type" class="form-control" required>
                                                <option value="resignation">Resignation</option>
                                                <option value="termination">Termination</option>
                                                <option value="retirement">Retirement</option>
                                                <option value="contract_end">Contract End</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Exit Date <span class="text-danger">*</span></label>
                                            <input type="date" name="exit_date" class="form-control" value="{{ old('exit_date', now()->toDateString()) }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Notice Period (days)</label>
                                            <input type="number" name="notice_period_days" class="form-control" min="0" value="{{ old('notice_period_days') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Reason <span class="text-danger">*</span></label>
                                    <textarea name="reason" class="form-control" rows="4" required placeholder="Provide detailed justification for the exit…">{{ old('reason') }}</textarea>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-user-slash"></i> Initiate Exit
                                </button>
                                <a href="{{ route('hr.exit') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Staff Information</h3>
                        </div>
                        <div class="card-body">
                            <p class="mb-1"><strong>{{ $staff->full_name }}</strong></p>
                            <p class="text-muted mb-1">{{ $staff->employee_number ?? 'No employee number' }}</p>
                            <p class="mb-1">{{ $staff->department->name ?? 'No department' }}</p>
                            <p class="mb-0">{{ $staff->jobPosition->title ?? $staff->jobPosition->name ?? 'No position' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
