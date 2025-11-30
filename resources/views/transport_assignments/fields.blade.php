<!-- Route Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('route_id', 'Route:') !!}
    {!! Form::select('route_id', $routes ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Route']) !!}
</div>

<!-- Vehicle Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('vehicle_id', 'Vehicle:') !!}
    {!! Form::select('vehicle_id', $vehicles ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Vehicle']) !!}
</div>

<!-- Driver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', 'Driver:') !!}
    {!! Form::select('driver_id', $drivers ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Driver']) !!}
</div>

<!-- Assistant Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('assistant_id', 'Assistant:') !!}
    {!! Form::select('assistant_id', $assistants ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Assistant']) !!}
</div>

<!-- Departure Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('departure_time', 'Departure Time:') !!}
    {!! Form::time('departure_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Return Time Field -->
<div class="form-group col-sm-6">
    {!! Form::label('return_time', 'Return Time:') !!}
    {!! Form::time('return_time', null, ['class' => 'form-control']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive'], null, ['class' => 'form-control']) !!}
</div>