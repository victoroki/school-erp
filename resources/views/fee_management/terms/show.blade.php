@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Term Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a class="btn btn-default" href="{{ route('fees.terms.index') }}" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row">
            <div class="col-md-8">
                <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 24px;">
                        <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937;">
                            <span style="color: #0073e7;">✦</span> Term Information
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Term</small>
                                <div style="font-weight: 600; color: #1f2937;">{{ $term->name }}</div>
                                <small style="color: #6b7280;">Code: {{ $term->code }}</small>
                            </div>
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Academic Year</small>
                                <div style="font-weight: 600; color: #1f2937;">{{ $term->academicYear->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="p-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <small style="color: #6b7280;">Start Date</small>
                                    <div style="font-weight: 700; color: #1f2937;">{{ $term->start_date->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <small style="color: #6b7280;">End Date</small>
                                    <div style="font-weight: 700; color: #1f2937;">{{ $term->end_date->format('d/m/Y') }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background: {{ $term->fee_due_date ? '#f8fafc' : '#fef3c7' }}; border-radius: 8px; border: 1px solid {{ $term->fee_due_date ? '#e2e8f0' : '#fde68a' }};">
                                    <small style="color: #6b7280;">Fee Due Date</small>
                                    <div style="font-weight: 700; color: {{ $term->fee_due_date ? '#1f2937' : '#92400e' }};">
                                        {{ $term->fee_due_date ? $term->fee_due_date->format('d/m/Y') : 'Not set' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Status</small>
                                <div class="mt-1">
                                    @if($term->status == 'active')
                                        <span style="background: #d1fae5; color: #047857; padding: 6px 16px; border-radius: 20px; font-weight: 500;">Active</span>
                                    @elseif($term->status == 'upcoming')
                                        <span style="background: #dbeafe; color: #1d4ed8; padding: 6px 16px; border-radius: 20px; font-weight: 500;">Upcoming</span>
                                    @else
                                        <span style="background: #f3f4f6; color: #4b5563; padding: 6px 16px; border-radius: 20px; font-weight: 500;">Completed</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Display Order</small>
                                <div style="font-weight: 600; color: #1f2937;">{{ $term->display_order ?? '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 24px;">
                        <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937;">Actions</h3>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <a href="{{ route('fees.terms.edit', $term->id) }}" class="btn btn-primary btn-block" style="border-radius: 8px;">
                            <i class="fas fa-edit mr-2"></i> Edit Term
                        </a>
                        @if($term->status != 'active')
                        <form action="{{ route('fees.terms.activate', $term->id) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block" style="border-radius: 8px;">
                                <i class="fas fa-play mr-2"></i> Activate Term
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <div class="card mt-3" style="border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 24px;">
                        <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937;">Fee Structures ({{ $term->feeStructures->count() }})</h3>
                    </div>
                    <div class="card-body p-0">
                        @if($term->feeStructures->isEmpty())
                            <p class="text-center text-muted py-4 mb-0">No fee structures linked to this term.</p>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach($term->feeStructures->take(10) as $fs)
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="border-color: #f1f5f9;">
                                        <span style="color: #374151;">{{ $fs->category->name ?? 'Uncategorized' }}</span>
                                        <span style="font-family: monospace; color: #1f2937;">KES {{ number_format($fs->amount, 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
