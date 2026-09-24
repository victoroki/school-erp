<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="payrolls-table">
            <thead>
            <tr>
                <th>Staff </th>
                <th>Salary </th>
                <th>Month</th>
                <th>Year</th>
                <th>Working Days</th>
                <th>Paid Days</th>
                <th>Absent Days</th>
                <th>Leave Days</th>
                <th>Basic Salary</th>
                <th>Allowances</th>
                <th>Overtime</th>
                <th>Gross Salary</th>
                <th>Deductions</th>
                <th>Net Salary</th>
                <th>Payment Date</th>
                <th>Payment Method</th>
                <th>Reference Number</th>
                <th>Remarks</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($payrolls as $payroll)
                <tr>
                    <td>{{ $payroll->staff->full_name ?? 'N/A' }}</td>
                    <td>{{ $payroll->salary_id ?? '—' }}</td>
                    <td>{{ $payroll->month }}</td>
                    <td>{{ $payroll->year }}</td>
                    <td>{{ $payroll->working_days }}</td>
                    <td>{{ $payroll->paid_days }}</td>
                    <td>{{ $payroll->absent_days }}</td>
                    <td>{{ $payroll->leave_days }}</td>
                    <td>{{ \App\Support\Money::format($payroll->basic_salary ?? 0) }}</td>
                    <td>{{ \App\Support\Money::format($payroll->allowances ?? 0) }}</td>
                    <td>{{ \App\Support\Money::format($payroll->overtime ?? 0) }}</td>
                    <td>{{ \App\Support\Money::format($payroll->gross_salary ?? 0) }}</td>
                    <td>{{ \App\Support\Money::format($payroll->deductions ?? 0) }}</td>
                    <td>{{ \App\Support\Money::format($payroll->net_salary ?? 0) }}</td>
                    <td>{{ $payroll->payment_date ? \Carbon\Carbon::parse($payroll->payment_date)->format('d/m/Y') : '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $payroll->payment_method ?? '')) }}</td>
                    <td>{{ $payroll->reference_number ?? '—' }}</td>
                    <td>{{ $payroll->remarks ?? '—' }}</td>
                    <td>{{ ucfirst($payroll->status ?? '') }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['payrolls.destroy', $payroll->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('payrolls.show', [$payroll->payroll_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('payrolls.edit', [$payroll->payroll_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $payrolls])
        </div>
    </div>
</div>
