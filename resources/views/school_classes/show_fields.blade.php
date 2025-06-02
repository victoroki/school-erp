<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $schoolClass->name }}</p>
</div>

<!-- Numeric Value Field -->
<div class="col-sm-12">
    {!! Form::label('numeric_value', 'Numeric Value:') !!}
    <p>{{ $schoolClass->numeric_value }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $schoolClass->description }}</p>
</div>

