@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1 class="mb-1">Allocate a Bed</h1>
                    <p class="text-muted mb-0">Give a student a bed. The room's occupancy and status update automatically.</p>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Allocations
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bed mr-1"></i> Bed Details</h3>
            </div>

            {!! Form::open(['route' => 'hostel-allocations.store']) !!}

            <div class="card-body">
                <div class="row">
                    @include('hostel_allocations.fields')
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <a href="{{ route('hostel-allocations.bulk-form') }}" class="btn btn-info">
                    <i class="fas fa-users mr-1"></i> Allocate several students instead
                </a>
                <div>
                    <a href="{{ route('hostel-allocations.index') }}" class="btn btn-default mr-2"> Cancel </a>
                    {!! Form::submit('Allocate Bed', ['class' => 'btn btn-primary']) !!}
                </div>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@endsection
