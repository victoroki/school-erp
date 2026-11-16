<!-- Room Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_number', '<span class="text-danger">*</span> Room Number:', [], false) !!}
    {!! Form::text('room_number', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'e.g. R-101']) !!}
    <small class="text-muted d-block mt-1">Unique physical identifier used on timetables.</small>
    @error('room_number')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<!-- Building Field -->
<div class="form-group col-sm-6">
    {!! Form::label('building', 'Building / Block:') !!}
    {!! Form::text('building', null, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'e.g. Block A']) !!}
    @error('building')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<!-- Floor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('floor', 'Floor:') !!}
    {!! Form::number('floor', null, ['class' => 'form-control', 'placeholder' => 'e.g. 1']) !!}
    @error('floor')<span class="text-danger">{{ $message }}</span>@enderror
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', '<span class="text-danger">*</span> Capacity:', [], false) !!}
    {!! Form::number('capacity', null, ['class' => 'form-control', 'required', 'min' => 1, 'placeholder' => 'e.g. 40']) !!}
    <small class="text-muted d-block mt-1">Number of learners the room can hold — used when allocating exam rooms.</small>
    @error('capacity')<span class="text-danger">{{ $message }}</span>@enderror
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