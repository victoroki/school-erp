@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem;">
                        <i class="fas fa-info-circle text-primary mr-2"></i> Class Section Details
                    </h1>
                    <p class="text-muted mb-0">Full overview of the class and its assigned section.</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <a class="btn px-4 py-2 shadow-sm"
                       href="{{ route('class-sections.index') }}"
                       style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                    <a class="btn btn-primary px-4 py-2 shadow-sm ml-2"
                       href="{{ route('class-sections.edit', $classSection->class_section_id) }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-edit mr-2"></i> Edit
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 py-4 px-4">
                <h5 class="mb-0 font-weight-bold">Summary Information</h5>
            </div>
            <div class="card-body px-4 pb-5">
                <div class="row">
                    @include('class_sections.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
