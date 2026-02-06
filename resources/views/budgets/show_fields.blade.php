<!-- Financial Year Field -->
<div class="col-sm-12">
    {!! Form::label('financial_year_id', 'Financial Year:') !!}
    <p>{{ $budget->financialYear->name }}</p>
</div>

<!-- Category Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category:') !!}
    <p>{{ $budget->category ? $budget->category->name : 'N/A' }}</p>
</div>

<!-- Category Type Field -->
<div class="col-sm-12">
    {!! Form::label('category_type', 'Category Type:') !!}
    <p>{{ ucfirst($budget->category_type) }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    <p>KES {{ number_format($budget->amount, 2) }}</p>
</div>

<!-- Alert Threshold Field -->
<div class="col-sm-12">
    {!! Form::label('alert_threshold', 'Alert Threshold:') !!}
    <p>{{ $budget->alert_threshold }}%</p>
</div>

<!-- Created At Field -->
<div class="col-sm-12">
    {!! Form::label('created_at', 'Created At:') !!}
    <p>{{ $budget->created_at }}</p>
</div>

<!-- Updated At Field -->
<div class="col-sm-12">
    {!! Form::label('updated_at', 'Updated At:') !!}
    <p>{{ $budget->updated_at }}</p>
</div>
