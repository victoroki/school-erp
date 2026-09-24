<!-- Class Id Field -->
<div class="col-sm-12">
    {!! Form::label('class_id', 'Class:') !!}
    <p>{{ $section->schoolClass->name ?? 'N/A' }}</p>
</div>

<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $section->name }}</p>
</div>

<!-- Capacity Field -->
<div class="col-sm-12">
    {!! Form::label('capacity', 'Capacity:') !!}
    <p>{{ $section->capacity }}</p>
</div>

