<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0" id="hostel-allocations-table">
            <thead>
            <tr>
                <th>Student</th>
                <th>Class / Stream</th>
                <th>Hostel &amp; Room</th>
                <th>Bed</th>
                <th>Dates</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($hostelAllocations as $hostelAllocation)
                <tr class="{{ $hostelAllocation->status !== 'active' ? 'text-muted' : '' }}">
                    <td>
                        <strong>{{ optional($hostelAllocation->student)->first_name ?? 'N/A' }} {{ optional($hostelAllocation->student)->last_name ?? '' }}</strong><br>
                        <small class="text-muted"><i class="fas fa-id-card mr-1"></i>{{ optional($hostelAllocation->student)->admission_no ?? 'N/A' }}</small>
                    </td>
                    <td><small>{{ $hostelAllocation->class_info }}</small></td>
                    <td>
                        {{ optional($hostelAllocation->hostel)->name ?? 'N/A' }}<br>
                        <small class="badge badge-light border">Room: {{ optional($hostelAllocation->room)->room_number ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="badge badge-light border">{{ $hostelAllocation->bed_number ?? '—' }}</span>
                    </td>
                    <td>
                        <small><strong>Allot:</strong> {{ optional($hostelAllocation->allocation_date)->format('d M, Y') }}</small>
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
                                <a href="{{ route('hostel-allocations.transfer-form', $hostelAllocation->allocation_id) }}"
                                   class='btn btn-outline-info btn-xs' title="Transfer Room">
                                    <i class="fas fa-exchange-alt"></i>
                                </a>
                            @endif
                            <a href="{{ route('hostel-allocations.show', $hostelAllocation->allocation_id) }}"
                               class='btn btn-outline-primary btn-xs' title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostel-allocations.edit', $hostelAllocation->allocation_id) }}"
                               class='btn btn-outline-secondary btn-xs' title="Edit">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-danger btn-xs', 'title' => 'Delete', 'onclick' => "return confirm('Delete this allocation record? The bed will be freed if it is still active.')"]) !!}
                        </div>
                        {!! Form::close() !!}

                        @if($hostelAllocation->status === 'active')
                            <!-- Checkout Modal -->
                            <div class="modal fade" id="checkoutModal{{ $hostelAllocation->allocation_id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        {!! Form::open(['route' => ['hostel-allocations.checkout', $hostelAllocation->allocation_id]]) !!}
                                        <div class="modal-header">
                                            <h5 class="modal-title">Checkout {{ optional($hostelAllocation->student)->first_name ?? 'Student' }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-left">
                                            <p class="mb-2">
                                                Room <strong>{{ optional($hostelAllocation->room)->room_number ?? 'N/A' }}</strong>,
                                                bed <strong>{{ $hostelAllocation->bed_number ?? '—' }}</strong>.
                                                This frees the bed and marks the allocation vacated.
                                            </p>
                                            <div class="form-group font-weight-normal mb-0">
                                                {!! Form::label('checkout_notes', 'Checkout Notes:') !!}
                                                {!! Form::textarea('checkout_notes', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Reason for vacating, damages, keys returned...']) !!}
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
            @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <i class="fas fa-bed fa-2x text-muted mb-2 d-block"></i>
                        <strong>No allocations found</strong>
                        <p class="text-muted small mb-2">
                            @php
                                $hasFilters = request()->filled('search')
                                    || request()->filled('hostel_id')
                                    || request()->filled('class_id')
                                    || request()->filled('section_id')
                                    || request()->filled('status')
                                    || request()->filled('academic_year_id');
                            @endphp
                            @if($hasFilters)
                                No allocation matches the filters. Try widening the search or reset them.
                            @else
                                No student has been given a bed yet.
                            @endif
                        </p>
                        <a href="{{ route('hostel-allocations.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus mr-1"></i> Allocate a Bed
                        </a>
                        <a href="{{ route('hostel-allocations.bulk-form') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-users mr-1"></i> Bulk Allocation
                        </a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($hostelAllocations->hasPages())
        <div class="card-footer clearfix">
            <div class="float-left text-muted small pt-2">
                Showing {{ $hostelAllocations->firstItem() }}–{{ $hostelAllocations->lastItem() }}
                of {{ $hostelAllocations->total() }}
            </div>
            <div class="float-right">
                @include('adminlte-templates::common.paginate', ['records' => $hostelAllocations])
            </div>
        </div>
    @endif
</div>
