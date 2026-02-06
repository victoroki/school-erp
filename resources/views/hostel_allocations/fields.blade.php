<!-- Student Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', $students, null, ['class' => 'form-control select2', 'placeholder' => 'Select Student', 'required']) !!}
</div>

<!-- Hostel Field -->
<div class="form-group col-sm-6">
    {!! Form::label('hostel_id', 'Hostel:') !!}
    {!! Form::select('hostel_id', $hostels, null, ['class' => 'form-control select2', 'placeholder' => 'Select Hostel', 'required']) !!}
</div>

<!-- Room Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_id', 'Room:') !!}
    {!! Form::select('room_id', $rooms, null, ['class' => 'form-control select2', 'placeholder' => 'Select Room', 'required']) !!}
</div>

<!-- Bed Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('bed_number', 'Bed Number/Name:') !!}
    {!! Form::text('bed_number', null, ['class' => 'form-control', 'placeholder' => 'e.g. Bed 1, A, etc.']) !!}
</div>

<!-- Allocation Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('allocation_date', 'Allocation Date:') !!}
    {!! Form::date('allocation_date', $hostelAllocation->allocation_date ?? date('Y-m-d'), ['class' => 'form-control', 'id'=>'allocation_date', 'required']) !!}
</div>

<!-- Vacating Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('vacating_date', 'Expected/Actual Vacating Date:') !!}
    {!! Form::date('vacating_date', $hostelAllocation->vacating_date ?? null, ['class' => 'form-control', 'id'=>'vacating_date']) !!}
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', [
        'active' => 'Active',
        'vacated' => 'Vacated',
        'pending' => 'Pending'
    ], null, ['class' => 'form-control select2', 'required']) !!}
</div>

<!-- Academic Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control select2', 'placeholder' => 'Select Academic Year', 'required']) !!}
</div>

<!-- Checkout Notes (Only on edit/vacated) -->
@if(isset($hostelAllocation))
<div class="form-group col-sm-12">
    {!! Form::label('checkout_notes', 'Checkout/Status Notes:') !!}
    {!! Form::textarea('checkout_notes', null, ['class' => 'form-control', 'rows' => 3]) !!}
</div>
@endif

@push('page_scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush