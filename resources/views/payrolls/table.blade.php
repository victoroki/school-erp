<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="payrolls-table">
            <thead>
            <tr>
                <th>Staff Id</th>
                <th>Salary Id</th>
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
                    <td>{{ $payroll->staff_id }}</td>
                    <td>{{ $payroll->salary_id }}</td>
                    <td>{{ $payroll->month }}</td>
                    <td>{{ $payroll->year }}</td>
                    <td>{{ $payroll->working_days }}</td>
                    <td>{{ $payroll->paid_days }}</td>
                    <td>{{ $payroll->absent_days }}</td>
                    <td>{{ $payroll->leave_days }}</td>
                    <td>{{ $payroll->basic_salary }}</td>
                    <td>{{ $payroll->allowances }}</td>
                    <td>{{ $payroll->overtime }}</td>
                    <td>{{ $payroll->gross_salary }}</td>
                    <td>{{ $payroll->deductions }}</td>
                    <td>{{ $payroll->net_salary }}</td>
                    <td>{{ $payroll->payment_date }}</td>
                    <td>{{ $payroll->payment_method }}</td>
                    <td>{{ $payroll->reference_number }}</td>
                    <td>{{ $payroll->remarks }}</td>
                    <td>{{ $payroll->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['payrolls.destroy', $payroll->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('payrolls.show', [$payroll->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('payrolls.edit', [$payroll->id]) }}"
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
