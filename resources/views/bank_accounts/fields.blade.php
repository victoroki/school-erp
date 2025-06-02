<!-- Account Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('account_name', 'Account Name:') !!}
    {!! Form::text('account_name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Account Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('account_number', 'Account Number:') !!}
    {!! Form::text('account_number', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Bank Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('bank_name', 'Bank Name:') !!}
    {!! Form::text('bank_name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Branch Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('branch_name', 'Branch Name:') !!}
    {!! Form::text('branch_name', null, ['class' => 'form-control', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Ifsc Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('ifsc_code', 'Ifsc Code:') !!}
    {!! Form::text('ifsc_code', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Opening Balance Field -->
<div class="form-group col-sm-6">
    {!! Form::label('opening_balance', 'Opening Balance:') !!}
    {!! Form::number('opening_balance', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Current Balance Field -->
<div class="form-group col-sm-6">
    {!! Form::label('current_balance', 'Current Balance:') !!}
    {!! Form::number('current_balance', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Account Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('account_type', 'Account Type:') !!}
    {!! Form::text('account_type', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>