@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-12 text-dark font-weight-bold">
                    <h1 style="font-size: 1.75rem;">
                        <i class="fas fa-sitemap text-primary mr-2"></i> Create New Department
                    </h1>
                    <p class="text-muted mb-0">Establish a new academic or administrative unit.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        @include('adminlte-templates::common.errors')
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            {!! Form::open(['route' => 'departments.store']) !!}
            <div class="card-body p-4">
                <div class="row">
                    @include('departments.fields')
                </div>
            </div>
            <div class="card-footer bg-white border-top py-3 px-4 d-flex justify-content-end">
                <a href="{{ route('departments.index') }}" class="btn px-4 py-2 mr-2" style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                    Cancel
                </a>
                {!! Form::submit('Save Department', ['class' => 'btn btn-primary px-4 py-2 shadow-sm', 'style' => 'font-weight: 600; border-radius: 8px;']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
