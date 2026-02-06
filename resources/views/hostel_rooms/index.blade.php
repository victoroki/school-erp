@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hostel Rooms</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('hostel-rooms.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-primary mb-3">
            <div class="card-body">
                <form action="{{ route('hostel-rooms.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Hostel:</label>
                                {!! Form::select('hostel_id', ['' => 'All Hostels'] + $hostels, request('hostel_id'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status:</label>
                                {!! Form::select('status', [
                                    '' => 'All Status',
                                    'available' => 'Available',
                                    'partial' => 'Partial',
                                    'full' => 'Full',
                                    'under_maintenance' => 'Maintenance'
                                ], request('status'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button>
                                <a href="{{ route('hostel-rooms.index') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @include('hostel_rooms.table')
        </div>
    </div>

@endsection
