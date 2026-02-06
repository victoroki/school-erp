@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Route Occupancy Report</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title">Seating Capacity vs Assignments</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-valign-middle">
                    <thead>
                        <tr>
                            <th>Route Name</th>
                            <th>Total Capacity</th>
                            <th>Current Students</th>
                            <th>Remaining Seats</th>
                            <th style="width: 30%">Utilization</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($routesPercentage as $route)
                            <tr>
                                <td><strong>{{ $route['name'] }}</strong></td>
                                <td>{{ $route['capacity'] }}</td>
                                <td>{{ $route['occupied'] }}</td>
                                <td>
                                    @php $remaining = $route['capacity'] - $route['occupied']; @endphp
                                    <span class="badge badge-{{ $remaining > 5 ? 'success' : ($remaining > 0 ? 'warning' : 'danger') }}">
                                        {{ $remaining }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress progress-xs w-100 mr-2">
                                            <div class="progress-bar bg-{{ $route['percentage'] > 90 ? 'danger' : ($route['percentage'] > 70 ? 'warning' : 'success') }}" 
                                                 style="width: {{ $route['percentage'] }}%"></div>
                                        </div>
                                        <small>{{ $route['percentage'] }}%</small>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
