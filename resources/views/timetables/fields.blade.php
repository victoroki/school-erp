<!-- Class Section Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select('class_section_id',
        ['' => 'Select Class Section'] + $classSections,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Day Of Week Field -->
<div class="form-group col-sm-6">
    {!! Form::label('day_of_week', 'Day Of Week:') !!}
    {!! Form::select('day_of_week',
        ['' => 'Select Day'] + $daysOfWeek,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Period Field -->
<div class="form-group col-sm-6">
    {!! Form::label('period_id', 'Period:') !!}
    {!! Form::select('period_id',
        ['' => 'Select Period'] + $periods,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Subject Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select('subject_id',
        ['' => 'Select Subject'] + $subjects,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Teacher Field -->
<div class="form-group col-sm-6">
    {!! Form::label('teacher_id', 'Teacher:') !!}
    {!! Form::select('teacher_id',
        ['' => 'Select Teacher'] + $teachers,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Classroom Field -->
<div class="form-group col-sm-6">
    {!! Form::label('classroom_id', 'Classroom:') !!}
    {!! Form::select('classroom_id',
        ['' => 'Select Classroom'] + $classrooms,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>

<!-- Academic Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id',
        ['' => 'Select Academic Year'] + $academicYears,
        null,
        ['class' => 'form-control', 'required']) !!}
</div>
