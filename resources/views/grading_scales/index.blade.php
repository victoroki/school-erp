@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Grading Scales</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        @php
                            $hasKcseGrades = \App\Models\GradingScale::whereIn('name', ['A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'E'])->count() > 0;
                            $hasCbcGrades = \App\Models\GradingScale::whereIn('name', ['EE', 'ME', 'AE', 'BE'])->count() > 0;
                        @endphp
                        {!! Form::open(['route' => 'gradingScales.seed-kcse', 'method' => 'POST', 'class' => 'd-inline']) !!}
                            {!! Form::button('<i class="fas fa-flag mr-1"></i> Load KCSE (8-4-4)', ['type' => 'submit', 'class' => 'btn btn-success mr-2 ' . ($hasKcseGrades ? 'disabled' : ''), 'disabled' => $hasKcseGrades, 'onclick' => $hasKcseGrades ? '' : "return confirm('Add the standard KCSE grading scale (A to E with points)? Existing grades will not be changed.')", 'title' => $hasKcseGrades ? 'KCSE grades already loaded' : '']) !!}
                        {!! Form::close() !!}
                        {!! Form::open(['route' => 'gradingScales.seed-cbc', 'method' => 'POST', 'class' => 'd-inline']) !!}
                            {!! Form::button('<i class="fas fa-seedling mr-1"></i> Load CBC / CBE', ['type' => 'submit', 'class' => 'btn btn-info mr-2 ' . ($hasCbcGrades ? 'disabled' : ''), 'disabled' => $hasCbcGrades, 'onclick' => $hasCbcGrades ? '' : "return confirm('Add the standard CBC/CBE performance levels (EE, ME, AE, BE)? Existing grades will not be changed.')", 'title' => $hasCbcGrades ? 'CBC/CBE grades already loaded' : '']) !!}
                        {!! Form::close() !!}
                        <a class="btn btn-primary"
                           href="{{ route('gradingScales.create') }}">
                            Add New
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card">
            @include('grading_scales.table')
        </div>
    </div>

@endsection
