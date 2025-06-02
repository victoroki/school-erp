<!-- Room Number Field -->
<div class="col-sm-12">
    {!! Form::label('room_number', 'Room Number:') !!}
    <p>{{ $classroom->room_number }}</p>
</div>

<!-- Building Field -->
<div class="col-sm-12">
    {!! Form::label('building', 'Building:') !!}
    <p>{{ $classroom->building }}</p>
</div>

<!-- Floor Field -->
<div class="col-sm-12">
    {!! Form::label('floor', 'Floor:') !!}
    <p>{{ $classroom->floor }}</p>
</div>

<!-- Capacity Field -->
<div class="col-sm-12">
    {!! Form::label('capacity', 'Capacity:') !!}
    <p>{{ $classroom->capacity }}</p>
</div>

<!-- Has Sockets Field -->
<div class="col-sm-12">
    {!! Form::label('has_sockets', 'Has Sockets:') !!}
    <p>{{ $classroom->has_sockets }}</p>
</div>

<!-- Has Whiteboard Field -->
<div class="col-sm-12">
    {!! Form::label('has_whiteboard', 'Has Whiteboard:') !!}
    <p>{{ $classroom->has_whiteboard }}</p>
</div>

