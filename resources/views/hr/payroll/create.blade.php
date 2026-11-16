@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-users-cog text-secondary"></i> Staff on Payroll</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll-processing.index') }}">Payroll</a></li>
                        <li class="breadcrumb-item active">Select Staff</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Step 1 of 2 — review the active staff included in this payroll run, then proceed to
                <strong>Calculate Payroll</strong> for the target month.
            </div>

            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title">Active Staff ({{ $staff->count() }})</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th class="px-4">Staff</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th class="text-right">Basic Salary</th>
                                    <th class="text-right">Allowances</th>
                                    <th class="text-right">Deductions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $employee)
                                    <tr>
                                        <td class="px-4">
                                            {{ $employee->full_name }}<br>
                                            <small class="text-muted">{{ $employee->employee_number ?? 'No ID' }}</small>
                                        </td>
                                        <td>{{ $employee->department->name ?? 'N/A' }}</td>
                                        <td>{{ $employee->jobPosition->title ?? $employee->jobPosition->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ \App\Support\Money::format($employee->basic_salary ?? 0) }}</td>
                                        <td class="text-right">{{ \App\Support\Money::format($employee->allowances->sum('amount')) }}</td>
                                        <td class="text-right">{{ \App\Support\Money::format($employee->deductions->sum('monthly_amount')) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No active staff found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('payroll-processing.index') }}" class="btn btn-success">
                        <i class="fas fa-calculator"></i> Proceed to Calculate
                    </a>
                    <a href="{{ route('payroll-processing.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </section>
@endsection
