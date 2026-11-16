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
                        {!! Form::label('student_id', 'Student') !!}
                        {!! Form::select('student_id', $students, null, ['class' => 'form-control select2', 'placeholder' => 'Search students by name or admission no.', 'required' => 'required']) !!}
                    </div>

                    <!-- Route Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('route_id', 'Route') !!}
                        {!! Form::select('route_id', $routes, null, ['class' => 'form-control select2', 'placeholder' => 'Search routes', 'required' => 'required', 'id' => 'route_select']) !!}
                    </div>

                    <!-- Pickup Stop Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('pickup_stop_id', 'Pickup Stop') !!}
                        {!! Form::select('pickup_stop_id', $stops, null, ['class' => 'form-control select2', 'id' => 'pickup_stop_select', 'disabled' => 'disabled']) !!}
                        <small class="form-text text-muted" id="pickup_stop_help">Choose a route first.</small>
                    </div>

                    <!-- Drop Stop Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('drop_stop_id', 'Drop Stop') !!}
                        {!! Form::select('drop_stop_id', $stops, null, ['class' => 'form-control select2', 'id' => 'drop_stop_select', 'disabled' => 'disabled']) !!}
                        <small class="form-text text-muted" id="drop_stop_help">Choose a route first.</small>
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

@include('student_transport_assignments._stop_cascade')
