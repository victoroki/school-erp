<!-- Academic Year Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    <p>{{ $feeStructure->academicYear->name ?? 'N/A' }}</p>
</div>

<!-- Class Field -->
<div class="col-sm-12">
    {!! Form::label('class_id', 'Class:') !!}
    <p>{{ $feeStructure->schoolClass->name ?? 'N/A' }}</p>
</div>

<!-- Category Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category:') !!}
    <p>{{ $feeStructure->category->name ?? 'N/A' }}</p>
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

