<!-- Vehicle Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('vehicle_number', 'Vehicle Number:') !!}
    {!! Form::text('vehicle_number', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'maxlength' => 20]) !!}
</div>

<!-- Vehicle Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('vehicle_type', 'Vehicle Type:') !!}
    {!! Form::text('vehicle_type', null, ['class' => 'form-control', 'required', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Model Field -->
<div class="form-group col-sm-6">
    {!! Form::label('model', 'Model:') !!}
    {!! Form::text('model', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Make Field -->
<div class="form-group col-sm-6">
    {!! Form::label('make', 'Make:') !!}
    {!! Form::text('make', null, ['class' => 'form-control', 'maxlength' => 50, 'maxlength' => 50]) !!}
</div>

<!-- Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('year', 'Year:') !!}
    {!! Form::number('year', null, ['class' => 'form-control']) !!}
</div>

<!-- Seating Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('seating_capacity', 'Seating Capacity:') !!}
    {!! Form::number('seating_capacity', null, ['class' => 'form-control', 'required']) !!}
</div>

<!-- Driver Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('driver_id', 'Driver:') !!}
    {!! Form::select('driver_id', $drivers ?? [], null, ['class' => 'form-control', 'placeholder' => 'Select Driver']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', ['active' => 'Active', 'maintenance' => 'Maintenance', 'inactive' => 'Inactive'], null, ['class' => 'form-control']) !!}
</div>

<!-- Insurance Expiry Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('insurance_expiry_date', 'Insurance Expiry Date:') !!}
    {!! Form::date('insurance_expiry_date', null, ['class' => 'form-control']) !!}
</div>