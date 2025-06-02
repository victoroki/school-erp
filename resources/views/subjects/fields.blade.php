<!-- Subject Code Field -->
<div class="form-group col-sm-6">
    {!! Form::label('subject_code', 'Subject Code:') !!}
    {!! Form::text('subject_code', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'maxlength' => 65535]) !!}
</div>

<!-- Is Elective Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('is_elective', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('is_elective', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('is_elective', 'Is Elective', ['class' => 'form-check-label']) !!}
    </div>
</div>