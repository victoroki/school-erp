@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Student Parent Relationships</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-outline-secondary"
                       href="{{ route('student-parent-relationships.print', request()->only(['q', 'class_id'])) }}"
                       target="_blank">
                        <i class="fas fa-print mr-1"></i> Print
                    </a>
                    <a class="btn btn-primary"
                       href="{{ route('student-parent-relationships.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        {{-- Search & filters --}}
        <div class="card card-outline card-info mb-3">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('student-parent-relationships.index') }}">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-5 mb-md-0">
                            <label for="q" class="small font-weight-bold text-muted mb-1">Search</label>
                            <input type="text" id="q" name="q" value="{{ request('q') }}" class="form-control form-control-sm"
                                   placeholder="Student name, admission no, guardian name, phone or email...">
                        </div>
                        <div class="form-group col-md-4 mb-md-0">
                            <label for="class_id" class="small font-weight-bold text-muted mb-1">Class</label>
                            <select id="class_id" name="class_id" class="form-control form-control-sm">
                                <option value="">All classes</option>
                                @foreach($classes as $id => $name)
                                    <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3 mb-md-0 text-md-right">
                            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i> Apply</button>
                            <a href="{{ route('student-parent-relationships.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            @include('student_parent_relationships.table')
        </div>
    </div>

@endsection
