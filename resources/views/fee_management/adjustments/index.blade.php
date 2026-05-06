@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Adjustments</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-sm-right" href="{{ route('fees.adjustments.create') }}" style="
                        background: linear-gradient(135deg, #0073e7 0%, #0056b3 100%);
                        border: none;
                        border-radius: 8px;
                        padding: 8px 16px;
                        font-weight: 500;
                    ">
                        <i class="fas fa-plus mr-1"></i> New Adjustment
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="row mb-4 g-3">
            <div class="col-lg-3 col-6">
                <div class="p-3" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 12px; border: 1px solid #bfdbfe;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Total</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #1e40af;">{{ $adjustments->total() }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sliders-h" style="color: #0073e7; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="p-3" style="background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); border-radius: 12px; border: 1px solid #fde68a;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Pending</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #b45309;">{{ $adjustments->where('status', 'pending')->count() }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-clock" style="color: #f59e0b; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="p-3" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border-radius: 12px; border: 1px solid #a7f3d0;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Approved</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #047857;">{{ $adjustments->where('status', 'approved')->count() }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="p-3" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-radius: 12px; border: 1px solid #fecaca;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase;">Rejected</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #b91c1c;">{{ $adjustments->where('status', 'rejected')->count() }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-times-circle" style="color: #ef4444; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 20px;">
                <form action="{{ route('fees.adjustments.index') }}" method="GET" class="d-flex align-items-center" style="gap: 8px;">
                    <select name="status" class="form-control form-control-sm" style="width: 140px; border-radius: 8px; border: 1px solid #d1d5db;">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <select name="adjustment_type" class="form-control form-control-sm" style="width: 140px; border-radius: 8px; border: 1px solid #d1d5db;">
                        <option value="">All Types</option>
                        <option value="reduction" {{ request('adjustment_type') == 'reduction' ? 'selected' : '' }}>Reduction</option>
                        <option value="increase" {{ request('adjustment_type') == 'increase' ? 'selected' : '' }}>Increase</option>
                        <option value="waiver" {{ request('adjustment_type') == 'waiver' ? 'selected' : '' }}>Waiver</option>
                    </select>
                    <select name="student_id" class="form-control form-control-sm" style="width: 200px; border-radius: 8px; border: 1px solid #d1d5db;">
                        <option value="">All Students</option>
                        @foreach($students as $id => $name)
                            <option value="{{ $id }}" {{ request('student_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit" style="border-radius: 8px;">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-light" style="border-bottom: 2px solid #e5e7eb;">
                            <tr>
                                <th class="px-4 py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Student</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Fee Category</th>
                                <th class="py-3 text-right" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Original</th>
                                <th class="py-3 text-right" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">New Amount</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Type</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Status</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Requested</th>
                                <th class="py-3 text-center" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adjustments as $adjustment)
                                <tr style="transition: background-color 150ms ease;">
                                    <td class="px-4 py-3">
                                        <div class="font-weight-semibold" style="color: #1f2937;">{{ $adjustment->student->full_name }}</div>
                                        <small style="color: #6b7280;">{{ $adjustment->student->admission_no }}</small>
                                    </td>
                                    <td class="py-3" style="color: #4b5563;">{{ $adjustment->studentFeeAssignment->feeStructure->category->name ?? '-' }}</td>
                                    <td class="py-3 text-right" style="color: #6b7280;">{{ number_format($adjustment->original_amount, 2) }}</td>
                                    <td class="py-3 text-right font-weight-bold" style="color: #059669;">{{ number_format($adjustment->new_amount, 2) }}</td>
                                    <td class="py-3">
                                        @if($adjustment->adjustment_type == 'reduction')
                                            <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Reduction</span>
                                        @elseif($adjustment->adjustment_type == 'increase')
                                            <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Increase</span>
                                        @else
                                            <span style="background: #ede9fe; color: #6d28d9; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">Waiver</span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        @if($adjustment->status == 'pending')
                                            <span style="background: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">
                                                <i class="fas fa-clock mr-1" style="font-size: 10px;"></i> Pending
                                            </span>
                                        @elseif($adjustment->status == 'approved')
                                            <span style="background: #d1fae5; color: #047857; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">
                                                <i class="fas fa-check mr-1" style="font-size: 10px;"></i> Approved
                                            </span>
                                        @else
                                            <span style="background: #fee2e2; color: #b91c1c; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem;">
                                                <i class="fas fa-times mr-1" style="font-size: 10px;"></i> Rejected
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3">
                                        <div style="color: #1f2937; font-size: 0.875rem;">{{ $adjustment->requested_at->format('d/m/Y') }}</div>
                                        <small style="color: #6b7280;">by {{ $adjustment->requestedBy->name ?? 'System' }}</small>
                                    </td>
                                    <td class="py-3 text-center">
                                        <a href="{{ route('fees.adjustments.show', $adjustment->id) }}" 
                                           class="btn btn-sm" style="width: 32px; height: 32px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px;"
                                           title="View">
                                            <i class="far fa-eye" style="font-size: 12px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div style="color: #6b7280;">
                                            <i class="fas fa-sliders-h" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                                            <p class="mb-2" style="font-size: 1rem; color: #4b5563;">No adjustments found</p>
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
