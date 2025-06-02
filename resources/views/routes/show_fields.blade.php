<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $route->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $route->description }}</p>
</div>

<!-- Start Point Field -->
<div class="col-sm-12">
    {!! Form::label('start_point', 'Start Point:') !!}
    <p>{{ $route->start_point }}</p>
</div>

<!-- End Point Field -->
<div class="col-sm-12">
    {!! Form::label('end_point', 'End Point:') !!}
    <p>{{ $route->end_point }}</p>
</div>

<!-- Distance Field -->
<div class="col-sm-12">
    {!! Form::label('distance', 'Distance:') !!}
    <p>{{ $route->distance }}</p>
</div>

