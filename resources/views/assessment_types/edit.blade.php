@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-edit mr-2"></i> Edit Assessment Type
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card card-outline card-danger elevation-2 border-0">
            {!! Form::model($assessmentType, ['route' => ['assessment-types.update', $assessmentType->id], 'method' => 'patch']) !!}
            <div class="card-body">
                @include('assessment_types.fields')
            </div>
            <div class="card-footer bg-white border-top">
                {!! Form::submit('Update Assessment Type', ['class' => 'btn btn-danger px-4']) !!}
                <a href="{{ route('assessment-types.index') }}" class="btn btn-default px-4 ml-2">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
