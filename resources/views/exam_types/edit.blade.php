@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Edit Exam Type
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {{-- The primary key is exam_type_id; $examType->id is null, which built
                 the update route with an empty id. --}}
            {!! Form::model($examType, ['route' => ['exam-types.update', $examType->exam_type_id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('exam_types.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('exam-types.index') }}" class="btn btn-default"> Cancel </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
