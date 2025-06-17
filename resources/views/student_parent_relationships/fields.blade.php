<!-- Student Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', $students, null, ['class' => 'form-control', 'placeholder' => 'Select Student']) !!}
</div>

<!-- Parent Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('parent_id', 'Parent:') !!}
    {!! Form::select('parent_id', $parents, null, ['class' => 'form-control', 'placeholder' => 'Select Parent']) !!}
</div>

<!-- Is Primary Contact Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_primary_contact', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_primary_contact', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_primary_contact', 'Is Primary Contact', ['class' => 'form-check-label']) !!}
    </div>
</div>