<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $department->name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $department->description }}</p>
</div>

<!-- Hod Id Field -->
<div class="col-sm-12">
    {!! Form::label('hod_id', 'Hod Id:') !!}
    <p>{{ $department->hod_id }}</p>
</div>

