@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Pending Fee Adjustments</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fees.adjustments.index') }}" style="border-radius: 8px;">
                        <i class="fas fa-arrow-left mr-1"></i> Back to All
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="alert" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 16px 20px;">
            <i class="fas fa-info-circle mr-2"></i>
            <strong>Review Required:</strong> These fee adjustments are awaiting your approval. Please review each request carefully.
        </div>

        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-light" style="border-bottom: 2px solid #e5e7eb;">
                            <tr>
                                <th class="px-4 py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Student</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Fee</th>
                                <th class="py-3 text-right" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Original</th>
                                <th class="py-3 text-right" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">New Amount</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Type</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Requested By</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Date</th>
                                <th class="py-3 text-center" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adjustment)
                                <tr style="transition: background-color 150ms ease;">
                                    <td class="px-4 py-3">
                                        <div style="font-weight: 600; color: #1f2937;">{{ $adjustment->student->full_name }}</div>
                                        <small style="color: #6b7280;">{{ $adjustment->student->admission_no }}</small>
                                    </td>
                                    <td class="py-3" style="color: #4b5563;">{{ $adjustment->studentFeeAssignment->feeStructure->category->name ?? '-' }}</td>
                                    <td class="py-3 text-right" style="color: #6b7280;">KES {{ number_format($adjustment->original_amount, 2) }}</td>
                                    <td class="py-3 text-right font-weight-bold" style="color: #059669;">KES {{ number_format($adjustment->new_amount, 2) }}</td>
                                    <td class="py-3">
                                        @if($adjustment->adjustment_type == 'waiver')
                                            <span style="background: #ede9fe; color: #6d28d9; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Waiver</span>
                                        @elseif($adjustment->adjustment_type == 'reduction')
                                            <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Reduction</span>
                                        @else
                                            <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Increase</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div style="color: #1f2937;">{{ $adjustment->requestedBy->name ?? 'System' }}</div>
                                    </td>
                                    <td class="py-3" style="color: #6b7280;">{{ $adjustment->created_at->format('d/m/Y') }}</td>
                                    <td class="py-3 text-center">
                                        <div class="d-inline-flex" style="gap: 6px;">
                                            <a href="{{ route('fees.adjustments.show', $adjustment->id) }}" class="btn btn-sm" style="width: 32px; height: 32px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px;" title="Review">
                                                <i class="far fa-eye" style="font-size: 12px;"></i>
                                            </a>
                                            <form action="{{ route('fees.adjustments.approve', $adjustment->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm" style="width: 32px; height: 32px; background: #d1fae5; color: #047857; border: none; border-radius: 8px;" title="Approve">
                                                    <i class="fas fa-check" style="font-size: 12px;"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div style="color: #6b7280;">
                                            <i class="fas fa-check-circle" style="font-size: 48px; color: #d1fae5; margin-bottom: 16px;"></i>
                                            <p style="font-size: 1rem; color: #4b5563;">No pending adjustments</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($adjustments->hasPages())
            <div class="card-footer bg-white" style="border-top: 1px solid #e5e7eb;">
                {{ $adjustments->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>
@endsection
