@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-calendar-edit text-info mr-2"></i>Edit Academic Event
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-info elevation-2">
            {!! Form::model($event, ['route' => ['academic-calendar.update', $event->id], 'method' => 'patch']) !!}
            <div class="card-body">
                <div class="row">
                    <!-- Academic Year Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('academic_year_id', 'Academic Year:') !!}
                        {!! Form::select('academic_year_id', $academicYears, null, ['class' => 'form-control', 'placeholder' => 'Select Academic Year']) !!}
                    </div>

                    <!-- Event Type Field -->
                    <div class="form-group col-sm-6">
                        {!! Form::label('event_type', 'Event Type:') !!}
                        {!! Form::select('event_type', [
                            'term_start' => 'Term Start',
                            'term_end' => 'Term End',
                            'holiday' => 'Holiday',
                            'exam' => 'Exam Period',
                            'sport' => 'Sports Event',
                            'parent_meeting' => 'Parents Meeting',
                            'other' => 'Other Event'
                        ], null, ['class' => 'form-control']) !!}
                    </div>

                    <!-- Title Field -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('title', 'Event Title:') !!}
                        {!! Form::text('title', null, ['class' => 'form-control']) !!}
                    </div>

                    <!-- Description Field -->
                    <div class="form-group col-sm-12">
                        {!! Form::label('description', 'Description:') !!}
                        {!! Form::textarea('description', null, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>

                    <!-- Start Date Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('start_date', 'Start Date:') !!}
                        {!! Form::date('start_date', $event->start_date->format('Y-m-d'), ['class' => 'form-control']) !!}
                    </div>

                    <!-- End Date Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('end_date', 'End Date (Optional):') !!}
                        {!! Form::date('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : null, ['class' => 'form-control']) !!}
                    </div>

                    <!-- Color Field -->
                    <div class="form-group col-sm-4">
                        {!! Form::label('event_color', 'Event Color:') !!}
                        <div class="input-group">
                            {!! Form::color('event_color', null, ['class' => 'form-control', 'style' => 'height: 38px;']) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                {!! Form::submit('Update Event', ['class' => 'btn btn-primary px-4 shadow-sm']) !!}
                <a href="{{ route('academic-calendar.index') }}" class="btn btn-default px-4 ml-2">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
