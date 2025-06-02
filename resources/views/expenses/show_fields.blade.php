<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category Id:') !!}
    <p>{{ $expenses->category_id }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ $expenses->amount }}</p>
</div>

<!-- Expense Date Field -->
<div class="col-sm-12">
    {!! Form::label('expense_date', 'Expense Date:') !!}
    <p>{{ $expenses->expense_date }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $expenses->description }}</p>
</div>

<!-- Payment Method Field -->
<div class="col-sm-12">
    {!! Form::label('payment_method', 'Payment Method:') !!}
    <p>{{ $expenses->payment_method }}</p>
</div>

<!-- Reference Number Field -->
<div class="col-sm-12">
    {!! Form::label('reference_number', 'Reference Number:') !!}
    <p>{{ $expenses->reference_number }}</p>
</div>

<!-- Approved By Field -->
<div class="col-sm-12">
    {!! Form::label('approved_by', 'Approved By:') !!}
    <p>{{ $expenses->approved_by }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $expenses->created_by }}</p>
</div>

