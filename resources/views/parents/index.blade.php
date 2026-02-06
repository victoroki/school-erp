@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Parents</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('parents.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-primary elevation-2 mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('parents.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-10">
                            <div class="form-group mb-0">
                                <label class="small text-uppercase text-muted font-weight-bold">Search Parents</label>
                                <div class="input-group shadow-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="q" value="{{ request('q') }}" class="form-control border-left-0" placeholder="Name, Email, Phone, Occupation...">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="btn-group w-100">
                                <button type="submit" class="btn btn-primary shadow-sm">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                                <a href="{{ route('parents.index') }}" class="btn btn-light border shadow-sm">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-outline card-primary elevation-2">
            @include('parents.table')
        </div>
    </div>

@endsection
