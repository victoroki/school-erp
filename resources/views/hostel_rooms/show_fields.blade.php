<!-- Hostel Id Field -->
<div class="col-sm-12">
    {!! Form::label('hostel_id', 'Hostel Id:') !!}
    <p>{{ $hostelRoom->hostel_id }}</p>
</div>

<!-- Room Number Field -->
<div class="col-sm-12">
    {!! Form::label('room_number', 'Room Number:') !!}
    <p>{{ $hostelRoom->room_number }}</p>
</div>

<!-- Room Type Field -->
<div class="col-sm-12">
    {!! Form::label('room_type', 'Room Type:') !!}
    <p>{{ $hostelRoom->room_type }}</p>
</div>

<!-- Capacity Field -->
<div class="col-sm-12">
    {!! Form::label('capacity', 'Capacity:') !!}
    <p>{{ $hostelRoom->capacity }}</p>
</div>

<!-- Occupied Field -->
<div class="col-sm-12">
    {!! Form::label('occupied', 'Occupied:') !!}
    <p>{{ $hostelRoom->occupied }}</p>
</div>

<!-- Floor Field -->
<div class="col-sm-12">
    {!! Form::label('floor', 'Floor:') !!}
    <p>{{ $hostelRoom->floor }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $hostelRoom->status }}</p>
</div>

