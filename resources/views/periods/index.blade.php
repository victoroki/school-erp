@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6 text-dark">
                    <h1 class="font-weight-bold" style="font-size: 1.75rem;">
                        <i class="fas fa-clock text-primary mr-2"></i> Class Periods
                    </h1>
                    <p class="text-muted mb-0">Manage teaching slots and schedule time intervals.</p>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right px-4 py-2 shadow-sm"
                       href="{{ route('periods.create') }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-plus mr-2"></i> Add New Period
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        @include('flash::message')
        <div class="clearfix"></div>

        @include('periods.table')
    </div>

@endsection
