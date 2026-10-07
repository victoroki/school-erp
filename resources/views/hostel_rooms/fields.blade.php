<!-- Hostel Field -->
<div class="form-group col-sm-6">
    {!! Form::label('hostel_id', 'Hostel:') !!}
    {!! Form::select('hostel_id', $hostels, null, ['class' => 'form-control select2', 'placeholder' => 'Select Hostel', 'required', 'style' => 'width: 100%']) !!}
</div>

<!-- Room Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_number', 'Room Number/Name:') !!}
    {!! Form::text('room_number', null, ['class' => 'form-control', 'required', 'maxlength' => 20, 'placeholder' => 'e.g. A-12']) !!}
</div>

<!-- Room Type Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_type', 'Room Type:') !!}
    {!! Form::select('room_type', [
        'single' => 'Single (1 bed)',
        'double' => 'Double (2 beds)',
        'triple' => 'Triple (3 beds)',
        'dormitory' => 'Dormitory (4+ beds)',
    ], null, ['class' => 'form-control select2', 'required', 'style' => 'width: 100%']) !!}
</div>

<!-- Capacity Field -->
<div class="form-group col-sm-6">
    {!! Form::label('capacity', 'Bed Capacity:') !!}
    {!! Form::number('capacity', null, ['class' => 'form-control', 'required', 'min' => 1]) !!}
    <small class="form-text text-muted">
        Beds are numbered 1 to this capacity. A room cannot be reduced below the students already in it.
    </small>
</div>

<!-- Floor Field -->
<div class="form-group col-sm-6">
    {!! Form::label('floor', 'Floor:') !!}
    {!! Form::text('floor', null, ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'e.g. Ground, 1st Floor']) !!}
</div>

<!-- Status Field -->
{{-- The room's occupancy is never typed in: it is counted from the allocations.
     "Full" is therefore a derived state and is not offered here. --}}
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Condition:') !!}
    {!! Form::select('status', [
        \App\Models\HostelRoom::STATUS_AVAILABLE => 'In service — beds allocated as they are filled',
        \App\Models\HostelRoom::STATUS_UNDER_MAINTENANCE => 'Under maintenance — cannot take new students',
    ], null, ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
    <small class="form-text text-muted">Status becomes <strong>Full</strong> automatically once every bed is taken.</small>
</div>

<!-- Maintenance Notes Field -->
<div class="form-group col-sm-12">
    {!! Form::label('maintenance_notes', 'Maintenance Notes / Description:') !!}
    {!! Form::textarea('maintenance_notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Any issues with the room or amenities...']) !!}
</div>
