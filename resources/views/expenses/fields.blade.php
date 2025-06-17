<!-- Category Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('category_id', 'Category :') !!}
    {!! Form::number('category_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Amount Field -->
<div class="form-group col-sm-6">
    {!! Form::label('amount', 'Amount:') !!}
    {!! Form::number('amount', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Expense Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('expense_date', 'Expense Date:') !!}
    {!! Form::text('expense_date', null, ['class' => 'form-control','id'=>'expense_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#expense_date').datepicker()
    </script>
@endpush

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>

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

<!-- Approved By Field -->
<div class="form-group col-sm-6">
    {!! Form::label('approved_by', 'Approved By:') !!}
    {!! Form::number('approved_by', null, ['class' => 'form-control']) !!}
</div>

<!-- Created By Field -->
<div class="form-group col-sm-6">
    {!! Form::label('created_by', 'Created By:') !!}
    {!! Form::number('created_by', null, ['class' => 'form-control']) !!}
</div>