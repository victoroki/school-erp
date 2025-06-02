<!-- Hostel Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('hostel_id', 'Hostel Id:') !!}
    {!! Form::number('hostel_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Room Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_number', 'Room Number:') !!}
    {!! Form::text('room_number', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Room Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_type', 'Room Type:') !!}
    {!! Form::text('room_type', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', 'Capacity:') !!}
    {!! Form::number('capacity', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Occupied Field -->
<div class="form-group col-sm-6">
    {!! Form::label('occupied', 'Occupied:') !!}
    {!! Form::number('occupied', null, ['class' => 'form-control']) !!}
</div>

<!-- Floor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('floor', 'Floor:') !!}
    {!! Form::text('floor', null, ['class' => 'form-control', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>