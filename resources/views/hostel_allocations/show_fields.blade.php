<!-- Student Id Field -->
<div class="col-sm-12">
    {!! Form::label('student_id', 'Student:') !!}
    <p>{{ $hostelAllocation->student->full_name ?? 'N/A' }}{{ isset($hostelAllocation->student->admission_no) ? ' ('.$hostelAllocation->student->admission_no.')' : '' }}</p>
</div>

<!-- Hostel Id Field -->
<div class="col-sm-12">
    {!! Form::label('hostel_id', 'Hostel:') !!}
    <p>{{ $hostelAllocation->hostel->name ?? 'N/A' }}</p>
</div>

<!-- Room Id Field -->
<div class="col-sm-12">
    {!! Form::label('room_id', 'Room:') !!}
    <p>{{ $hostelAllocation->room->room_number ?? 'N/A' }}</p>
</div>

<!-- Bed Number Field -->
<div class="col-sm-12">
    {!! Form::label('bed_number', 'Bed Number:') !!}
    <p>{{ $hostelAllocation->bed_number }}</p>
</div>

<!-- Allocation Date Field -->
<div class="col-sm-12">
    {!! Form::label('allocation_date', 'Allocation Date:') !!}
    <p>{{ $hostelAllocation->allocation_date ? \Carbon\Carbon::parse($hostelAllocation->allocation_date)->format('d/m/Y') : '—' }}</p>
</div>

<!-- Vacating Date Field -->
<div class="col-sm-12">
    {!! Form::label('vacating_date', 'Vacating Date:') !!}
    <p>{{ $hostelAllocation->vacating_date ? \Carbon\Carbon::parse($hostelAllocation->vacating_date)->format('d/m/Y') : 'Not vacated' }}</p>
</div>

<!-- Status Field -->
<div class="col-sm-12">
    {!! Form::label('status', 'Status:') !!}
    <p>{{ $hostelAllocation->status }}</p>
</div>

<!-- Academic Year Id Field -->
<div class="col-sm-12">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    <p>{{ $hostelAllocation->academicYear->name ?? 'N/A' }}</p>
</div>

