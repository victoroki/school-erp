@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Route Stops</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-danger float-right"
                       href="{{ route('routeStops.create') }}">
                        <i class="fas fa-plus"></i> Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-danger mb-3">
            <div class="card-body">
                <form action="{{ route('routeStops.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Filter by Route:</label>
                                {!! Form::select('route_id', ['' => 'All Routes'] + $routes->toArray(), request('route_id'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i> Filter</button>
                                <a href="{{ route('routeStops.index') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @include('route_stops.table')
        </div>
    </div>

@endsection

@push('page_scripts')
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        });
    </script>
@endpush
