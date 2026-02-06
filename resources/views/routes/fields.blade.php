<div class="row">
    <div class="col-md-12">
        <h5 class="text-danger mb-3 border-bottom pb-2"><i class="fas fa-route mr-2"></i> Route Information</h5>
    </div>
    
    <!-- Name Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Route Name:') !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'maxlength' => 100, 'placeholder' => 'e.g. Westlands - Parklands']) !!}
    </div>

    <!-- Route Code Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('route_code', 'Route Code:') !!}
        {!! Form::text('route_code', null, ['class' => 'form-control', 'maxlength' => 20, 'placeholder' => 'e.g. RT-001']) !!}
    </div>

    <!-- Start Point Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('start_point', 'Start Point:') !!}
        {!! Form::text('start_point', null, ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
    </div>

    <!-- End Point Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('end_point', 'End Point:') !!}
        {!! Form::text('end_point', null, ['class' => 'form-control', 'required', 'maxlength' => 100]) !!}
    </div>

    <!-- Distance Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('distance', 'Est. Distance (km):') !!}
        {!! Form::number('distance', null, ['class' => 'form-control', 'step' => '0.1']) !!}
    </div>

    <div class="col-md-12 mt-4">
        <h5 class="text-danger mb-3 border-bottom pb-2"><i class="fas fa-bus mr-2"></i> Vehicle & Staff Information</h5>
    </div>

    <!-- Vehicle Name Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('vehicle_name', 'Bus/Vehicle Name:') !!}
        {!! Form::text('vehicle_name', null, ['class' => 'form-control', 'placeholder' => 'e.g. Blue Bus A']) !!}
    </div>

    <!-- Vehicle Number Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('vehicle_number', 'Vehicle Number Plate:') !!}
        {!! Form::text('vehicle_number', null, ['class' => 'form-control', 'placeholder' => 'KAA 123X']) !!}
    </div>

    <!-- Vehicle Capacity Field -->
    <div class="form-group col-sm-4">
        {!! Form::label('vehicle_capacity', 'Seating Capacity:') !!}
        {!! Form::number('vehicle_capacity', null, ['class' => 'form-control', 'min' => 0]) !!}
    </div>

    <!-- Driver Name Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('driver_name', 'Driver Name:') !!}
        {!! Form::text('driver_name', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Driver Contact Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('driver_contact', 'Driver Phone:') !!}
        {!! Form::text('driver_contact', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Conductor Name Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('conductor_name', 'Conductor Name:') !!}
        {!! Form::text('conductor_name', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Conductor Contact Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('conductor_contact', 'Conductor Phone:') !!}
        {!! Form::text('conductor_contact', null, ['class' => 'form-control']) !!}
    </div>

    <div class="col-md-12 mt-4">
        <h5 class="text-danger mb-3 border-bottom pb-2"><i class="fas fa-clock mr-2"></i> Schedule & Fees</h5>
    </div>

    <!-- Morning Start Time Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('morning_start_time', 'AM Start Time:') !!}
        {!! Form::time('morning_start_time', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Morning End Time Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('morning_end_time', 'AM End Time:') !!}
        {!! Form::time('morning_end_time', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Evening Start Time Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('evening_start_time', 'PM Start Time:') !!}
        {!! Form::time('evening_start_time', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Evening End Time Field -->
    <div class="form-group col-sm-3">
        {!! Form::label('evening_end_time', 'PM End Time:') !!}
        {!! Form::time('evening_end_time', null, ['class' => 'form-control']) !!}
    </div>

    <!-- Route Fee Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('route_fee', 'Base Route Fee:') !!}
        {!! Form::number('route_fee', null, ['class' => 'form-control', 'step' => '0.01']) !!}
    </div>

    <!-- Status Field -->
    <div class="form-group col-sm-6">
        {!! Form::label('status', 'Route Status:') !!}
        {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Under Maintenance'], null, ['class' => 'form-control']) !!}
    </div>

    <!-- Description Field -->
    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Additional Notes:') !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 2]) !!}
    </div>
</div>