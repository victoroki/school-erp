@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-primary font-weight-bold">
                        <i class="fas fa-users mr-2"></i> Edit Parent Link
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-primary shadow-sm">

            {!! Form::model($studentParentRelationship, ['route' => ['student-parent-relationships.update', $studentParentRelationship->id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('student_parent_relationships.fields')
                </div>
            </div>

            <div class="card-footer text-right bg-white">
                <a href="{{ route('student-parent-relationships.index') }}" class="btn btn-light border mr-2">Cancel</a>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-save mr-1"></i> Save Changes
                </button>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
