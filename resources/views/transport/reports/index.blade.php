@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Transportation Reports</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Route Lists</h3>
                    </div>
                    <div class="card-body">
                        <p>Generate a complete list of students assigned to a specific route, including their pickup stops and contact details. Ideal for drivers.</p>
                        <a href="{{ route('transportation.reports.route-wise') }}" class="btn btn-danger">
                            <i class="fas fa-file-pdf mr-1"></i> Generate Route List
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Occupancy Analysis</h3>
                    </div>
                    <div class="card-body">
                        <p>View a summary of all routes, comparing their seating capacity against currently assigned students to identify over or under-utilized routes.</p>
                        <a href="{{ route('transportation.reports.occupancy') }}" class="btn btn-danger">
                            <i class="fas fa-chart-pie mr-1"></i> View Occupancy Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
