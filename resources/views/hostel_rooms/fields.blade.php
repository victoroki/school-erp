<!-- Hostel Field -->
<div class="form-group col-sm-6">
    {!! Form::label('hostel_id', 'Hostel:') !!}
    {!! Form::select('hostel_id', $hostels, null, ['class' => 'form-control select2', 'placeholder' => 'Select Hostel', 'required']) !!}
</div>

<!-- Room Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_number', 'Room Number/Name:') !!}
    {!! Form::text('room_number', null, ['class' => 'form-control', 'required', 'maxlength' => 20]) !!}
</div>

<!-- Room Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_type', 'Room Type:') !!}
    {!! Form::select('room_type', [
        'single' => 'Single (1 Bed)',
        'double' => 'Double (2 Beds)',
        'triple' => 'Triple (3 Beds)',
        'dormitory' => 'Dormitory (4+ Beds)'
    ], null, ['class' => 'form-control select2', 'required']) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', 'Bed Capacity:') !!}
    {!! Form::number('capacity', null, ['class' => 'form-control', 'required', 'min' => 1]) !!}
</div>

<!-- Floor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('floor', 'Floor:') !!}
    {!! Form::text('floor', null, ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'e.g. Ground, 1st Floor']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', [
        'available' => 'Available',
        'under_maintenance' => 'Under Maintenance',
        'full' => 'Full (Read-only status)',
        'partial' => 'Partial (Read-only status)'
    ], null, ['class' => 'form-control select2']) !!}
    <small class="text-muted">Status is usually auto-managed based on occupancy.</small>
</div>

<!-- Maintenance Notes Field -->
<div class="form-group col-sm-12">
    {!! Form::label('maintenance_notes', 'Maintenance Notes / Description:') !!}
    {!! Form::textarea('maintenance_notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Any issues with the room or amenities...']) !!}
</div>