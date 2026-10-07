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
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Search</label>
                                <input type="text" name="search" value="{{ request('search') }}"
                                       class="form-control" placeholder="Room number or floor">
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Hostel</label>
                                {!! Form::select('hostel_id', ['' => 'All Hostels'] + $hostels, request('hostel_id'), ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Status</label>
                                {!! Form::select('status', [
                                    '' => 'All Status',
                                    \App\Models\HostelRoom::STATUS_AVAILABLE => 'Available (has free beds)',
                                    \App\Models\HostelRoom::STATUS_FULL => 'Full',
                                    \App\Models\HostelRoom::STATUS_UNDER_MAINTENANCE => 'Under maintenance',
                                ], request('status'), ['class' => 'form-control select2', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <div class="form-group mb-0">
                                <label>Beds</label>
                                {!! Form::select('has_beds', [
                                    '' => 'Any',
                                    '1' => 'Has free beds',
                                    '0' => 'No free beds',
                                ], request('has_beds'), ['class' => 'form-control', 'style' => 'width: 100%']) !!}
                            </div>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button>
                            <a href="{{ route('hostel-rooms.index') }}" class="btn btn-default">Reset</a>
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
