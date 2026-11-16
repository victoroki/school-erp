@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-primary font-weight-bold">
                        <i class="fas fa-users mr-2"></i> Link Parent to Student
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')
        @include('flash::message')

        <div class="card card-outline card-primary shadow-sm">
            <form action="{{ route('student-parent-relationships.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <p class="text-muted mb-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        Search for a student and a parent/guardian, then link them. Tick
                        <strong>Primary Contact</strong> if this parent is the student's main point of contact.
                    </p>

                    <div class="row">
                        @include('student_parent_relationships.fields')
                    </div>

                </div>

                <div class="card-footer text-right bg-white">
                    <a href="{{ route('student-parent-relationships.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-link mr-1"></i> Link Parent
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection
