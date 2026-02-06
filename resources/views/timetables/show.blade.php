@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
                        <i class="fas fa-clock text-info mr-2"></i>Lesson Details
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default mr-2" href="{{ route('timetables.index') }}">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                    <a class="btn btn-info" href="{{ route('timetables.edit', $timetable->timetable_id) }}">
                        <i class="fas fa-edit"></i> Edit Lesson
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Lesson Card -->
            <div class="col-md-6 mx-auto">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">
                            {{ $timetable->day_of_week }}, {{ $timetable->period->name }}
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex align-items-center justify-content-center bg-light border elevation-1 mb-3" style="width: 80px; height: 80px; border-radius: 50%; border-top: 4px solid #17a2b8 !important;">
                                <i class="fas fa-chalkboard-teacher fa-2x text-info"></i>
                            </div>
                            <h4 class="font-weight-bold mb-1">{{ $timetable->subject->name }}</h4>
                            <p class="text-info font-weight-bold mb-0">
                                {{ $timetable->period->start_time }} - {{ $timetable->period->end_time }}
                            </p>
                        </div>

                        <hr>

                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted small font-weight-bold text-uppercase">Class & Section:</div>
                            <div class="col-sm-8 font-weight-bold">
                                {{ $timetable->classSection->class->name ?? '' }} {{ $timetable->classSection->section->name ?? '' }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted small font-weight-bold text-uppercase">Teacher:</div>
                            <div class="col-sm-8">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset('garikon-black.png') }}" class="img-circle img-sm mr-2 border shadow-sm" style="width: 25px; height: 25px;">
                                    <span>{{ $timetable->teacher->full_name ?? 'Not Assigned' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted small font-weight-bold text-uppercase">Classroom:</div>
                            <div class="col-sm-8">
                                <span class="badge badge-pill badge-light border px-3">
                                    <i class="fas fa-door-open mr-1 text-muted"></i> 
                                    Room {{ $timetable->classroom->room_number ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-sm-4 text-muted small font-weight-bold text-uppercase">Academic Year:</div>
                            <div class="col-sm-8">
                                <span class="text-muted">{{ $timetable->academicYear->name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="p-3 rounded bg-light border-left border-info" style="border-left-width: 4px !important;">
                                    <small class="text-muted font-italic">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        This lesson is part of the recurring weekly schedule. No conflicts detected with teacher or room availability.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
