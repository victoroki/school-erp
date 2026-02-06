@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-clipboard-check mr-2"></i> Assessment Types
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('assessment-types.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Type
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-danger elevation-2 border-0">
            <div class="card-body p-0">
                @include('assessment_types.table')
            </div>
        </div>
    </div>
@endsection
