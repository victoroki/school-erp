<!-- Route Id Field -->
<div class="col-sm-12">
    {!! Form::label('route_id', 'Route:') !!}
    <p>{{ $transportAssignment->route->name ?? 'N/A' }}</p>
</div>

<!-- Vehicle Id Field -->
<div class="col-sm-12">
    {!! Form::label('vehicle_id', 'Vehicle:') !!}
    <p>{{ $transportAssignment->vehicle->vehicle_number ?? 'N/A' }}</p>
</div>

<!-- Driver Id Field -->
<div class="col-sm-12">
    {!! Form::label('driver_id', 'Driver:') !!}
    <p>{{ $transportAssignment->driver->name ?? 'N/A' }}</p>
</div>

<!-- Assistant Id Field -->
<div class="col-sm-12">
    {!! Form::label('assistant_id', 'Assistant:') !!}
    <p>{{ $transportAssignment->assistant->name ?? 'N/A' }}</p>
</div>

<!-- Departure Time Field -->
<div class="col-sm-12">
    {!! Form::label('departure_time', 'Departure Time:') !!}
    <p>{{ $transportAssignment->departure_time }}</p>
</div>

<!-- Return Time Field -->
<div class="col-sm-12">
    {!! Form::label('return_time', 'Return Time:') !!}
    <p>{{ $transportAssignment->return_time }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $transportAssignment->status }}</p>
</div>