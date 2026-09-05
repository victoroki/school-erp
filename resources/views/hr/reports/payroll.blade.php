@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-money-bill-wave text-success"></i> Payroll Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Payroll Report</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Month</label>
                                <select name="month" class="form-select">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ \Carbon\Carbon::create()->month($m)->format('F') }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Year</label>
                                <select name="year" class="form-select">
                                    @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Total Payroll Cost -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Payroll Cost</span>
                            <span class="info-box-number">KSh {{ number_format($totalPayrollCost, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Active Staff</span>
                            <span class="info-box-number">{{ $byDepartment->sum('staff_count') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Salary</span>
                            <span class="info-box-number">KSh {{ number_format($byDepartment->sum('staff_count') > 0 ? $totalPayrollCost / $byDepartment->sum('staff_count') : 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payroll by Department -->
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">Payroll by Department</h3>
                </div>
                <div class="card-body">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th class="text-right">Staff Count</th>
                                <th class="text-right">Total Salary</th>
                                <th class="text-right">Average Salary</th>
                                <th class="text-right">% of Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($byDepartment as $dept)
                                <tr>
                                    <td>{{ $dept['department'] }}</td>
                                    <td class="text-right"><span class="badge badge-secondary">{{ $dept['staff_count'] }}</span></td>
                                    <td class="text-right">KSh {{ number_format($dept['total_salary'], 2) }}</td>
                                    <td class="text-right">KSh {{ number_format($dept['average_salary'], 2) }}</td>
                                    <td class="text-right">
                                        @if($totalPayrollCost > 0)
                                            <span class="badge badge-info">{{ number_format(($dept['total_salary'] / $totalPayrollCost) * 100, 1) }}%</span>
                                        @else
                                            <span class="badge badge-info">0%</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold">
                                <td>Total</td>
                                <td class="text-right">{{ $byDepartment->sum('staff_count') }}</td>
                                <td class="text-right">KSh {{ number_format($totalPayrollCost, 2) }}</td>
                                <td class="text-right">KSh {{ number_format($byDepartment->sum('staff_count') > 0 ? $totalPayrollCost / $byDepartment->sum('staff_count') : 0, 2) }}</td>
                                <td class="text-right">100%</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
