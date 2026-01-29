@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Structures</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('fee-structures.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('fee-structures.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Filter by Class</label>
                                {!! Form::select('class_id', $classes, request('class_id'), ['class' => 'form-control select2', 'placeholder' => 'All Classes']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label>Filter by Academic Year</label>
                                {!! Form::select('academic_year_id', $academicYears, request('academic_year_id'), ['class' => 'form-control select2', 'placeholder' => 'All Years']) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="{{ route('fee-structures.index') }}" class="btn btn-default">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            @include('fee_structures.table')
        </div>
    </div>

@endsection
