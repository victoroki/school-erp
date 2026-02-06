@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-dark font-weight-bold"><i class="fas fa-plus-circle text-secondary mr-2"></i>Setup Financial Year</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            {!! Form::open(['route' => 'financial-years.store']) !!}
            <div class="card-body">
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Year Name <span class="text-danger">*</span></label>
                        {!! Form::text('name', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required', 'placeholder' => 'e.g. 2025/2026 Academic Year']) !!}
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Status</label>
                        {!! Form::select('status', ['open' => 'Open', 'closed' => 'Closed'], 'open', ['class' => 'form-control border-0 bg-light rounded-lg']) !!}
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">Start Date <span class="text-danger">*</span></label>
                        {!! Form::date('start_date', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required']) !!}
                    </div>
                    <div class="form-group col-sm-6">
                        <label class="font-weight-bold">End Date <span class="text-danger">*</span></label>
                        {!! Form::date('end_date', null, ['class' => 'form-control border-0 bg-light rounded-lg', 'required']) !!}
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light py-4 text-center">
                {!! Form::submit('Create Financial Year', ['class' => 'btn btn-secondary rounded-pill px-5 shadow-sm font-weight-bold']) !!}
                <a href="{{ route('financial-years.index') }}" class="btn btn-outline-secondary rounded-pill px-5 ml-2">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
