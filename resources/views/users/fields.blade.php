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
<div class="form-group col-sm-12">
    <h3>Roles</h3>
    <div class="row">
        @foreach($roles as $role)
            @if($role->role_name !== 'Owner')
            <div class="col-md-3">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('roles[]', $role->role_id, isset($user) && $user->roles->contains($role->role_id)) !!}
                        {{ $role->role_name }}
                    </label>
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>
