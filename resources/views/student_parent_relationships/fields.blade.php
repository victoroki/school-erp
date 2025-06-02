<!-- Student Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student Id:') !!}
    {!! Form::number('student_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Parent Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('parent_id', 'Parent Id:') !!}
    {!! Form::number('parent_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Is Primary Contact Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_primary_contact', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_primary_contact', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_primary_contact', 'Is Primary Contact', ['class' => 'form-check-label']) !!}
    </div>
</div>