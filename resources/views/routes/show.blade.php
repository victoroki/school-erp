@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Route: {{ $route->name }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default" href="{{ route('routes.index') }}">Back</a>
                    <a class="btn btn-primary" href="{{ route('routes.edit', $route->route_id) }}">Edit Route</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <!-- Summary Sidebar -->
            <div class="col-md-4">
                <div class="card card-outline card-danger">
                    <div class="card-body box-profile">
                        <div class="text-center">
                            <i class="fas fa-route fa-3x text-danger mb-3"></i>
                        </div>
                        <h3 class="profile-username text-center">{{ $route->name }}</h3>
                        <p class="text-muted text-center">{{ $route->route_code }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>Vehicle</b> <span class="float-right text-dark">{{ $route->vehicle_name }} ({{ $route->vehicle_number }})</span>
                            </li>
                            <li class="list-group-item">
                                <b>Driver</b> <span class="float-right text-dark">{{ $route->driver_name }}</span>
                            </li>
                            <li class="list-group-item border-bottom-0">
                                <b>Status</b> 
                                <span class="float-right badge badge-{{ $route->status == 'active' ? 'success' : ($route->status == 'maintenance' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($route->status) }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="card card-danger card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Route Schedule</h3>
                    </div>
                    <div class="card-body">
                        <strong><i class="fas fa-sun mr-1 text-warning"></i> Morning Trip</strong>
                        <p class="text-muted">{{ $route->morning_start_time }} - {{ $route->morning_end_time }}</p>
                        <hr>
                        <strong><i class="fas fa-moon mr-1 text-primary"></i> Evening Trip</strong>
                        <p class="text-muted">{{ $route->evening_start_time }} - {{ $route->evening_end_time }}</p>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="col-md-8">
                <div class="card card-outline card-danger">
                    <div class="card-header p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#stops" data-toggle="tab">Route Stops</a></li>
                            <li class="nav-item"><a class="nav-link" href="#students" data-toggle="tab">Students ({{ $route->getCurrentOccupancy() }})</a></li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Stops Tab -->
                            <div class="tab-pane active" id="stops">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Stop Name</th>
                                                <th>Landmark</th>
                                                <th>Est. Time</th>
                                                <th>Fee</th>
                                                <th>Students</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($route->routeStops as $stop)
                                                <tr>
                                                    <td><span class="badge badge-secondary">{{ $stop->sequence }}</span></td>
                                                    <td><strong>{{ $stop->stop_name }}</strong></td>
                                                    <td>{{ $stop->landmark }}</td>
                                                    <td>{{ $stop->stop_time }}</td>
                                                    <td>{{ $stop->stop_fee }}</td>
                                                    <td><span class="badge badge-info">{{ $stop->getStudentCount() }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($route->routeStops->isEmpty())
                                    <p class="text-center py-3 text-muted">No stops defined for this route. <a href="{{ route('routeStops.create', ['route_id' => $route->route_id]) }}">Add one?</a></p>
                                @endif
                            </div>

                            <!-- Students Tab -->
                            <div class="tab-pane" id="students">
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped">
                                        <thead>
                                            <tr>
                                                <th>Student ID</th>
                                                <th>Name</th>
                                                <th>Pickup Stop</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($route->studentAssignments->where('status', 'active') as $assignment)
                                                <tr>
                                                    <td>{{ $assignment->student->student_id }}</td>
                                                    <td>{{ $assignment->student->first_name }} {{ $assignment->student->last_name }}</td>
                                                    <td>{{ $assignment->pickupStop->stop_name ?? 'N/A' }}</td>
                                                    <td><span class="badge badge-success">Active</span></td>
                                                    <td>
                                                        <a href="{{ route('student-transport-assignments.edit', $assignment->assignment_id) }}" class="btn btn-xs btn-default"><i class="fas fa-edit"></i></a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-3">No students assigned to this route.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-3">
                                    <a href="{{ route('student-transport-assignments.create', ['route_id' => $route->route_id]) }}" class="btn btn-danger btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Assign New Student
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
