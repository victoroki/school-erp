<!-- Route Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('route_id', 'Route Id:') !!}
    {!! Form::number('route_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Stop Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stop_name', 'Stop Name:') !!}
    {!! Form::text('stop_name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Stop Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('stop_time', 'Stop Time:') !!}
    {!! Form::text('stop_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Sequence Field -->
<div class="form-group col-sm-6">
    {!! Form::label('sequence', 'Sequence:') !!}
    {!! Form::number('sequence', null, ['class' => 'form-control', 'required']) !!}
</div>