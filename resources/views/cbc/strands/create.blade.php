@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-plus-circle mr-2"></i> Add CBC Strand
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')
        <div class="card card-outline card-danger elevation-2 border-0">
            {!! Form::open(['route' => 'strands.store']) !!}
            <div class="card-body">
                @include('cbc.strands.fields')
            </div>
            <div class="card-footer bg-white border-top">
                {!! Form::submit('Save Strand', ['class' => 'btn btn-danger px-4']) !!}
                <a href="{{ route('strands.index') }}" class="btn btn-default px-4 ml-2">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
