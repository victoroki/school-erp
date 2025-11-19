<!-- Role & Permission -->
<div class="col-sm-12">
    {!! Form::label('permission_id', 'Role & Permission:') !!}
    <p>{{ $rolePermission->role->role_name }} - {{ $rolePermission->permission->permission_name }}</p>
</div>

