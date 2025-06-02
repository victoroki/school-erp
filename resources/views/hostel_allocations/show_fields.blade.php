<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student Id:') !!}
    <p>{{ $hostelAllocation->student_id }}</p>
</div>

<!-- Hostel Id Field -->
<div class="col-sm-12">
    {!! Form::label('hostel_id', 'Hostel Id:') !!}
    <p>{{ $hostelAllocation->hostel_id }}</p>
</div>

<!-- Room Id Field -->
<div class="col-sm-12">
    {!! Form::label('room_id', 'Room Id:') !!}
    <p>{{ $hostelAllocation->room_id }}</p>
</div>

<!-- Bed Number Field -->
<div class="col-sm-12">
    {!! Form::label('bed_number', 'Bed Number:') !!}
    <p>{{ $hostelAllocation->bed_number }}</p>
</div>

<!-- Allocation Date Field -->
<div class="col-sm-12">
    {!! Form::label('allocation_date', 'Allocation Date:') !!}
    <p>{{ $hostelAllocation->allocation_date }}</p>
</div>

<!-- Vacating Date Field -->
<div class="col-sm-12">
    {!! Form::label('vacating_date', 'Vacating Date:') !!}
    <p>{{ $hostelAllocation->vacating_date }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $hostelAllocation->status }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year Id:') !!}
    <p>{{ $hostelAllocation->academic_year_id }}</p>
</div>

