<!-- Student Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student Id:') !!}
    {!! Form::number('student_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Class Section Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section Id:') !!}
    {!! Form::number('class_section_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Roll Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('roll_number', 'Roll Number:') !!}
    {!! Form::text('roll_number', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    {!! Form::number('academic_year_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Enrollment Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('enrollment_date', 'Enrollment Date:') !!}
    {!! Form::text('enrollment_date', null, ['class' => 'form-control','id'=>'enrollment_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#enrollment_date').datepicker()
    </script>
@endpush

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>