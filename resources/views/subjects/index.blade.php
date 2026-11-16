@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1>
                        <i class="fas fa-book mr-2 text-primary"></i> Subjects
                    </h1>
                    <p class="text-muted small mb-0">
                        Manage the course catalog. A subject that has been taught, examined or timetabled
                        can be archived but never deleted, so its academic history is never lost.
                    </p>
                </div>
                <div class="col-sm-4 text-right">
                    <a class="btn btn-primary mb-1" href="{{ route('subjects.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New Subject
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        <div class="clearfix"></div>

        <div class="card card-outline card-primary elevation-2">
            <div class="card-header bg-light">
                <form method="GET" action="{{ route('subjects.index') }}" class="row align-items-end">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <label for="q" class="mb-1">Search</label>
                        <div class="input-group">
                            <input type="text" name="q" id="q" value="{{ request('q') }}"
                                   class="form-control {{ $errors->has('q') ? 'is-invalid' : '' }}"
                                   placeholder="Subject name or code" autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="status" class="mb-1">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="archived" {{ $status === 'archived' ? 'selected' : '' }}>Archived</option>
                            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="department_id" class="mb-1">Department</label>
                        <select name="department_id" id="department_id" class="form-control">
                            <option value="">All departments</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->department_id }}"
                                    {{ (string) request('department_id') === (string) $department->department_id ? 'selected' : '' }}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2 mb-md-0">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <a href="{{ route('subjects.index') }}" class="btn btn-default btn-block">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </form>
            </div>

            @include('subjects.table')
        </div>
    </div>
@endsection
