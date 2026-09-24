<!-- Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('name', 'Name:') !!}
    {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'required', 'maxlength' => 255]) !!}
</div>

<!-- Password Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password', 'Password:') !!}
    {!! Form::password('password', ['class' => 'form-control', 'minlength' => 8]) !!}
    @if(isset($user)) 
        <small class="text-muted">Leave blank to keep current password</small>
    @endif
</div>

<!-- Password Confirmation Field -->
<div class="form-group col-sm-6">
    {!! Form::label('password_confirmation', 'Confirm Password:') !!}
    {!! Form::password('password_confirmation', ['class' => 'form-control', 'minlength' => 8]) !!}
</div>

<!-- Roles Field -->
@php
    /*
     * Pre-tick the user's saved roles and preserve the submitted selection
     * after a failed validation.
     *
     * $user->roles is a collection of Role *models*, so comparing it against a
     * scalar id always returned false. The edit form therefore rendered fully
     * unchecked, and saving posted nothing — which stripped every role the
     * user held.
     */
    $assignedRoleIds = collect(
        old('roles', isset($user) ? $user->roles->pluck('role_id')->all() : [])
    )->map(fn ($id) => (int) $id)->all();
@endphp

{{-- Marks that this form submitted the role checkboxes, so an empty selection
     is distinguishable from a request that never carried them. --}}
<input type="hidden" name="roles_submitted" value="1">

<div class="form-group col-sm-12">
    <h3>Roles</h3>
    <div class="row">
        @foreach($roles as $role)
            @if($role->role_name !== 'Owner')
            <div class="col-md-3">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('roles[]', $role->role_id, in_array((int) $role->role_id, $assignedRoleIds, true)) !!}
                        {{ $role->role_name }}
                    </label>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
