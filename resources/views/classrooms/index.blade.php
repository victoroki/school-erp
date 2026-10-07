@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem;">
                        <i class="fas fa-door-open text-primary mr-2"></i> Classrooms
                    </h1>
                    <p class="text-muted mb-0">Manage school building resources and room assignments.</p>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right px-4 py-2 shadow-sm"
                       href="{{ route('classrooms.create') }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-plus mr-2"></i> Add New Room
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        @include('flash::message')
        <div class="clearfix"></div>

        {{-- Status tabs + search --}}
        <div class="card card-outline card-primary mb-3">
            <div class="card-body py-3">
                <div class="form-row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <ul class="nav nav-pills">
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'active' ? 'active' : '' }} py-1 px-3"
                                   href="{{ route('classrooms.index', ['status' => 'active']) }}">Active</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'archived' ? 'active' : '' }} py-1 px-3"
                                   href="{{ route('classrooms.index', ['status' => 'archived']) }}">Archived</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $status === 'all' ? 'active' : '' }} py-1 px-3"
                                   href="{{ route('classrooms.index', ['status' => 'all']) }}">All</a>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('classrooms.index') }}">
                            <input type="hidden" name="status" value="{{ $status }}">
                            <div class="input-group input-group-sm">
                                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                                       placeholder="Search by room number or building...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    @if(request('q'))
                                        <a href="{{ route('classrooms.index', ['status' => $status]) }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @include('classrooms.table')
    </div>

@endsection
