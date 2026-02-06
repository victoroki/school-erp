@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Transportation Dashboard</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_routes'] }}</h3>
                        <p>Total Routes</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-route"></i>
                    </div>
                    <a href="{{ route('routes.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_students'] }}</h3>
                        <p>Students in Trans.</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="{{ route('student-transport-assignments.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_capacity'] }}</h3>
                        <p>Total Capacity</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bus-alt"></i>
                    </div>
                    <a href="{{ route('routes.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['maintenance_vehicles'] }}</h3>
                        <p>In Maintenance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <a href="{{ route('routes.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Route Occupancy Chart/Progress -->
            <div class="col-md-8">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Route Occupancy</h3>
                    </div>
                    <div class="card-body">
                        @foreach($routesOccupancy as $route)
                            @php $percent = $route->getOccupancyPercentage(); @endphp
                            <div class="progress-group mb-3">
                                <strong>{{ $route->name }}</strong> ({{ $route->vehicle_number }})
                                <span class="float-right"><b>{{ $route->occupied_count }}</b>/{{ $route->vehicle_capacity }}</span>
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-{{ $percent > 90 ? 'danger' : ($percent > 70 ? 'warning' : 'success') }}" 
                                         style="width: {{ $percent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Assignments -->
            <div class="col-md-4">
                <div class="card card-outline card-primary">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">Recent Assignments</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Route</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAssignments as $assignment)
                                        <tr>
                                            <td>{{ $assignment->student->first_name }}</td>
                                            <td><span class="badge badge-info">{{ $assignment->route->name }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ route('student-transport-assignments.index') }}" class="uppercase">View All Assignments</a>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('student-transport-assignments.create') }}" class="btn btn-app ml-0">
                            <i class="fas fa-user-plus"></i> Assign Student
                        </a>
                        <a href="{{ route('routes.create') }}" class="btn btn-app">
                            <i class="fas fa-plus-circle"></i> Add Route
                        </a>
                        <a href="{{ route('transportation.reports.index') }}" class="btn btn-app">
                            <i class="fas fa-print"></i> Route List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
