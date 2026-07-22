@extends('layouts.portal')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Fee Statement — {{ $student->full_name }}</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($assignments->isEmpty())
                        <p class="text-muted p-3">No fee records found.</p>
                    @else
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Fee Category</th>
                                <th>Academic Year</th>
                                <th>Term</th>
                                <th>Total (KES)</th>
                                <th>Paid (KES)</th>
                                <th>Balance (KES)</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $assignment->feeStructure->category->name ?? 'N/A' }}</td>
                                <td>{{ $assignment->feeStructure->academicYear->name ?? 'N/A' }}</td>
                                <td>{{ $assignment->term ?? $assignment->termModel?->name ?? 'N/A' }}</td>
                                <td>{{ number_format($assignment->final_amount, 2) }}</td>
                                <td>{{ number_format($assignment->paid_amount, 2) }}</td>
                                <td>{{ number_format($assignment->balance, 2) }}</td>
                                <td>
                                    @if($assignment->payment_status === 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif($assignment->payment_status === 'partial')
                                        <span class="badge badge-warning">Partial</span>
                                    @else
                                        <span class="badge badge-danger">Unpaid</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('portal.fees.show', $assignment->id) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($assignment->paid_amount > 0)
                                    <a href="{{ route('portal.fees.receipt', $assignment->id) }}" class="btn btn-sm btn-success" title="Download Receipt">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="card-footer">
                        {{ $assignments->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
