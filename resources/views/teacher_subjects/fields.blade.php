<!-- Staff Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('staff_id', 'Staff:') !!}
    {!! Form::select('staff_id', $staffList, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Subject Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject:') !!}
    {!! Form::select('subject_id', $subjectList, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Class Section Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class_section_id', 'Class Section:') !!}
    {!! Form::select('class_section_id', $classSectionList, null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', $academicYearList, null, ['class' => 'form-control', 'required']) !!}
</div>
