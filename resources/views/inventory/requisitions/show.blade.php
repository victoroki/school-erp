@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-invoice text-info mr-2"></i>Requisition: {{ $requisition->requisition_number }}</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('inventory.requisitions.index') }}" class="btn btn-default shadow-sm border">
                        <i class="fas fa-chevron-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="font-weight-bold mb-0">Requested Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light small uppercase">
                                    <tr>
                                        <th class="pl-4">Item Name</th>
                                        <th>Quantity</th>
                                        <th>Unit Cost</th>
                                        <th class="text-right pr-4">Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requisition->items as $item)
                                        <tr>
                                            <td class="pl-4 font-weight-bold">{{ $item->item->name }}</td>
                                            <td>{{ $item->quantity_requested }} {{ $item->item->unit }}</td>
                                            <td>KES {{ number_format($item->unit_price, 2) }}</td>
                                            <td class="text-right pr-4 font-weight-bold">KES {{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="3" class="text-right font-weight-bold">Grand Total</td>
                                        <td class="text-right pr-4 text-primary font-weight-bold h5">KES {{ number_format($requisition->total_cost, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="font-weight-bold mb-0">Justification / Remarks</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-muted italic">"{{ $requisition->justification }}"</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="font-weight-bold mb-0">Details</h6>
                    </div>
                    <div class="card-body py-1">
                        <ul class="list-group list-group-unbordered mb-0">
                            <li class="list-group-item d-flex justify-content-between border-top-0 px-0">
                                <b class="text-muted small">Status</b>
                                <span>{!! $requisition->status_badge !!}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <b class="text-muted small">Requested By</b>
                                <span>{{ $requisition->requestedBy->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <b class="text-muted small">Department</b>
                                <span>{{ $requisition->department->name }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <b class="text-muted small">Needed By</b>
                                <span>{{ $requisition->date_needed->format('d M Y') }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between px-0 border-bottom-0">
                                <b class="text-muted small">Priority</b>
                                <span class="badge {{ $requisition->priority == 'Urgent' ? 'badge-danger' : 'badge-light border' }}">{{ $requisition->priority }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                @if($requisition->status == 'Pending' && auth()->user()->hasRole('Admin'))
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-header bg-light border-bottom-0">
                            <h6 class="font-weight-bold mb-0">Approval Actions</h6>
                        </div>
                        <div class="card-body pt-0">
                            <form action="{{ route('inventory.requisitions.approve', $requisition->requisition_id) }}" method="POST">
                                @csrf
                                <div class="form-group mb-3">
                                    <textarea name="reason" class="form-control form-control-sm" placeholder="Reason for approval/rejection (Optional)" rows="2"></textarea>
                                </div>
                                <div class="d-flex">
                                    <button type="submit" name="action" value="approve" class="btn btn-success btn-block mr-2 shadow-sm">
                                        <i class="fas fa-check mr-1"></i> Approve
                                    </button>
                                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-block mt-0 shadow-sm">
                                        <i class="fas fa-times mr-1"></i> Reject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                @elseif($requisition->status == 'Approved' || $requisition->status == 'Rejected')
                    <div class="card border-0 shadow-sm border-left border-info">
                        <div class="card-body py-3">
                            <div class="small text-muted mb-1">{{ $requisition->status }} by:</div>
                            <div class="font-weight-bold mb-1">{{ $requisition->approvedBy->name ?? 'System' }}</div>
                            <div class="small text-muted">{{ $requisition->approved_date ? $requisition->approved_date->format('d M Y H:i') : '' }}</div>
                            @if($requisition->rejected_reason)
                                <hr class="my-2">
                                <div class="small font-weight-bold text-danger">Reason:</div>
                                <p class="small mb-0">{{ $requisition->rejected_reason }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <style>
        .list-group-item { padding: 0.75rem 0; border-left: 0; border-right: 0; }
    </style>
@endsection
