<!-- Role Field -->
<div class="form-group col-sm-6">
    {!! Form::label('role_id', 'Role :') !!}
    {!! Form::select('role_id', $roles->pluck('role_name','role_id'), isset($rolePermission)?$rolePermission->role_id:null, ['class' => 'form-control', 'required']) !!}
    </div>

<!-- Permission Field -->
<div class="form-group col-sm-6">
    {!! Form::label('permission_id', 'Permission :') !!}
    {!! Form::select('permission_id', $permissions->pluck('permission_name','permission_id'), isset($rolePermission)?$rolePermission->permission_id:null, ['class' => 'form-control', 'required']) !!}
</div>