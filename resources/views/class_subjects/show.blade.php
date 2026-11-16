@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6 text-dark font-weight-bold">
                    <h1 style="font-size: 1.75rem;">
                        <i class="fas fa-book-reader text-primary mr-2"></i> Class Subject Details
                    </h1>
                    <p class="text-muted mb-0">Overview of the subject assigned to this class.</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <a class="btn px-4 py-2 shadow-sm mr-2"
                       href="{{ route('class-subjects.index') }}"
                       style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                    <a class="btn btn-primary px-4 py-2 shadow-sm"
                       href="{{ route('class-subjects.edit', $classSubject->class_subject_id) }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-edit mr-2"></i> Edit Assignment
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 py-4 px-4">
                <h5 class="mb-0 font-weight-bold text-dark">Assignment Summary</h5>
            </div>
            <div class="card-body px-4 pb-5">
                <div class="row">
                    @include('class_subjects.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
