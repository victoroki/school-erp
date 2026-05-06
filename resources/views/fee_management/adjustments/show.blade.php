@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Adjustment Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fees.adjustments.index') }}" style="border-radius: 8px;">
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
                            <span style="color: #0073e7;">✦</span> Adjustment Information
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Student</small>
                                <div style="font-weight: 600; color: #1f2937;">{{ $adjustment->student->full_name }}</div>
                                <small style="color: #6b7280;">{{ $adjustment->student->admission_no }}</small>
                            </div>
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Fee Category</small>
                                <div style="font-weight: 600; color: #1f2937;">{{ $adjustment->studentFeeAssignment->feeStructure->category->name ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="p-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <small style="color: #6b7280;">Original Amount</small>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #1f2937;">KES {{ number_format($adjustment->original_amount, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background: {{ $adjustment->status == 'approved' ? '#ecfdf5' : '#f8fafc' }}; border-radius: 8px; border: 1px solid {{ $adjustment->status == 'approved' ? '#a7f3d0' : '#e2e8f0' }};">
                                    <small style="color: #6b7280;">New Amount</small>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: #059669;">KES {{ number_format($adjustment->new_amount, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3" style="background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                    <small style="color: #6b7280;">Adjustment</small>
                                    <div style="font-size: 1.5rem; font-weight: 700; color: {{ $adjustment->adjustment_amount > 0 ? '#dc2626' : '#059669' }};">
                                        {{ $adjustment->adjustment_amount > 0 ? '-' : '+' }}KES {{ number_format(abs($adjustment->adjustment_amount), 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Adjustment Type</small>
                            <div class="mt-1">
                                @if($adjustment->adjustment_type == 'reduction')
                                    <span style="background: #dbeafe; color: #1d4ed8; padding: 6px 16px; border-radius: 20px; font-weight: 500;">Reduction</span>
                                @elseif($adjustment->adjustment_type == 'increase')
                                    <span style="background: #fef3c7; color: #92400e; padding: 6px 16px; border-radius: 20px; font-weight: 500;">Increase</span>
                                @else
                                    <span style="background: #ede9fe; color: #6d28d9; padding: 6px 16px; border-radius: 20px; font-weight: 500;">Full Waiver</span>
                                @endif
                            </div>
                        </div>

                        <div class="mb-4">
                            <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Reason</small>
                            <div class="mt-1 p-3" style="background: #f8fafc; border-radius: 8px; color: #374151;">
                                {{ $adjustment->reason }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Requested By</small>
                                <div style="font-weight: 500; color: #1f2937;">{{ $adjustment->requestedBy->name ?? 'System' }}</div>
                                <small style="color: #6b7280;">{{ $adjustment->requested_at->format('d/m/Y H:i') }}</small>
                            </div>
                            @if($adjustment->approvedBy)
                            <div class="col-md-6">
                                <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">{{ ucfirst($adjustment->status) }} By</small>
                                <div style="font-weight: 500; color: #1f2937;">{{ $adjustment->approvedBy->name }}</div>
                                <small style="color: #6b7280;">{{ $adjustment->approved_at->format('d/m/Y H:i') }}</small>
                            </div>
                            @endif
                        </div>

                        @if($adjustment->approval_notes)
                        <div class="mt-4">
                            <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Approval Notes</small>
                            <div class="mt-1 p-3" style="background: #ecfdf5; border-radius: 8px; color: #047857;">
                                {{ $adjustment->approval_notes }}
                            </div>
                        </div>
                        @endif

                        @if($adjustment->rejection_reason)
                        <div class="mt-4">
                            <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Rejection Reason</small>
                            <div class="mt-1 p-3" style="background: #fef2f2; border-radius: 8px; color: #b91c1c;">
                                {{ $adjustment->rejection_reason }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px;">
                    <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 24px;">
                        <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937;">Status & Actions</h3>
                    </div>
                    <div class="card-body" style="padding: 24px;">
                        <div class="mb-4">
                            <small style="color: #6b7280; text-transform: uppercase; font-size: 0.75rem;">Current Status</small>
                            <div class="mt-2">
                                @if($adjustment->status == 'pending')
                                    <span style="background: #fef3c7; color: #92400e; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 1rem;">
                                        <i class="fas fa-clock mr-2"></i> Pending Approval
                                    </span>
                                @elseif($adjustment->status == 'approved')
                                    <span style="background: #d1fae5; color: #047857; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 1rem;">
                                        <i class="fas fa-check-circle mr-2"></i> Approved
                                    </span>
                                @else
                                    <span style="background: #fee2e2; color: #b91c1c; padding: 8px 20px; border-radius: 20px; font-weight: 600; font-size: 1rem;">
                                        <i class="fas fa-times-circle mr-2"></i> Rejected
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if($adjustment->status == 'pending')
                        <hr style="border-top: 1px solid #e5e7eb;">
                        
                        <form action="{{ route('fees.adjustments.approve', $adjustment->id) }}" method="POST" class="mb-3">
                            @csrf
                            <div class="form-group">
                                <label style="font-weight: 600; color: #374151; font-size: 0.875rem;">Approval Notes (Optional)</label>
                                <textarea name="approval_notes" class="form-control" rows="3" style="border-radius: 8px; border: 1px solid #d1d5db;" placeholder="Add notes for this approval..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success btn-block" style="border-radius: 8px; font-weight: 500;">
                                <i class="fas fa-check mr-2"></i> Approve Adjustment
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-block" style="border-radius: 8px; font-weight: 500;" data-toggle="modal" data-target="#rejectModal">
                            <i class="fas fa-times mr-2"></i> Reject Adjustment
                        </button>
                        @endif

                        <hr style="border-top: 1px solid #e5e7eb;">
                        <a href="{{ route('fees.adjustments.audit-log', $adjustment->id) }}" class="btn btn-default btn-block" style="border: 1px solid #d1d5db; border-radius: 8px;">
                            <i class="fas fa-history mr-2"></i> View Audit Log
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 12px; border: none;">
                <form action="{{ route('fees.adjustments.reject', $adjustment->id) }}" method="POST">
                    @csrf
                    <div class="modal-header" style="border-bottom: 1px solid #e5e7eb; padding: 20px 24px;">
                        <h5 class="modal-title" style="font-weight: 600; color: #1f2937;">Reject Adjustment</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" style="padding: 24px;">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" class="form-control" rows="4" style="border-radius: 8px; border: 1px solid #d1d5db;" required placeholder="Provide reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px;">
                        <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                        <button type="submit" class="btn btn-danger" style="border-radius: 8px;">Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
