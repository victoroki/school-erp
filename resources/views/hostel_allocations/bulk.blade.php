@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Bulk Bed Allocation</h1>
                    <p class="text-muted mb-0">Fill a room with several students in one go. Nothing is saved unless every student can be placed.</p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Allocations
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-1"></i> Students &amp; Room</h3>
            </div>

            {!! Form::open(['route' => 'hostel-allocations.bulk-store']) !!}

            <div class="card-body">
                <div class="row">
                    <!-- Student Selection -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('student_ids', 'Students:') !!}
                        {!! Form::select('student_ids[]', $students, old('student_ids', []), ['class' => 'form-control select2', 'multiple' => 'multiple', 'required', 'style' => 'width: 100%']) !!}
                        <small class="form-text text-muted">
                            Search by name or admission number. Students who already hold a bed are rejected.
                        </small>
                    </div>

                    <!-- Hostel Selection -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('hostel_id', 'Hostel:') !!}
                        {!! Form::select('hostel_id', ['' => 'Select Hostel'] + $hostels, old('hostel_id'), ['class' => 'form-control select2', 'id' => 'hostel_select', 'required', 'style' => 'width: 100%']) !!}
                    </div>

                    <!-- Room Selection -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('room_id', 'Room:') !!}
                        <select name="room_id" id="room_select" class="form-control select2" required style="width: 100%">
                            <option value="">Select Room</option>
                            @foreach($rooms as $roomId => $roomLabel)
                                <option value="{{ $roomId }}"
                                        data-hostel="{{ $roomHostels[$roomId] ?? '' }}"
                                        @selected((string) old('room_id') === (string) $roomId)>
                                    {{ $roomLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Allocation Date -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('allocation_date', 'Allocation Date:') !!}
                        {!! Form::date('allocation_date', old('allocation_date', date('Y-m-d')), ['class' => 'form-control', 'id' => 'allocation_date', 'required']) !!}
                    </div>

                    <!-- Academic Year -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', ['' => 'Select Academic Year'] + $academicYears, old('academic_year_id'), ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                    </div>

                    <!-- Live summary -->
                    <div class="col-sm-12">
                        <div class="alert alert-light border d-flex justify-content-between align-items-center py-2 mb-0" id="bulk-summary-box">
                            <span class="mb-0">
                                <i class="fas fa-info-circle mr-1 text-info"></i>
                                <span id="bulk-summary-text">Choose a hostel and room to see how many students fit.</span>
                            </span>
                            <span class="badge badge-secondary" id="bulk-selected-count">0 selected</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default mr-2">Cancel</a>
                {!! Form::submit('Allocate Students', ['class' => 'btn btn-primary']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    </div>

    @include('hostel_allocations.partials.room-cascade')
@endsection

@push('page_scripts')
    <script>
        // Plain DOM: works regardless of the jQuery/select2 load order.
        (function () {
            var students = document.getElementById('student_ids');
            var room = document.getElementById('room_select');
            var box = document.getElementById('bulk-summary-box');
            var countLabel = document.getElementById('bulk-selected-count');
            var summary = document.getElementById('bulk-summary-text');
            if (!students || !room) { return; }

            function freeBeds() {
                var option = room.options[room.selectedIndex];
                if (!option || !option.value) { return null; }
                var match = option.textContent.match(/(\d+)\s+beds? left/);
                return match ? parseInt(match[1], 10) : null;
            }

            function render() {
                var selected = students.selectedOptions ? students.selectedOptions.length : 0;
                if (countLabel) {
                    countLabel.textContent = selected + ' selected';
                    countLabel.className = 'badge ' + (selected > 0 ? 'badge-primary' : 'badge-secondary');
                }

                if (!summary) { return; }

                var free = freeBeds();
                if (free === null) {
                    summary.textContent = 'Choose a hostel and room to see how many students fit.';
                    return;
                }

                if (selected === 0) {
                    summary.textContent = 'Room holds ' + free + ' free bed' + (free === 1 ? '' : 's') + '. Now pick the students.';
                    return;
                }

                summary.textContent = selected + ' student' + (selected === 1 ? '' : 's') + ' selected, ' + free + ' free bed' + (free === 1 ? '' : 's') + ' available.';
                if (box) { box.classList.toggle('alert-warning', selected > free); }
            }

            students.addEventListener('change', render);
            room.addEventListener('change', render);
            render();
        })();
    </script>
@endpush
