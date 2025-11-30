@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Students</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('students.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('students.index') }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by name or admission no">
                        </div>
                        <div class="col-md-4">
                            <select name="status" class="form-control">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="alumni" {{ request('status')=='alumni' ? 'selected' : '' }}>Alumni</option>
                                <option value="transferred" {{ request('status')=='transferred' ? 'selected' : '' }}>Transferred</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card">
            @include('students.table')
        </div>
    </div>

@endsection
