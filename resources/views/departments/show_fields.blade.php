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
    {!! Form::label('hod_id', 'Head of Department:') !!}
    <p>{{ $department->hod ? $department->hod->full_name : 'Not assigned' }}</p>
</div>

