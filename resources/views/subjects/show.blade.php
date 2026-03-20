@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="font-weight-bold text-dark" style="font-size: 1.75rem;">
                        <i class="fas fa-book-open text-primary mr-2"></i> Subject Overview
                    </h1>
                    <p class="text-muted mb-0">Detailed curriculum and resource allocation for this course.</p>
                </div>
                <div class="col-sm-6 d-flex justify-content-end">
                    <a class="btn px-4 py-2 shadow-sm mr-2"
                       href="{{ route('subjects.index') }}"
                       style="background-color: #f1f5f9; color: #475569; font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-2"></i> Back to List
                    </a>
                    <a class="btn btn-primary px-4 py-2 shadow-sm"
                       href="{{ route('subjects.edit', $subject->subject_id) }}"
                       style="font-weight: 600; border-radius: 8px;">
                        <i class="fas fa-edit mr-2"></i> Edit Subject
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3 mt-2">
        <div class="row">
            <!-- Left Column: Core Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="p-4 text-center" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
                            <div class="bg-white shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 20px;">
                                <i class="fas fa-book fa-3x text-primary text-gradient"></i>
                            </div>
                            <h4 class="font-weight-bold text-dark mb-1">{{ $subject->name }}</h4>
                            <span class="badge" style="background-color: #dbeafe; color: #1e40af; padding: 0.4rem 0.8rem; border-radius: 6px; font-weight: 700;">
                                {{ $subject->subject_code }}
                            </span>
                        </div>
                        
                        <div class="p-4">
                            <div class="mb-4">
                                <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Type</label>
                                @if($subject->is_elective)
                                    <div class="d-flex align-items-center text-purple">
                                        <i class="fas fa-star mr-2"></i>
                                        <span class="font-weight-bold">Elective Subject</span>
                                    </div>
                                @else
                                    <div class="d-flex align-items-center text-success">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        <span class="font-weight-bold">Core Curriculum</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mb-4">
                                <label class="text-muted d-block small mb-1" style="font-weight: 700; text-transform: uppercase;">Description</label>
                                <p class="text-dark small mb-0" style="line-height: 1.6;">
                                    {{ $subject->description ?: 'No detailed description available for this subject.' }}
                                </p>
                            </div>

                            <div class="pt-3 border-top">
                                <small class="text-muted">Registered on {{ $subject->created_at ? $subject->created_at->format('M d, Y') : 'N/A' }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Assignments & Staff -->
            <div class="col-lg-8 mt-4 mt-lg-0">
                <!-- Classes Table -->
                <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-dark">
                            <i class="fas fa-school text-primary mr-2"></i> Assigned Classes
                        </h6>
                        <span class="badge badge-light border px-2 py-1">{{ $subject->classSubjects->count() }} Total</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8fafc;">
                                    <tr>
                                        <th class="border-0 text-muted small px-4" style="font-weight: 700; text-transform: uppercase;">Class Name</th>
                                        <th class="border-0 text-muted small" style="font-weight: 700; text-transform: uppercase;">Academic Year</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subject->classSubjects as $cs)
                                        <tr>
                                            <td class="px-4 py-3 align-middle font-weight-bold text-dark">
                                                <i class="fas fa-circle text-primary mr-2" style="font-size: 0.5rem;"></i>
                                                {{ $cs->class->name ?? 'Unknown Class' }}
                                            </td>
                                            <td class="py-3 align-middle">
                                                <span class="badge" style="background-color: #f1f5f9; color: #475569;">{{ $cs->academicYear->name ?? 'Current' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-5 text-muted small">No class assignments found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Teaching Staff -->
                <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 font-weight-bold text-dark">
                            <i class="fas fa-user-tie text-success mr-2"></i> Teaching Staff
                        </h6>
                        <span class="badge badge-light border px-2 py-1">{{ $subject->teacherSubjects->count() }} Teachers</span>
                    </div>
                    <div class="card-body p-0 text-dark">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background-color: #f8fafc;">
                                    <tr>
                                        <th class="border-0 text-muted small px-4" style="font-weight: 700; text-transform: uppercase;">Staff Member</th>
                                        <th class="border-0 text-muted small" style="font-weight: 700; text-transform: uppercase;">Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subject->teacherSubjects as $ts)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle mr-3 d-flex align-items-center justify-content-center bg-light font-weight-bold text-primary" style="width: 35px; height: 35px; border: 1px solid #e2e8f0;">
                                                        {{ strtoupper(substr($ts->staff->first_name ?? '?', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold">{{ $ts->staff->full_name ?? 'N/A' }}</div>
                                                        <div class="text-muted x-small">ID: {{ $ts->staff->employee_id ?? 'N/A' }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="py-3 align-middle">
                                                <span class="small text-muted font-weight-bold">{{ $ts->staff->department->name ?? 'N/A' }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-5 text-muted small">No teachers assigned to this subject.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .x-small { font-size: 0.7rem; }
        .text-purple { color: #a21caf; }
    </style>
@endsection
