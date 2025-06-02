<!-- Permission Name Field -->
<div class="col-sm-12">
    {!! Form::label('permission_name', 'Permission Name:') !!}
    <p>{{ $permission->permission_name }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $permission->description }}</p>
</div>

