@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Route Student List</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-default" onclick="window.print()"><i class="fas fa-print mr-1"></i> Print List</button>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-danger no-print">
            <div class="card-body">
                <form action="{{ route('transportation.reports.route-wise') }}" method="GET">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Select Route:</label>
                                {!! Form::select('route_id', ['' => 'Select a Route'] + $routes->pluck('name', 'route_id')->toArray(), $selectedRouteId, ['class' => 'form-control select2', 'required']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-danger btn-block">Generate List</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedRouteId)
            @php $route = $routes->where('route_id', $selectedRouteId)->first(); @endphp
            <div class="invoice p-3 mb-3">
                <div class="row">
                    <div class="col-12">
                        <h4>
                            <i class="fas fa-bus text-danger"></i> {{ $route->name }}
                            <small class="float-right text-muted">Date: {{ date('d M, Y') }}</small>
                        </h4>
                    </div>
                </div>
                
                <div class="row invoice-info mb-4 border-bottom pb-3">
                    <div class="col-sm-4 invoice-col">
                        <strong>Vehicle Details</strong><br>
                        Name: {{ $route->vehicle_name }}<br>
                        Reg No: {{ $route->vehicle_number }}<br>
                        Capacity: {{ $route->vehicle_capacity }} Beds
                    </div>
                    <div class="col-sm-4 invoice-col">
                        <strong>Driver Info</strong><br>
                        Name: {{ $route->driver_name }}<br>
                        Contact: {{ $route->driver_contact }}
                    </div>
                    <div class="col-sm-4 invoice-col">
                        <strong>Schedule</strong><br>
                        AM: {{ $route->morning_start_time }} - {{ $route->morning_end_time }}<br>
                        PM: {{ $route->evening_start_time }} - {{ $route->evening_end_time }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Pickup Stop</th>
                                    <th>Parent Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $index => $assignment)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $assignment->student->student_id }}</td>
                                        <td>{{ $assignment->student->first_name }} {{ $assignment->student->last_name }}</td>
                                        <td>{{ $assignment->pickupStop->stop_name ?? 'N/A' }}</td>
                                        <td>{{ $assignment->student->mobile_number ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3">No active students on this route.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('page_css')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        .main-footer, .content-header .btn {
            display: none !important;
        }
    }
</style>
@endpush
