@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Routes</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('routes.create') }}">
                        Add New
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
                <form action="{{ route('routes.index') }}" method="GET">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Status:</label>
                                {!! Form::select('status', ['' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'], request('status'), ['class' => 'form-control select2']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-danger"><i class="fas fa-search"></i> Filter</button>
                                <a href="{{ route('routes.index') }}" class="btn btn-default">Reset</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @include('routes.table')
        </div>
    </div>

@endsection
