<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student Id:') !!}
    <p>{{ $studentFeeDiscount->student_id }}</p>
</div>

<!-- Discount Id Field -->
<div class="col-sm-12">
    {!! Form::label('discount_id', 'Discount Id:') !!}
    <p>{{ $studentFeeDiscount->discount_id }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    <p>{{ $studentFeeDiscount->academic_year_id }}</p>
</div>

