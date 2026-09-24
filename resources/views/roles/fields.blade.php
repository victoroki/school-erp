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
@php
    /*
     * Pre-tick the role's saved permissions and preserve the submitted
     * selection after a failed validation.
     *
     * $role->permissions is a collection of Permission *models*, so comparing
     * it against a scalar id always returned false. The edit form therefore
     * rendered fully unchecked, and saving posted nothing — which the
     * controller interpreted as "remove every permission".
     */
    $assignedPermissionIds = collect(
        old('permissions', isset($role) ? $role->permissions->pluck('permission_id')->all() : [])
    )->map(fn ($id) => (int) $id)->all();
@endphp

{{-- Marks that this form submitted the permission checkboxes, so an empty
     selection is distinguishable from a request that never carried them. --}}
<input type="hidden" name="permissions_submitted" value="1">

<div class="form-group col-sm-12">
    <h3>Permissions</h3>
    <div class="row">
        @foreach($permissions as $permission)
            <div class="col-md-3">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('permissions[]', $permission->permission_id, in_array((int) $permission->permission_id, $assignedPermissionIds, true)) !!}
                        {{ $permission->permission_name }}
                    </label>
                </div>
            </div>
        @endforeach
    </div>
</div>