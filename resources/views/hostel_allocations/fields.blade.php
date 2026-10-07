{{-- Hostel Allocation fields, shared by the create and edit forms.

     `$allocation` is the model when editing (null on create) and `$preselected`
     holds values carried in from elsewhere, e.g. "Quick Allocate" on a room.
     `old()` always wins so a failed validation keeps what was typed. --}}

@php
    $allocation = $allocation ?? null;
    $preselected = $preselected ?? [];
@endphp

<!-- Student Field -->
<div class="form-group col-sm-6">
    {!! Form::label('student_id', 'Student:') !!}
    {!! Form::select('student_id', $students, old('student_id', $allocation->student_id ?? $preselected['student_id'] ?? null), ['class' => 'form-control select2', 'placeholder' => 'Search for a student by name or admission no.', 'required', 'style' => 'width: 100%']) !!}
    <small class="form-text text-muted">Students already holding a bed cannot be allocated again.</small>
</div>

<!-- Academic Year Field -->
<div class="form-group col-sm-6">
    {!! Form::label('academic_year_id', 'Academic Year:') !!}
    {!! Form::select('academic_year_id', $academicYears, old('academic_year_id', $allocation->academic_year_id ?? null), ['class' => 'form-control select2', 'placeholder' => 'Select Academic Year', 'required', 'style' => 'width: 100%']) !!}
</div>

<!-- Hostel Field -->
<div class="form-group col-sm-6">
    {!! Form::label('hostel_id', 'Hostel:') !!}
    {!! Form::select('hostel_id', $hostels, old('hostel_id', $allocation->hostel_id ?? $preselected['hostel_id'] ?? null), ['class' => 'form-control select2', 'id' => 'hostel_select', 'placeholder' => 'Select Hostel', 'required', 'style' => 'width: 100%']) !!}
    <small class="form-text text-muted">Boys and girls hostels only accept students of the matching gender.</small>
</div>

<!-- Room Field -->
<div class="form-group col-sm-6">
    {!! Form::label('room_id', 'Room:') !!}
    <select name="room_id" id="room_select" class="form-control select2" required style="width: 100%">
        <option value="">-- Choose a room with a free bed --</option>
        @foreach($rooms as $roomId => $roomLabel)
            <option value="{{ $roomId }}"
                    data-hostel="{{ $roomHostels[$roomId] ?? '' }}"
                    @selected((string) old('room_id', $allocation->room_id ?? $preselected['room_id'] ?? '') === (string) $roomId)>
                {{ $roomLabel }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">
        <i class="fas fa-info-circle mr-1"></i>Only rooms with a free bed are listed. Choosing a hostel narrows the list.
    </small>
</div>

<!-- Bed Number Field -->
<div class="form-group col-sm-6">
    {!! Form::label('bed_number', 'Bed Number:') !!}
    {!! Form::number('bed_number', old('bed_number', $allocation->bed_number), ['class' => 'form-control', 'min' => 1, 'placeholder' => 'Leave blank to auto-assign the next free bed']) !!}
    <small class="form-text text-muted">Beds are numbered 1 to the room capacity. Leave blank and the next free bed is used.</small>
</div>

<!-- Status Field -->
<div class="form-group col-sm-6">
    {!! Form::label('status', 'Status:') !!}
    {!! Form::select('status', [
        'active' => 'Active — student holds this bed',
        'pending' => 'Pending — reserved, bed not yet taken',
        'vacated' => 'Vacated — bed released',
    ], old('status', $allocation->status ?? 'active'), ['class' => 'form-control select2', 'required', 'style' => 'width: 100%']) !!}
    <small class="form-text text-muted">Setting an allocation to vacated frees the bed immediately.</small>
</div>

<!-- Allocation Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('allocation_date', 'Allocation Date:') !!}
    {!! Form::date('allocation_date', old('allocation_date', $allocation?->allocation_date?->format('Y-m-d') ?? date('Y-m-d')), ['class' => 'form-control', 'id' => 'allocation_date', 'required']) !!}
</div>

<!-- Vacating Date Field -->
<div class="form-group col-sm-6">
    {!! Form::label('vacating_date', 'Expected Vacating Date:') !!}
    {!! Form::date('vacating_date', old('vacating_date', $allocation?->vacating_date?->format('Y-m-d')), ['class' => 'form-control', 'id' => 'vacating_date']) !!}
    <small class="form-text text-muted">Optional. Used for planning; the actual date is recorded on checkout.</small>
</div>

<!-- Checkout Notes (Only on edit/vacated) -->
@if($allocation)
    <div class="form-group col-sm-12">
        {!! Form::label('checkout_notes', 'Checkout / Status Notes:') !!}
        {!! Form::textarea('checkout_notes', old('checkout_notes', $allocation->checkout_notes), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Reason for vacating, room condition, damages...']) !!}
    </div>
@endif

@include('hostel_allocations.partials.room-cascade')
