@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-info">
                        <i class="fas fa-user-edit mr-2"></i> Edit Student Record
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('students.index') }}">Students</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-info elevation-2 border-0 overflow-hidden">
            {!! Form::model($student, ['route' => ['students.update', $student->student_id], 'method' => 'patch', 'enctype' => 'multipart/form-data']) !!}
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
                    <button type="submit" class="btn btn-info px-4 shadow-sm text-white font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Update Student Data
                    </button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
    <style>
        .bg-light-gray { background-color: #fcfcfc; }
    </style>
@endsection
