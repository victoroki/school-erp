<!-- Room Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_number', 'Room Number:') !!}
    {!! Form::text('room_number', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Building Field -->
<div class="form-group col-sm-6">
    {!! Form::label('building', 'Building:') !!}
    {!! Form::text('building', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Floor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('floor', 'Floor:') !!}
    {!! Form::number('floor', null, ['class' => 'form-control']) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', 'Capacity:') !!}
    {!! Form::number('capacity', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Has Sockets Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('has_sockets', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('has_sockets', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('has_sockets', 'Has Sockets', ['class' => 'form-check-label']) !!}
    </div>
</div>

<!-- Has Whiteboard Field -->
<div class="form-group col-sm-6">
    <div class="form-check">
        {!! Form::hidden('has_whiteboard', 0, ['class' => 'form-check-input']) !!}
        {!! Form::checkbox('has_whiteboard', '1', null, ['class' => 'form-check-input']) !!}
        {!! Form::label('has_whiteboard', 'Has Whiteboard', ['class' => 'form-check-label']) !!}
    </div>
</div>