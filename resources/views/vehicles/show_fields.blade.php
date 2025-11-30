<!-- Vehicle Number Field -->
<div class="col-sm-12">
    {!! Form::label('vehicle_number', 'Vehicle Number:') !!}
    <p>{{ $vehicle->vehicle_number }}</p>
</div>

<!-- Vehicle Type Field -->
<div class="col-sm-12">
    {!! Form::label('vehicle_type', 'Vehicle Type:') !!}
    <p>{{ $vehicle->vehicle_type }}</p>
</div>

<!-- Model Field -->
<div class="col-sm-12">
    {!! Form::label('model', 'Model:') !!}
    <p>{{ $vehicle->model }}</p>
</div>

<!-- Make Field -->
<div class="col-sm-12">
    {!! Form::label('make', 'Make:') !!}
    <p>{{ $vehicle->make }}</p>
</div>

<!-- Year Field -->
<div class="col-sm-12">
    {!! Form::label('year', 'Year:') !!}
    <p>{{ $vehicle->year }}</p>
</div>

<!-- Seating Capacity Field -->
<div class="col-sm-12">
    {!! Form::label('seating_capacity', 'Seating Capacity:') !!}
    <p>{{ $vehicle->seating_capacity }}</p>
</div>

<!-- Driver Id Field -->
<div class="col-sm-12">
    {!! Form::label('driver_id', 'Driver:') !!}
    <p>{{ $vehicle->driver->name ?? 'Not Assigned' }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $vehicle->status }}</p>
</div>

<!-- Insurance Expiry Date Field -->
<div class="col-sm-12">
    {!! Form::label('insurance_expiry_date', 'Insurance Expiry Date:') !!}
    <p>{{ $vehicle->insurance_expiry_date }}</p>
</div>