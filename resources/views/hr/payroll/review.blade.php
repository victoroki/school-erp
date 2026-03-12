@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-invoice-dollar text-success"></i> Review Payroll</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll-processing.index') }}">Payroll</a></li>
                        <li class="breadcrumb-item active">Review</li>
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
                            <h3>{{ count($payrollData) }}</h3>
                            <p>Total Employees</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>KES {{ number_format(array_sum(array_column($payrollData, 'gross_salary')), 2) }}</h3>
                            <p>Total Gross</p>
                        </div>
                        <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>KES {{ number_format(array_sum(array_column($payrollData, 'total_deductions')), 2) }}</h3>
                            <p>Total Deductions</p>
                        </div>
                        <div class="icon"><i class="fas fa-minus-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>KES {{ number_format(array_sum(array_column($payrollData, 'net_salary')), 2) }}</h3>
                            <p>Total Net Pay</p>
                        </div>
                        <div class="icon"><i class="fas fa-hand-holding-usd"></i></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">Payroll Details - {{ date('F Y', mktime(0, 0, 0, $request->month, 1, $request->year)) }}</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th class="text-right">Basic</th>
                                    <th class="text-right">Allowances</th>
                                    <th class="text-right">Gross</th>
                                    <th class="text-right">PAYE</th>
                                    <th class="text-right">NHIF</th>
                                    <th class="text-right">NSSF</th>
                                    <th class="text-right">Other Ded.</th>
                                    <th class="text-right">Total Ded.</th>
                                    <th class="text-right">Net Pay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payrollData as $data)
                                    <tr>
                                        <td>
                                            <strong>{{ $data['staff_name'] }}</strong><br>
                                            <small class="text-muted">{{ $data['employee_number'] }}</small>
                                        </td>
                                        <td class="text-right">{{ number_format($data['basic_salary'], 2) }}</td>
                                        <td class="text-right">{{ number_format($data['allowances'], 2) }}</td>
                                        <td class="text-right"><strong>{{ number_format($data['gross_salary'], 2) }}</strong></td>
                                        <td class="text-right text-danger">{{ number_format($data['paye'], 2) }}</td>
                                        <td class="text-right text-danger">{{ number_format($data['nhif'], 2) }}</td>
                                        <td class="text-right text-danger">{{ number_format($data['nssf'], 2) }}</td>
                                        <td class="text-right text-danger">{{ number_format($data['other_deductions'], 2) }}</td>
                                        <td class="text-right text-danger"><strong>{{ number_format($data['total_deductions'], 2) }}</strong></td>
                                        <td class="text-right text-success"><strong>{{ number_format($data['net_salary'], 2) }}</strong></td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th>TOTALS</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'basic_salary')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'allowances')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'gross_salary')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'paye')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'nhif')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'nssf')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'other_deductions')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'total_deductions')), 2) }}</th>
                                    <th class="text-right">{{ number_format(array_sum(array_column($payrollData, 'net_salary')), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-success" onclick="alert('Payroll finalization coming soon!')">
                        <i class="fas fa-check-circle"></i> Approve & Process Payroll
                    </button>
                    <a href="{{ route('payroll-processing.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </div>
        </div>
    </section>
@endsection
