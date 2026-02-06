@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>Assign Student to Transport</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card">
            {!! Form::open(['route' => 'student-transport-assignments.store']) !!}
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
                        {!! Form::select('route_id', $routes, request('route_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Route', 'required', 'id' => 'route_select']) !!}
                    </div>

                    <!-- Pickup Stop Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('pickup_stop_id', 'Pickup Stop:') !!}
                        {!! Form::select('pickup_stop_id', [], null, ['class' => 'form-control select2', 'placeholder' => 'Select Stop', 'id' => 'pickup_stop_select']) !!}
                    </div>

                    <!-- Drop Stop Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('drop_stop_id', 'Drop Stop:') !!}
                        {!! Form::select('drop_stop_id', [], null, ['class' => 'form-control select2', 'placeholder' => 'Select Stop', 'id' => 'drop_stop_select']) !!}
                    </div>

                    <!-- Academic Year Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control', 'required']) !!}
                    </div>

                    <!-- Assigned Date Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('assigned_date', 'Assignment Date:') !!}
                        {!! Form::date('assigned_date', date('Y-m-d'), ['class' => 'form-control']) !!}
                    </div>

                    <!-- Status Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('status', 'Status:') !!}
                        {!! Form::select('status', ['active' => 'Active', 'inactive' => 'Inactive'], 'active', ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                {!! Form::submit('Assign Student', ['class' => 'btn btn-danger']) !!}
                <a href="{{ route('student-transport-assignments.index') }}" class="btn btn-default">Cancel</a>
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

            $('#route_select').on('change', function() {
                var routeId = $(this).val();
                if (routeId) {
                    $.ajax({
                        url: '/api/routes/' + routeId + '/stops',
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#pickup_stop_select').empty().append('<option value="">Select Stop</option>');
                            $('#drop_stop_select').empty().append('<option value="">Select Stop</option>');
                            $.each(data, function(key, stop) {
                                var option = '<option value="' + stop.stop_id + '">' + stop.stop_name + ' (' + stop.stop_time + ')</option>';
                                $('#pickup_stop_select').append(option);
                                $('#drop_stop_select').append(option);
                            });
                        }
                    });
                }
            });

            // Trigger change if route_id is in URL
            if($('#route_select').val()) {
                $('#route_select').trigger('change');
            }
        });
    </script>
@endpush
