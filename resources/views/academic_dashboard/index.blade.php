@extends('layouts.app')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fas fa-graduation-cap text-info mr-2"></i>Academic Management Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Academic Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content px-3">
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box elevation-1">
                    <span class="info-box-icon bg-info elevation-1"><i class="fas fa-chalkboard"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Classes</span>
                        <span class="info-box-number">{{ $stats['total_classes'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box elevation-1">
                    <span class="info-box-icon bg-success elevation-1"><i class="fas fa-book-open"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Subjects</span>
                        <span class="info-box-number">{{ $stats['total_subjects'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box elevation-1">
                    <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-user-tie"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Teachers</span>
                        <span class="info-box-number">{{ $stats['total_teachers'] }}</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box elevation-1">
                    <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Students</span>
                        <span class="info-box-number">{{ $stats['total_students'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Today's Lessons -->
            <div class="col-md-8">
                <div class="card card-outline card-info elevation-2">
                    <div class="card-header border-0">
                        <h3 class="card-title font-weight-bold">
                            <i class="fas fa-calendar-day mr-1"></i> Today's Live Schedule ({{ ucfirst(now()->format('l')) }})
                        </h3>
                        <div class="card-tools">
                             <span class="badge badge-info">{{ $todayLessons->count() }} Lessons Today</span>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Subject</th>
                                    <th>Class</th>
                                    <th>Teacher</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayLessons->sortBy('period.start_time') as $lesson)
                                    <tr>
                                        <td>
                                            <span class="text-info font-weight-bold small">{{ $lesson->period->start_time }}</span>
                                        </td>
                                        <td>{{ $lesson->subject->name }}</td>
                                        <td>
                                            <span class="badge badge-light">
                                                {{ $lesson->classSection->class->name ?? '' }} {{ $lesson->classSection->section->name ?? '' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $lesson->teacher->photo_url ?? asset('garikon-black.png') }}" alt="User" class="img-circle img-sm mr-2" style="width: 25px; height: 25px;">
                                                <span class="small">{{ $lesson->teacher->full_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td><span class="small text-muted"><i class="fas fa-door-open mr-1"></i> {{ $lesson->classroom->room_number }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No lessons scheduled for today.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-center bg-white">
                        <a href="{{ route('timetables.index') }}" class="small-box-footer">View Full Timetable <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
            </div>

            <!-- Side Widgets -->
            <div class="col-md-4">
                <!-- Classroom Utilization -->
                <div class="card card-outline card-success elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-th mr-1"></i> Venue Utilization</h3>
                    </div>
                    <div class="card-body">
                        @forelse($utilization as $item)
                            <div class="progress-group mb-3">
                                Room {{ $item['room'] }}
                                <span class="float-right"><b>{{ $item['rate'] }}%</b> Occupancy</span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-success" style="width: {{ $item['rate'] }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted">No room data for today.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Changes -->
                <div class="card card-outline card-warning elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-1"></i> Recent Changes</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @forelse($recentChanges as $change)
                                <li class="item">
                                    <div class="product-info ml-0">
                                        <a href="javascript:void(0)" class="product-title font-weight-bold">
                                            {{ $change->subject->name }} 
                                            <span class="badge badge-warning float-right small">{{ $change->updated_at->diffForHumans() }}</span>
                                        </a>
                                        <span class="product-description small text-muted">
                                            Schedule updated for {{ $change->classSection->class->name ?? 'Unknown' }}
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="item text-center py-3 text-muted">No recent changes.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card card-outline card-secondary elevation-2">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-bolt mr-1"></i> Quick Actions</h3>
                    </div>
                    <div class="card-body p-2">
                        <div class="row">
                            <div class="col-6 mb-2">
                                <a href="{{ route('timetables.create') }}" class="btn btn-app btn-block m-0 elevation-1">
                                    <i class="fas fa-plus"></i> Add Lesson
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="{{ route('subjects.create') }}" class="btn btn-app btn-block m-0 elevation-1">
                                    <i class="fas fa-book"></i> New Subject
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="{{ route('school-classes.index') }}" class="btn btn-app btn-block m-0 elevation-1">
                                    <i class="fas fa-chalkboard"></i> Manage Classes
                                </a>
                            </div>
                            <div class="col-6 mb-2">
                                <a href="{{ route('academic-calendar.index') }}" class="btn btn-app btn-block m-0 elevation-1">
                                    <i class="fas fa-calendar-plus"></i> Add Event
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
