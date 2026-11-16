@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h1>Guardian Profile</h1>
                    <p class="text-muted small mb-0">Contact details and the learners registered against this guardian.</p>
                </div>
                <div class="col-sm-4 text-right">
                    @can('students.manage')
                        <a class="btn btn-primary mb-1 mr-1" href="{{ route('parents.edit', $parents->parent_id) }}">
                            <i class="far fa-edit mr-1"></i> Edit
                        </a>
                    @endcan
                    <a class="btn btn-default mb-1" href="{{ route('parents.index') }}">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card card-outline card-primary elevation-2">
            <div class="card-body">
                <div class="row">
                    @include('parents.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
