<!-- Route Id Field -->
<div class="col-sm-12">
    {!! Form::label('route_id', 'Route :') !!}
    <p>{{ $routeStop->route_id }}</p>
</div>

<!-- Stop Name Field -->
<div class="col-sm-12">
    {!! Form::label('stop_name', 'Stop Name:') !!}
    <p>{{ $routeStop->stop_name }}</p>
</div>

<!-- Stop Time Field -->
<div class="col-sm-12">
    {!! Form::label('stop_time', 'Stop Time:') !!}
    <p>{{ $routeStop->stop_time }}</p>
</div>

<!-- Sequence Field -->
<div class="col-sm-12">
    {!! Form::label('sequence', 'Sequence:') !!}
    <p>{{ $routeStop->sequence }}</p>
</div>

