@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Hostel Allocations</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('hostel-allocations.create') }}">
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
                <form action="{{ route('hostel-allocations.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Hostel:</label>
                                {!! Form::select('hostel_id', ['' => 'All Hostels'] + $hostels, request('hostel_id'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status:</label>
                                {!! Form::select('status', [
                                    '' => 'All Status',
                                    'active' => 'Active',
                                    'vacated' => 'Vacated',
                                    'pending' => 'Pending'
                                ], request('status'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search mr-1"></i> Filter</button>
                                <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                        <div class="col-md-3 text-right">
                            <label>&nbsp;</label>
                            <div>
                                <a href="{{ route('hostel-allocations.bulk-form') }}" class="btn btn-info">
                                    <i class="fas fa-users mr-1"></i> Bulk Allocation
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @include('hostel_allocations.table')
        </div>
    </div>

@endsection
