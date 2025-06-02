<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    <p>{{ $feeStructure->academic_year_id }}</p>
</div>

<!-- Class Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_id', 'Class Id:') !!}
    <p>{{ $feeStructure->class_id }}</p>
</div>

<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category Id:') !!}
    <p>{{ $feeStructure->category_id }}</p>
</div>

<!-- Amount Field -->
<div class="col-sm-12">
    {!! Form::label('amount', 'Amount:') !!}
    <p>{{ $feeStructure->amount }}</p>
</div>

<!-- Due Date Field -->
<div class="col-sm-12">
    {!! Form::label('due_date', 'Due Date:') !!}
    <p>{{ $feeStructure->due_date }}</p>
</div>

