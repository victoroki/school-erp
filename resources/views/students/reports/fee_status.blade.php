@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-coins text-danger mr-2"></i>Fee Status Report
                </h1>
                <p class="text-muted small mb-0">Student fee balances and payment status for {{ $currentYear->name ?? 'current year' }}</p>
            </div>
            <div class="col-sm-6 text-right">
                <div class="btn-group mr-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle" data-toggle="dropdown"><i class="fas fa-download mr-1"></i> Export</button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('student-reports.fee-status.csv', request()->query()) }}"><i class="fas fa-file-csv text-success mr-2"></i>Download CSV</a>
                        <a class="dropdown-item" href="{{ route('student-reports.fee-status.pdf', request()->query()) }}"><i class="fas fa-file-pdf text-danger mr-2"></i>Download PDF</a>
                    </div>
                </div>
                <button onclick="window.print()" class="btn btn-outline-info btn-sm shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print
                </button>
                <a href="{{ route('student-reports.index') }}" class="btn btn-default btn-sm shadow-sm border ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    {{-- Filter --}}
    <div class="card elevation-1 border-0 mb-4">
        <div class="card-body py-3">
            <form action="{{ route('student-reports.fee-status') }}" method="GET" class="form-inline justify-content-end">
                <label class="small font-weight-bold text-uppercase mr-2">Academic Year</label>
                <select name="academic_year_id" class="form-control form-control-sm mr-2" style="width: 200px;" onchange="this.form.submit()">
                    @foreach($academicYears as $year)
                        <option value="{{ $year->academic_year_id }}" {{ $yearId == $year->academic_year_id ? 'selected' : '' }}>
                            {{ $year->name }} @if($year->is_current) (Current) @endif
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>

    {{-- Stats Strip --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card border-left-danger">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle text-danger"></i></div>
                <div>
                    <div class="stat-value text-danger">{{ $studentsWithArrears }}</div>
                    <div class="stat-label">Students with Arrears</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-left-primary">
                <div class="stat-icon"><i class="fas fa-file-invoice-dollar text-primary"></i></div>
                <div>
                    <div class="stat-value text-primary">KES {{ number_format($totalAssigned, 0) }}</div>
                    <div class="stat-label">Total Assigned</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-left-success">
                <div class="stat-icon"><i class="fas fa-check-circle text-success"></i></div>
                <div>
                    <div class="stat-value text-success">KES {{ number_format($totalPaid, 0) }}</div>
                    <div class="stat-label">Total Collected</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card border-left-warning">
                <div class="stat-icon"><i class="fas fa-balance-scale text-warning"></i></div>
                <div>
                    <div class="stat-value text-warning">KES {{ number_format($totalBalance, 0) }}</div>
                    <div class="stat-label">Outstanding Balance</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-outline card-danger elevation-2 border-0">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold">Student Fee Status</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="bg-light text-muted small text-uppercase">
                            <th class="pl-4">Student</th>
                            <th>Class</th>
                            <th class="text-right">Assigned</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $s)
                            <tr>
                                <td class="pl-4">
                                    <span class="font-weight-bold text-dark">{{ $s->full_name }}</span>
                                    <br><small class="text-muted">{{ $s->admission_no }}</small>
                                </td>
                                <td>{{ $s->class_info }}</td>
                                <td class="text-right">KES {{ number_format($s->total_assigned, 2) }}</td>
                                <td class="text-right text-success font-weight-bold">KES {{ number_format($s->total_paid, 2) }}</td>
                                <td class="text-right font-weight-bold {{ $s->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    KES {{ number_format($s->balance, 2) }}
                                </td>
                                <td class="text-center">
                                    @if($s->balance <= 0)
                                        <span class="badge badge-success px-2 py-1">Paid</span>
                                    @elseif($s->balance < $s->total_assigned * 0.5)
                                        <span class="badge badge-warning px-2 py-1">Partial</span>
                                    @else
                                        <span class="badge badge-danger px-2 py-1">Unpaid</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-check-circle fa-2x text-success d-block mb-2"></i>
                                    No fee assignments found for this academic year.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-left: 4px solid;
    }
    .stat-icon { font-size: 1.5rem; }
    .stat-value { font-size: 1.15rem; font-weight: 800; color: #1e293b; }
    .stat-label { font-size: 0.72rem; color: #64748b; text-transform: uppercase; font-weight: 600; letter-spacing: 0.05em; }
    @media print {
        .btn, .main-header, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@endsection
