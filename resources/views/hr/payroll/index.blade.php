@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-money-check-alt text-secondary"></i> Payroll Processing</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Payroll</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-secondary">
                    <h3 class="card-title">Process New Payroll</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('payroll-processing.calculate') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Month <span class="text-danger">*</span></label>
                                    <select name="month" class="form-select" required>
                                        @for($m = 1; $m <= 12; $m++)
                                            <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Year <span class="text-danger">*</span></label>
                                    <select name="year" class="form-select" required>
                                        @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                            <option value="{{ $y }}" {{ date('Y') == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-calculator"></i> Calculate Payroll
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Payroll Runs</h3>
                </div>
                <div class="card-body">
                    @if($payrolls->isEmpty())
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No payroll runs found. Start by processing a new payroll above.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Staff</th>
                                        <th>Period</th>
                                        <th class="text-right">Basic Salary</th>
                                        <th class="text-right">Gross</th>
                                        <th class="text-right">Deductions</th>
                                        <th class="text-right">Net Salary</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payrolls as $payroll)
                                        <tr>
                                            <td>{{ $payroll->staff->full_name ?? 'N/A' }}</td>
                                            <td>{{ \Carbon\Carbon::create($payroll->year, $payroll->month, 1)->format('M Y') }}</td>
                                            <td class="text-right">{{ \App\Support\Money::format($payroll->basic_salary ?? 0) }}</td>
                                            <td class="text-right">{{ \App\Support\Money::format($payroll->gross_salary ?? 0) }}</td>
                                            <td class="text-right">{{ \App\Support\Money::format($payroll->deductions ?? 0) }}</td>
                                            <td class="text-right font-weight-bold">{{ \App\Support\Money::format($payroll->net_salary ?? 0) }}</td>
                                            <td><span class="badge badge-{{ $payroll->status === 'paid' ? 'success' : ($payroll->status === 'processing' ? 'warning' : 'secondary') }}">{{ ucfirst($payroll->status ?? 'draft') }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                @if($payrolls->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">{{ $payrolls->withQueryString()->links() }}</div>
                </div>
                @endif
            </div>
        </div>
    </section>
@endsection
