<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0" id="hostel-allocations-table">
            <thead>
            <tr>
                <th>Student</th>
                <th>Hostel & Room</th>
                <th>Bed</th>
                <th>Dates</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostelAllocations as $hostelAllocation)
                <tr>
                    <td>
                        <strong>{{ $hostelAllocation->student->first_name ?? 'N/A' }} {{ $hostelAllocation->student->last_name ?? '' }}</strong><br>
                        <small class="text-muted"><i class="fas fa-id-card mr-1"></i>{{ $hostelAllocation->student->student_id ?? 'N/A' }}</small>
                    </td>
                    <td>
                        {{ $hostelAllocation->hostel->name ?? 'N/A' }}<br>
                        <small class="badge badge-light border">Room: {{ $hostelAllocation->room->room_number ?? 'N/A' }}</small>
                    </td>
                    <td><span class="badge badge-light border">{{ $hostelAllocation->bed_number ?? 'N/A' }}</span></td>
                    <td>
                        <small><strong>Allot:</strong> {{ $hostelAllocation->allocation_date->format('d M, Y') }}</small>
                        @if($hostelAllocation->vacating_date)
                            <br><small><strong>Vacate:</strong> {{ $hostelAllocation->vacating_date->format('d M, Y') }}</small>
                        @endif
                    </td>
                    <td>
                        @if($hostelAllocation->status === 'active')
                            <span class="badge badge-success px-2">Active</span>
                        @elseif($hostelAllocation->status === 'vacated')
                            <span class="badge badge-danger px-2">Vacated</span>
                        @elseif($hostelAllocation->status === 'pending')
                            <span class="badge badge-warning px-2">Pending</span>
                        @else
                            <span class="badge badge-secondary px-2">{{ ucfirst($hostelAllocation->status) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {!! Form::open(['route' => ['hostel-allocations.destroy', $hostelAllocation->allocation_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            @if($hostelAllocation->status === 'active')
                                <button type="button" class="btn btn-outline-warning btn-xs" 
                                        data-toggle="modal" data-target="#checkoutModal{{ $hostelAllocation->allocation_id }}" 
                                        title="Checkout Student">
                                    <i class="fas fa-sign-out-alt"></i>
                                </button>
                                <a href="{{ route('hostel-allocations.transfer-form', [$hostelAllocation->allocation_id]) }}"
                                   class='btn btn-outline-info btn-xs' title="Transfer Room">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>
                            @endif
                            <a href="{{ route('hostel-allocations.show', [$hostelAllocation->allocation_id]) }}"
                               class='btn btn-outline-primary btn-xs' title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostel-allocations.edit', [$hostelAllocation->allocation_id]) }}"
                               class='btn btn-outline-secondary btn-xs' title="Edit">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-danger btn-xs', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure you want to delete this allocation?')"]) !!}
                        </div>
                        {!! Form::close() !!}

                        @if($hostelAllocation->status === 'active')
                            <!-- Checkout Modal -->
                            <div class="modal fade" id="checkoutModal{{ $hostelAllocation->allocation_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        {!! Form::open(['route' => ['hostel-allocations.checkout', $hostelAllocation->allocation_id]]) !!}
                                        <div class="modal-header">
                                            <h5 class="modal-title">Checkout Student: {{ $hostelAllocation->student->first_name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-left">
                                            <div class="form-group font-weight-normal">
                                                {!! Form::label('checkout_notes', 'Checkout Notes:') !!}
                                                {!! Form::textarea('checkout_notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Reason for vacating, damages etc.']) !!}
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger">Confirm Checkout</button>
                                        </div>
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $hostelAllocations])
        </div>
    </div>
</div>
