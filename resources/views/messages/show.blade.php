@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Message Details
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        <div class="card">

            <div class="card-body">
                <div class="row">
                    @include('messages.show_fields')
                </div>
            </div>

            <div class="card-footer">
                <a href="{{ route('messages.index') }}" class="btn btn-default"> Back </a>
            </div>

        </div>
    </div>
@endsection