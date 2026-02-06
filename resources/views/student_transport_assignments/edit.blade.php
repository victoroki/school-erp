@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Edit Transport Assignment</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::model($assignment, ['route' => ['student-transport-assignments.update', $assignment->assignment_id], 'method' => 'patch']) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Student Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('student_id', 'Student:') !!}
                        {!! Form::select('student_id', $students, null, ['class' => 'form-control select2', 'placeholder' => 'Select Student', 'required']) !!}
                    </div>

                    <!-- Route Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('route_id', 'Route:') !!}
                        {!! Form::select('route_id', $routes, null, ['class' => 'form-control select2', 'placeholder' => 'Select Route', 'required', 'id' => 'route_select']) !!}
                    </div>

                    <!-- Pickup Stop Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('pickup_stop_id', 'Pickup Stop:') !!}
                        {!! Form::select('pickup_stop_id', $stops, null, ['class' => 'form-control select2', 'placeholder' => 'Select Stop', 'id' => 'pickup_stop_select']) !!}
                    </div>

                    <!-- Drop Stop Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('drop_stop_id', 'Drop Stop:') !!}
                        {!! Form::select('drop_stop_id', $stops, null, ['class' => 'form-control select2', 'placeholder' => 'Select Stop', 'id' => 'drop_stop_select']) !!}
                    </div>

                    <!-- Academic Year Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Assigned Date Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('assigned_date', 'Assignment Date:') !!}
                        {!! Form::date('assigned_date', null, ['class' => 'form-control']) !!}
                    </div>

                    <!-- Status Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('status', 'Status:') !!}
                        {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive'], null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                {!! Form::submit('Update Assignment', ['class' => 'btn btn-danger']) !!}
                <a href="{{ route('student-transport-assignments.index') }}" class="btn btn-default">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').each(function() {
                $(this).select2({
                    theme: 'bootstrap4'
                });
            });

            $('#route_select').on('change', function() {
                var routeId = $(this).val();
                if (routeId) {
                    $.ajax({
                        url: '/api/routes/' + routeId + '/stops',
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            var currentPickup = "{{ $assignment->pickup_stop_id }}";
                            var currentDrop = "{{ $assignment->drop_stop_id }}";
                            
                            $('#pickup_stop_select').empty().append('<option value="">Select Stop</option>');
                            $('#drop_stop_select').empty().append('<option value="">Select Stop</option>');
                            
                            $.each(data, function(key, stop) {
                                var selectedPickup = (stop.stop_id == currentPickup) ? 'selected' : '';
                                var selectedDrop = (stop.stop_id == currentDrop) ? 'selected' : '';
                                
                                var option = '<option value="' + stop.stop_id + '">' + stop.stop_name + ' (' + stop.stop_time + ')</option>';
                                
                                $(option).appendTo('#pickup_stop_select').prop('selected', stop.stop_id == currentPickup);
                                $(option).appendTo('#drop_stop_select').prop('selected', stop.stop_id == currentDrop);
                            });
                            
                            $('#pickup_stop_select, #drop_stop_select').trigger('change');
                        }
                    });
                }
            });
        });
    </script>
@endpush
