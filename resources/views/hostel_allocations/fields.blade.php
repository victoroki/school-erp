<!-- Student Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student :') !!}
    {!! Form::select('student_id', $students, null, ['class' => 'form-control', 'placeholder' => 'Select Student']) !!}
</div>

<!-- Hostel Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('hostel_id', 'Hostel Id:') !!}
    {!! Form::select('hostel_id', $hostels, null, ['class' => 'form-control', 'placeholder' => 'Select Hostel']) !!}
</div>

<!-- Room Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_id', 'Room Id:') !!}
    {!! Form::select('room_id', $room, null, ['class' => 'form-control', 'placeholder' => 'Select Room']) !!}
</div>

<!-- Bed Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('bed_number', 'Bed Number:') !!}
    {!! Form::number('bed_number', null, ['class' => 'form-control']) !!}
</div>

<!-- Allocation Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('allocation_date', 'Allocation Date:') !!}
    {!! Form::text('allocation_date', null, ['class' => 'form-control','id'=>'allocation_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#allocation_date').datepicker()
    </script>
@endpush

<!-- Vacating Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('vacating_date', 'Vacating Date:') !!}
    {!! Form::text('vacating_date', null, ['class' => 'form-control','id'=>'vacating_date']) !!}
</div>

@push('page_scripts')
    <script type="text/javascript">
        $('#vacating_date').datepicker()
    </script>
@endpush

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::text('status', null, ['class' => 'form-control']) !!}
</div>

<!-- Academic Year Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    {!! Form::select('academic_year_id', $academicYear, null, ['class' => 'form-control', 'placeholder' => 'Select Academic Year']) !!}
</div>