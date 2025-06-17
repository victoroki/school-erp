<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control', 'placeholder' => 'Select Academic Year']) !!}
</div>

<!-- Class Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_id', 'Class:') !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control', 'placeholder' => 'Select Class']) !!}
</div>

<!-- Section Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('section_id', 'Section:') !!}
    {!! Form::select('section_id', $sections, null, ['class' => 'form-control', 'placeholder' => 'Select Section']) !!}
</div>

<!-- Classroom Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('classroom_id', 'Classroom:') !!}
    {!! Form::select('classroom_id', $classrooms, null, ['class' => 'form-control', 'placeholder' => 'Select Classroom']) !!}
</div>

<!-- Class Teacher Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('teacher_id', 'Class Teacher:') !!}
    {!! Form::select('teacher_id', $teachers, null, ['class' => 'form-control', 'placeholder' => 'Select Teacher']) !!}
</div>