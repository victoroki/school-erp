@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-primary">
                        <i class="fas fa-user-plus mr-2"></i> Student Admission
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                        <li class="breadcrumb-item active">Admission</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-primary elevation-2 border-0 overflow-hidden">
            {!! Form::open(['route' => 'students.store', 'enctype' => 'multipart/form-data']) !!}
                <div class="card-body bg-light-gray p-0">
                    <div class="p-4">
                        <div class="row">
                            @include('students.fields')
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white text-right py-3">
                    <a href="{{ route('students.index') }}" class="btn btn-light border mr-2"> 
                        <i class="fas fa-times mr-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="fas fa-save mr-1"></i> Save Student Record
                    </button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
    <style>
        .bg-light-gray { background-color: #fcfcfc; }
    </style>
@endsection
