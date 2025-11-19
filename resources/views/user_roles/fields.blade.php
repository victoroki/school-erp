<!-- User Field -->
<div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User :') !!}
    {!! Form::select('user_id', $users->pluck('name','id'), isset($userRole)?$userRole->user_id:null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Role Field -->
<div class="form-group col-sm-6">
    {!! Form::label('role_id', 'Role :') !!}
    {!! Form::select('role_id', $roles->pluck('role_name','role_id'), isset($userRole)?$userRole->role_id:null, ['class' => 'form-control', 'required']) !!}
</div>