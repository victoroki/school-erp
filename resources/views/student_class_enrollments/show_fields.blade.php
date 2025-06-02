<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student Id:') !!}
    <p>{{ $studentClassEnrollment->student_id }}</p>
</div>

<!-- Class Section Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_section_id', 'Class Section Id:') !!}
    <p>{{ $studentClassEnrollment->class_section_id }}</p>
</div>

<!-- Roll Number Field -->
<div class="col-sm-12">
    {!! Form::label('roll_number', 'Roll Number:') !!}
    <p>{{ $studentClassEnrollment->roll_number }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    <p>{{ $studentClassEnrollment->academic_year_id }}</p>
</div>

<!-- Enrollment Date Field -->
<div class="col-sm-12">
    {!! Form::label('enrollment_date', 'Enrollment Date:') !!}
    <p>{{ $studentClassEnrollment->enrollment_date }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $studentClassEnrollment->status }}</p>
</div>

