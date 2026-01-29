@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Timetables</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('timetables.create') }}">
                        Add Lesson
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card mb-3">
            <div class="card-body">
                {!! Form::open(['route' => 'timetables.index', 'method' => 'GET']) !!}
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        {!! Form::label('academic_year_id', 'Academic Year') !!}
                        {!! Form::select(
                            'academic_year_id',
                            $academicYearOptions,
                            $selectedAcademicYearId,
                            ['class' => 'form-control', 'placeholder' => 'Select academic year']
                        ) !!}
                    </div>
                    <div class="form-group col-md-4">
                        {!! Form::label('class_section_id', 'Class and Section') !!}
                        {!! Form::select(
                            'class_section_id',
                            $classSectionOptions,
                            $selectedClassSectionId,
                            ['class' => 'form-control', 'placeholder' => 'Select class and section']
                        ) !!}
                    </div>
                    <div class="form-group col-md-4 d-flex">
                        <div class="ml-md-auto mt-4">
                            <button type="submit" class="btn btn-primary mr-2">
                                Apply
                            </button>
                            <a href="{{ route('timetables.index') }}" class="btn btn-default">
                                Reset
                            </a>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>

        @if($academicYearOptions->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted">
                    Set up at least one academic year before creating timetables.
                </div>
            </div>
        @elseif($classSectionOptions->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted">
                    No class sections found for the selected academic year.
                </div>
            </div>
        @elseif($periods->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted">
                    Define periods before building timetables.
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-bordered mb-0">
                        <thead>
                        <tr>
                            <th style="width: 140px;">Day / Period</th>
                            @foreach($periods as $period)
                                <th class="text-center">
                                    <div>{{ $period->name }}</div>
                                    <div class="text-muted small">
                                        {{ $period->start_time }} – {{ $period->end_time }}
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($daysOfWeek as $dayKey => $dayLabel)
                            <tr>
                                <td><strong>{{ $dayLabel }}</strong></td>
                                @foreach($periods as $period)
                                    @php
                                        $entry = $schedule[$dayKey][$period->period_id] ?? null;
                                    @endphp
                                    <td class="align-top">
                                        @if($entry)
                                            <div class="font-weight-bold">
                                                {{ $entry->subject->name }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $entry->teacher ? trim(($entry->teacher->first_name ?? '') . ' ' . ($entry->teacher->last_name ?? '')) : '' }}
                                            </div>
                                            <div class="text-muted small">
                                                Room {{ $entry->classroom->room_number }}
                                            </div>
                                            <div class="mt-1">
                                                <a href="{{ route('timetables.edit', $entry->timetable_id) }}"
                                                   class="btn btn-outline-primary btn-xs">
                                                    Edit
                                                </a>
                                                <a href="{{ route('timetables.show', $entry->timetable_id) }}"
                                                   class="btn btn-outline-secondary btn-xs">
                                                    Details
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-muted small">Free</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
