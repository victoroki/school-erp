@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6 text-dark">
                    <h1 class="font-weight-bold" style="font-size: 1.75rem;">
                        <i class="fas fa-history text-primary mr-2"></i> Period Details
                    </h1>
                    <p class="text-muted mb-0">Overview of the specific time slot and duration.</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <a class="btn px-4 py-2 shadow-sm mr-2"
                       href="{{ route('periods.index') }}"
                       style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                    <a class="btn btn-primary px-4 py-2 shadow-sm"
                       href="{{ route('periods.edit', $period->period_id) }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-edit mr-2"></i> Edit Period
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
            <div class="card-header bg-white border-bottom-0 py-4 px-4">
                <h5 class="mb-0 font-weight-bold text-dark">Time Interval Summary</h5>
            </div>
            <div class="card-body px-4 pb-5">
                <div class="row">
                    @include('periods.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection
