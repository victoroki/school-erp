<!-- User Id Field -->
<!-- <div class="form-group col-sm-6">
    {!! Form::label('user_id', 'User Id:') !!}
    {!! Form::number('user_id', null, ['class' => 'form-control']) !!}
</div> -->

<!-- First Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('first_name', 'First Name:') !!}
    {!! Form::text('first_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Last Name Field -->
<div class="form-group col-sm-6">
    {!! Form::label('last_name', 'Last Name:') !!}
    {!! Form::text('last_name', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Relationship Field -->
<div class="form-group col-sm-6">
    {!! Form::label('relationship', 'Relationship:') !!}
    {!! Form::select('relationship', ['Father', 'Mother', 'Guardian'], null, ['class' => 'form-control', 'placeholder' => 'Select Relationship', 'required']) !!}
</div>

<!-- Email Field -->
<div class="form-group col-sm-6">
    {!! Form::label('email', 'Email:') !!}
    {!! Form::email('email', null, ['class' => 'form-control', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>

<!-- Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('phone', 'Phone:') !!} <span class="text-danger">*</span>
    {!! Form::text('phone', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'type' => 'tel', 'placeholder' => 'e.g. 0712 345 678']) !!}
    <small class="form-text text-muted">Kenyan mobile number — used for SMS alerts.</small>
</div>

<!-- Alternate Phone Field -->
<div class="form-group col-sm-6">
    {!! Form::label('alternate_phone', 'Alternate Phone:') !!}
    {!! Form::text('alternate_phone', null, ['class' => 'form-control', 'maxlength' => 20, 'type' => 'tel', 'placeholder' => 'e.g. 0733 111 222']) !!}
    <small class="form-text text-muted">Optional backup number for SMS alerts.</small>
</div>

<!-- Occupation Field -->
<div class="form-group col-sm-6">
    {!! Form::label('occupation', 'Occupation:') !!}
    {!! Form::text('occupation', null, ['class' => 'form-control', 'maxlength' => 100, 'maxlength' => 100]) !!}
</div>