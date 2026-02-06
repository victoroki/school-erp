@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-danger">
                        <i class="fas fa-tasks mr-2"></i> Competency Assessment
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card elevation-2 border-0 mb-4">
            <div class="card-body">
                <form action="{{ route('cbc-assessments.index') }}" method="GET" id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Learning Area</label>
                            {!! Form::select('learning_area_id', $learningAreas, request('learning_area_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Area', 'required', 'onchange' => 'this.form.submit()']) !!}
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold text-uppercase">Strand</label>
                            {!! Form::select('strand_id', $strands, request('strand_id'), ['class' => 'form-control select2', 'placeholder' => 'Any Strand', 'onchange' => 'this.form.submit()']) !!}
                        </div>
                        <div class="col-md-2">
                            <label class="small font-weight-bold text-uppercase">Sub-Strand</label>
                            {!! Form::select('sub_strand_id', $subStrands, request('sub_strand_id'), ['class' => 'form-control select2', 'placeholder' => 'Any Sub-Strand', 'onchange' => 'this.form.submit()']) !!}
                        </div>
                        <div class="col-md-3">
                            <label class="small font-weight-bold text-uppercase">Class & Section</label>
                            {!! Form::select('class_section_id', $classSections, request('class_section_id'), ['class' => 'form-control select2', 'placeholder' => 'Select Class', 'required']) !!}
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-danger btn-block shadow-sm">
                                <i class="fas fa-search mr-1"></i> Retrieve
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(count($students) > 0)
        <div class="card card-outline card-danger elevation-2 border-0">
            <form action="{{ route('cbc-assessments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="learning_area_id" value="{{ request('learning_area_id') }}">
                <input type="hidden" name="strand_id" value="{{ request('strand_id') }}">
                <input type="hidden" name="sub_strand_id" value="{{ request('sub_strand_id') }}">
                
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Performance Ratings ({{ count($students) }} learners)</h3>
                    <div class="card-tools">
                        <button type="submit" class="btn btn-success px-4 elevation-1">
                            <i class="fas fa-save mr-1"></i> Save All Assessments
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th class="pl-4">Admission No</th>
                                <th>Learner Name</th>
                                <th width="300">Rating</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $learner)
                            @php
                                $currentRating = $existingAssessments[$learner->student_id]->rating ?? '';
                            @endphp
                            <tr>
                                <td class="pl-4">{{ $learner->admission_no }}</td>
                                <td class="font-weight-bold">{{ $learner->full_name }}</td>
                                <td>
                                    <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                                        <label class="btn btn-outline-danger btn-sm w-100 {{ $currentRating == 1 ? 'active' : '' }}" title="Below Expectation">
                                            <input type="radio" name="ratings[{{ $learner->student_id }}]" value="1" {{ $currentRating == 1 ? 'checked' : '' }}> BE
                                        </label>
                                        <label class="btn btn-outline-warning btn-sm w-100 {{ $currentRating == 2 ? 'active' : '' }}" title="Approaching Expectation">
                                            <input type="radio" name="ratings[{{ $learner->student_id }}]" value="2" {{ $currentRating == 2 ? 'checked' : '' }}> AE
                                        </label>
                                        <label class="btn btn-outline-info btn-sm w-100 {{ $currentRating == 3 ? 'active' : '' }}" title="Meeting Expectation">
                                            <input type="radio" name="ratings[{{ $learner->student_id }}]" value="3" {{ $currentRating == 3 ? 'checked' : '' }}> ME
                                        </label>
                                        <label class="btn btn-outline-success btn-sm w-100 {{ $currentRating == 4 ? 'active' : '' }}" title="Exceeding Expectation">
                                            <input type="radio" name="ratings[{{ $learner->student_id }}]" value="4" {{ $currentRating == 4 ? 'checked' : '' }}> EE
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    @if(isset($existingAssessments[$learner->student_id]))
                                        <span class="badge badge-success px-2">Assessed</span>
                                    @else
                                        <span class="badge badge-secondary px-2">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-right">
                    <button type="submit" class="btn btn-success btn-lg px-5 elevation-2">
                        <i class="fas fa-save mr-2"></i> SAVE ALL RATINGS
                    </button>
                    <div class="mt-2 small text-muted text-left">
                        <b>Legend:</b> BE = Below Expectation | AE = Approaching Expectation | ME = Meeting Expectation | EE = Exceeding Expectation
                    </div>
                </div>
            </form>
        </div>
        @elseif(request()->filled(['learning_area_id', 'class_section_id']))
        <div class="alert alert-info border-0 shadow-sm">
            <i class="fas fa-info-circle mr-2"></i> No active learners found for this class and section.
        </div>
        @endif
    </div>
@endsection
