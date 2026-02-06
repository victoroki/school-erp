@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Bulk Hostel Allocation</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::open(['route' => 'hostel-allocations.bulk-store']) !!}

            <div class="card-body">
                <div class="row">
                    <!-- Student Selection -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('student_ids', 'Select Students:') !!}
                        {!! Form::select('student_ids[]', $students, null, ['class' => 'form-control select2', 'multiple' => 'multiple', 'style' => 'width: 100%']) !!}
                        <small class="text-muted">You can select multiple students to allocate to the same room.</small>
                    </div>

                    <!-- Hostel Selection -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('hostel_id', 'Hostel:') !!}
                        {!! Form::select('hostel_id', ['' => 'Select Hostel'] + $hostels, null, ['class' => 'form-control select2', 'id' => 'hostel_select']) !!}
                    </div>

                    <!-- Room Selection -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('room_id', 'Room:') !!}
                        <select name="room_id" id="room_select" class="form-control select2">
                            <option value="">Select Room</option>
                            @foreach($rooms as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date & Academic Year -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('allocation_date', 'Allocation Date:') !!}
                        {!! Form::date('allocation_date', date('Y-m-d'), ['class' => 'form-control', 'id'=>'allocation_date']) !!}
                    </div>

                    <div class="form-group col-sm-4">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control select2']) !!}
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default">Cancel</a>
                {!! Form::submit('Allocate Students', ['class' => 'btn btn-primary']) !!}
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Filtering rooms based on hostel could be added here via AJAX if needed, 
            // but for now we list all available rooms with their hostel names.
        });
    </script>
@endpush
