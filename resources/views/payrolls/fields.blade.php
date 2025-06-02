<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff Id:') !!}
    {!! Form::number('staff_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Salary Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('salary_id', 'Salary Id:') !!}
    {!! Form::number('salary_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Month Field -->
<div class="form-group col-sm-6">
    {!! Form::label('month', 'Month:') !!}
    {!! Form::number('month', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('year', 'Year:') !!}
    {!! Form::number('year', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Working Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('working_days', 'Working Days:') !!}
    {!! Form::number('working_days', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Paid Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('paid_days', 'Paid Days:') !!}
    {!! Form::number('paid_days', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Absent Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('absent_days', 'Absent Days:') !!}
    {!! Form::number('absent_days', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Leave Days Field -->
<div class="form-group col-sm-6">
    {!! Form::label('leave_days', 'Leave Days:') !!}
    {!! Form::number('leave_days', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Basic Salary Field -->
<div class="form-group col-sm-6">
    {!! Form::label('basic_salary', 'Basic Salary:') !!}
    {!! Form::number('basic_salary', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Allowances Field -->
<div class="form-group col-sm-6">
    {!! Form::label('allowances', 'Allowances:') !!}
    {!! Form::number('allowances', null, ['class' => 'form-control']) !!}
</div>

<!-- Overtime Field -->
<div class="form-group col-sm-6">
    {!! Form::label('overtime', 'Overtime:') !!}
    {!! Form::number('overtime', null, ['class' => 'form-control']) !!}
</div>

<!-- Gross Salary Field -->
<div class="form-group col-sm-6">
    {!! Form::label('gross_salary', 'Gross Salary:') !!}
    {!! Form::number('gross_salary', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Deductions Field -->
<div class="form-group col-sm-6">
    {!! Form::label('deductions', 'Deductions:') !!}
    {!! Form::number('deductions', null, ['class' => 'form-control']) !!}
</div>

<!-- Net Salary Field -->
<div class="form-group col-sm-6">
    {!! Form::label('net_salary', 'Net Salary:') !!}
    {!! Form::number('net_salary', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Payment Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('payment_date', 'Payment Date:') !!}
    {!! Form::text('payment_date', null, ['class' => 'form-control','id'=>'payment_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#payment_date').datepicker()
    </script>
@endpush

<!-- Payment Method Field -->
<div class="form-group col-sm-6">
    {!! Form::label('payment_method', 'Payment Method:') !!}
    {!! Form::text('payment_method', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Reference Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('reference_number', 'Reference Number:') !!}
    {!! Form::text('reference_number', null, ['class' => 'form-control', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Remarks Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>