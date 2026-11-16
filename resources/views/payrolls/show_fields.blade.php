<!-- Staff Id Field -->
<div class="col-sm-12">
    {!! Form::label('staff_id', 'Staff:') !!}
    <p>{{ $payroll->staff->full_name ?? 'N/A' }}</p>
</div>

<!-- Salary Id Field -->
<div class="col-sm-12">
    {!! Form::label('salary_id', 'Salary Grade:') !!}
    <p>{{ $payroll->salary_id ?? '—' }}</p>
</div>

<!-- Month Field -->
<div class="col-sm-12">
    {!! Form::label('month', 'Month:') !!}
    <p>{{ $payroll->month }}</p>
</div>

<!-- Year Field -->
<div class="col-sm-12">
    {!! Form::label('year', 'Year:') !!}
    <p>{{ $payroll->year }}</p>
</div>

<!-- Working Days Field -->
<div class="col-sm-12">
    {!! Form::label('working_days', 'Working Days:') !!}
    <p>{{ $payroll->working_days }}</p>
</div>

<!-- Paid Days Field -->
<div class="col-sm-12">
    {!! Form::label('paid_days', 'Paid Days:') !!}
    <p>{{ $payroll->paid_days }}</p>
</div>

<!-- Absent Days Field -->
<div class="col-sm-12">
    {!! Form::label('absent_days', 'Absent Days:') !!}
    <p>{{ $payroll->absent_days }}</p>
</div>

<!-- Leave Days Field -->
<div class="col-sm-12">
    {!! Form::label('leave_days', 'Leave Days:') !!}
    <p>{{ $payroll->leave_days }}</p>
</div>

<!-- Basic Salary Field -->
<div class="col-sm-12">
    {!! Form::label('basic_salary', 'Basic Salary:') !!}
    <p>{{ \App\Support\Money::format($payroll->basic_salary ?? 0) }}</p>
</div>

<!-- Allowances Field -->
<div class="col-sm-12">
    {!! Form::label('allowances', 'Allowances:') !!}
    <p>{{ \App\Support\Money::format($payroll->allowances ?? 0) }}</p>
</div>

<!-- Overtime Field -->
<div class="col-sm-12">
    {!! Form::label('overtime', 'Overtime:') !!}
    <p>{{ \App\Support\Money::format($payroll->overtime ?? 0) }}</p>
</div>

<!-- Gross Salary Field -->
<div class="col-sm-12">
    {!! Form::label('gross_salary', 'Gross Salary:') !!}
    <p>{{ \App\Support\Money::format($payroll->gross_salary ?? 0) }}</p>
</div>

<!-- Deductions Field -->
<div class="col-sm-12">
    {!! Form::label('deductions', 'Deductions:') !!}
    <p>{{ \App\Support\Money::format($payroll->deductions ?? 0) }}</p>
</div>

<!-- Net Salary Field -->
<div class="col-sm-12">
    {!! Form::label('net_salary', 'Net Salary:') !!}
    <p>{{ \App\Support\Money::format($payroll->net_salary ?? 0) }}</p>
</div>

<!-- Payment Date Field -->
<div class="col-sm-12">
    {!! Form::label('payment_date', 'Payment Date:') !!}
    <p>{{ $payroll->payment_date ? \Carbon\Carbon::parse($payroll->payment_date)->format('d/m/Y') : 'Not paid' }}</p>
</div>

<!-- Payment Method Field -->
<div class="col-sm-12">
    {!! Form::label('payment_method', 'Payment Method:') !!}
    <p>{{ ucfirst(str_replace('_', ' ', $payroll->payment_method ?? '')) ?: '—' }}</p>
</div>

<!-- Reference Number Field -->
<div class="col-sm-12">
    {!! Form::label('reference_number', 'Reference Number:') !!}
    <p>{{ $payroll->reference_number }}</p>
</div>

<!-- Remarks Field -->
<div class="col-sm-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    <p>{{ $payroll->remarks ?? '—' }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ ucfirst($payroll->status ?? '') ?: '—' }}</p>
</div>

