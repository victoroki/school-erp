<!-- Exam Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('exam_id', 'Exam Id:') !!}
    {!! Form::number('exam_id', null, ['class' => 'form-control']) !!}
</div>

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

<!-- Subject Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_id', 'Subject Id:') !!}
    {!! Form::number('subject_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Marks Obtained Field -->
<div class="form-group col-sm-6">
    {!! Form::label('marks_obtained', 'Marks Obtained:') !!}
    {!! Form::number('marks_obtained', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Grade Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('grade_id', 'Grade Id:') !!}
    {!! Form::number('grade_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Remarks Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('remarks', 'Remarks:') !!}
    {!! Form::textarea('remarks', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>

<!-- Created By Field -->
<div class="form-group col-sm-6">
    {!! Form::label('created_by', 'Created By:') !!}
    {!! Form::number('created_by', null, ['class' => 'form-control']) !!}
</div>