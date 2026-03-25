@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-calendar-alt mr-2"></i> Exam Sessions
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-success px-4 mr-2 elevation-2" href="{{ route('exam-results.bulk') }}">
                        <i class="fas fa-file-import mr-1"></i> Bulk Import Results
                    </a>
                    <a class="btn btn-danger elevation-2 px-4" href="{{ route('exams.create') }}">
                        <i class="fas fa-plus mr-1"></i> New Session
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <!-- Filter Bar -->
        <div class="card elevation-1 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('exams.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-uppercase">Search Exam</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                </div>
                                <input type="text" name="q" class="form-control border-left-0" placeholder="Exam name..." value="{{ request('q') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Exam Type</label>
                            {!! Form::select('exam_type_id', $examTypes, request('exam_type_id'), ['class' => 'form-control select2', 'placeholder' => 'All Categories']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Academic Year</label>
                            {!! Form::select('academic_year_id', $academicYears, request('academic_year_id'), ['class' => 'form-control select2', 'placeholder' => 'All Years']) !!}
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-block shadow-sm">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="card card-outline card-danger elevation-2 border-0 overflow-hidden">
            @include('exams.table')
        </div>
    </div>

@endsection
