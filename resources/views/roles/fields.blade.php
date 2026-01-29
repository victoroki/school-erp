<!-- Role Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('role_name', 'Role Name:') !!}
    {!! Form::text('role_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50]) !!}
</div>

<!-- Description Field -->
<div class="form-group col-sm-12 col-lg-12">
    {!! Form::label('description', 'Description:') !!}
    {!! Form::textarea('description', null, ['class' => 'form-control', 'maxlength' => 65535, 'rows' => 3]) !!}
</div>

<!-- Permissions Field -->
<div class="form-group col-sm-12">
    <h3>Permissions</h3>
    <div class="row">
        @foreach($permissions as $permission)
            <div class="col-md-3">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('permissions[]', $permission->permission_id, isset($role) && $role->permissions->contains($permission->permission_id)) !!}
                        {{ $permission->permission_name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>