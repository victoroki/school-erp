<!-- Account Name Field -->
<div class="col-sm-12">
    {!! Form::label('account_name', 'Account Name:') !!}
    <p>{{ $bankAccount->account_name }}</p>
</div>

<!-- Account Number Field -->
<div class="col-sm-12">
    {!! Form::label('account_number', 'Account Number:') !!}
    <p>{{ $bankAccount->account_number }}</p>
</div>

<!-- Bank Name Field -->
<div class="col-sm-12">
    {!! Form::label('bank_name', 'Bank Name:') !!}
    <p>{{ $bankAccount->bank_name }}</p>
</div>

<!-- Branch Name Field -->
<div class="col-sm-12">
    {!! Form::label('branch_name', 'Branch Name:') !!}
    <p>{{ $bankAccount->branch_name }}</p>
</div>

<!-- Ifsc Code Field -->
<div class="col-sm-12">
    {!! Form::label('ifsc_code', 'Ifsc Code:') !!}
    <p>{{ $bankAccount->ifsc_code }}</p>
</div>

<!-- Opening Balance Field -->
<div class="col-sm-12">
    {!! Form::label('opening_balance', 'Opening Balance:') !!}
    <p>{{ $bankAccount->opening_balance }}</p>
</div>

<!-- Current Balance Field -->
<div class="col-sm-12">
    {!! Form::label('current_balance', 'Current Balance:') !!}
    <p>{{ $bankAccount->current_balance }}</p>
</div>

<!-- Account Type Field -->
<div class="col-sm-12">
    {!! Form::label('account_type', 'Account Type:') !!}
    <p>{{ $bankAccount->account_type }}</p>
</div>

