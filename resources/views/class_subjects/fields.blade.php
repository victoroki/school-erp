<!-- Class Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_id', 'Class :') !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control', 'placeholder' => 'Select Class']) !!}
</div>

<!-- Subject Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject :') !!}
    {!! Form::select('subject_id', $subjects, null, ['class' => 'form-control', 'placeholder' => 'Select Subject']) !!}
</div>

<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year :') !!}
    {!! Form::select('academic_year_id', $academicYear, null, ['class' => 'form-control', 'placeholder'=> 'Select Academic Year']) !!}
</div>