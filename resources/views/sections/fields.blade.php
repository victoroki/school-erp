<!-- Class Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('class', 'Class Id:') !!}
    {!! Form::select('class_id', $classes, null, ['class' => 'form-control', 'placeholder' => 'Select Class']) !!}
</div>

<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', 'Capacity:') !!}
    {!! Form::number('capacity', null, ['class' => 'form-control']) !!}
</div>