<!-- Academic Year Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('academic_year_id', 'Academic Year', ['class' => 'dash-label']) !!}
    {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control dash-control', 'placeholder' => 'Select Academic Year', 'required']) !!}
</div>

<!-- Class Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('class_id', 'Class Level', ['class' => 'dash-label']) !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control dash-control', 'placeholder' => 'Select Class', 'required']) !!}
</div>

<!-- Section Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('section_id', 'Section Name', ['class' => 'dash-label']) !!}
    {!! Form::select('section_id', $sections, null, ['class' => 'form-control dash-control', 'placeholder' => 'Select Section', 'required']) !!}
</div>

<!-- Classroom Field -->
<div class="form-group col-sm-6 mb-3">
    {!! Form::label('classroom_id', 'Assigned Room', ['class' => 'dash-label']) !!}
    {!! Form::select('classroom_id', $classrooms, null, ['class' => 'form-control dash-control', 'placeholder' => 'Select Classroom']) !!}
</div>

<!-- Class Teacher Field -->
<div class="form-group col-sm-12 mb-3">
    {!! Form::label('class_teacher_id', 'Class Teacher', ['class' => 'dash-label']) !!}
    {!! Form::select('class_teacher_id', $teachers, null, ['class' => 'form-control dash-control', 'placeholder' => 'Assign a Teaching Staff Member']) !!}
</div>