@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-calendar-alt text-info mr-2"></i>Academic Calendar
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-primary" href="{{ route('academic-calendar.create') }}">
                        <i class="fas fa-plus"></i> Add Event
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-info elevation-2 mb-4">
            <div class="card-body">
                {!! Form::open(['route' => 'academic-calendar.index', 'method' => 'GET', 'class' => 'form-inline']) !!}
                    <div class="form-group mr-3">
                        <label for="academic_year_id" class="mr-2">Academic Year:</label>
                        {!! Form::select('academic_year_id', $academicYears->pluck('name', 'academic_year_id'), $selectedAcademicYearId, ['class' => 'form-control', 'onchange' => 'this.form.submit()']) !!}
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">School Events & Terms</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>Event Title</th>
                                        <th>Type</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($events as $event)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2" style="width: 12px; height: 12px; border-radius: 50%; background-color: {{ $event->event_color }}"></div>
                                                    <span class="font-weight-bold">{{ $event->title }}</span>
                                                </div>
                                                <small class="text-muted d-block ml-4">{{ Str::limit($event->description, 50) }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-pill badge-light border text-uppercase small">
                                                    {{ str_replace('_', ' ', $event->event_type) }}
                                                </span>
                                            </td>
                                            <td><span class="small font-weight-bold">{{ $event->start_date->format('M d, Y') }}</span></td>
                                            <td><span class="small text-muted">{{ $event->end_date ? $event->end_date->format('M d, Y') : '-' }}</span></td>
                                            <td>
                                                @if($event->start_date->isFuture())
                                                    <span class="badge badge-info">Upcoming</span>
                                                @elseif($event->end_date && $event->end_date->isPast())
                                                    <span class="badge badge-secondary">Past</span>
                                                @else
                                                    <span class="badge badge-success">Ongoing</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('academic-calendar.edit', $event->id) }}" class="btn btn-sm btn-outline-info mr-1">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                {!! Form::open(['route' => ['academic-calendar.destroy', $event->id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                {!! Form::close() !!}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="fas fa-calendar-times fa-3x mb-3 d-block opacity-50"></i>
                                                No events scheduled for this academic year.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
