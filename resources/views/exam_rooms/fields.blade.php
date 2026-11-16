<div class="row">
    <div class="form-group col-sm-6">
        {!! Form::label('room_no', 'Room Number:', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('room_no', null, ['class' => 'form-control', 'required', 'placeholder' => 'e.g. RM-101']) !!}
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('name', 'Room Name (Optional):', ['class' => 'font-weight-bold']) !!}
        {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'e.g. Science Lab 1']) !!}
    </div>

    <div class="form-group col-sm-6">
        {!! Form::label('capacity', 'Seating Capacity:', ['class' => 'font-weight-bold']) !!}
        {!! Form::number('capacity', null, ['class' => 'form-control', 'required', 'min' => 1]) !!}
    </div>

    <div class="form-group col-sm-12">
        {!! Form::label('description', 'Description:', ['class' => 'font-weight-bold']) !!}
        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
    </div>

    <div class="form-group col-sm-12">
        <div class="custom-control custom-switch pr-2">
            {!! Form::hidden('status', 0) !!}
            {!! Form::checkbox('status', 1, null, ['class' => 'custom-control-input', 'id' => 'roomStatus']) !!}
            {!! Form::label('roomStatus', 'Active for use', ['class' => 'custom-control-label font-weight-bold']) !!}
        </div>
    </div>
</div>
