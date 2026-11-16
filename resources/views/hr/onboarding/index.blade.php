@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-user-plus text-success"></i> Staff Onboarding</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('hr.dashboard') }}">HR</a></li>
                        <li class="breadcrumb-item active">Onboarding</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">Staff in Onboarding Process</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($onboardingStaff as $staff)
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $staff->photo_url ?? asset('images/default-avatar.png') }}" 
                                                 class="img-circle elevation-2" 
                                                 style="width: 60px; height: 60px;" 
                                                 alt="Staff Photo">
                                            <div class="ml-3 flex-grow-1">
                                                <h5 class="mb-0">{{ $staff->full_name }}</h5>
                                                <small class="text-muted">{{ $staff->jobPosition->title ?? 'N/A' }} | {{ $staff->department->name ?? 'N/A' }}</small>
                                                <div class="progress mt-2" style="height: 20px;">
                                                    @php
                                                        $total = $staff->onboardingChecklist->count();
                                                        $completed = $staff->onboardingChecklist->where('is_completed', true)->count();
                                                        $percentage = $total > 0 ? round(($completed / $total) * 100) : 0;
                                                    @endphp
                                                    <div class="progress-bar bg-success" style="width: {{ $percentage }}%">
                                                        {{ $percentage }}%
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <a href="{{ route('hr.onboarding.show', $staff->staff_id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-tasks"></i> View Checklist
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No staff currently in onboarding process.
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
