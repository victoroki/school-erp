<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0" id="hostel-rooms-table">
            <thead>
            <tr>
                <th>Hostel & Floor</th>
                <th>Room Info</th>
                <th>Type</th>
                <th>Occupancy</th>
                <th>Status</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostelRooms as $hostelRoom)
                <tr>
                    <td>
                        <strong>{{ $hostelRoom->hostel->name ?? 'N/A' }}</strong><br>
                        <small class="text-muted"><i class="fas fa-layer-group mr-1"></i>Floor: {{ $hostelRoom->floor ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="text-primary font-weight-bold">Room {{ $hostelRoom->room_number }}</span>
                        @if($hostelRoom->maintenance_notes)
                            <br><small class="text-danger" title="{{ $hostelRoom->maintenance_notes }}"><i class="fas fa-exclamation-triangle mr-1"></i>Maint. Notes</small>
                        @endif
                    </td>
                    <td><span class="badge badge-light border">{{ ucfirst($hostelRoom->room_type) }}</span></td>
                    <td>
                        <div>
                            <small class="font-weight-bold">{{ $hostelRoom->occupied ?? 0 }} / {{ $hostelRoom->capacity }} Beds</small>
                            <div class="progress progress-xs" style="width: 100px; margin-top: 3px;">
                                @php
                                    $percentage = $hostelRoom->getOccupancyPercentage();
                                    $progressClass = $percentage >= 100 ? 'bg-danger' : ($percentage >= 75 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress-bar {{ $progressClass }}" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php
                            $status = $hostelRoom->status;
                            // Check if status should be partial if it's available but has occupants
                            if($status == 'available' && $hostelRoom->occupied > 0 && $hostelRoom->occupied < $hostelRoom->capacity) {
                                $status = 'partial';
                            }
                        @endphp

                        @if($status === 'available')
                            <span class="badge badge-success px-2">Available</span>
                        @elseif($status === 'partial')
                            <span class="badge badge-warning px-2">Partial</span>
                        @elseif($status === 'full')
                            <span class="badge badge-danger px-2">Full</span>
                        @elseif($status === 'under_maintenance')
                            <span class="badge badge-secondary px-2"><i class="fas fa-tools mr-1"></i>Maintenance</span>
                        @else
                            <span class="badge badge-info px-2">{{ ucfirst($status) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {!! Form::open(['route' => ['hostel-rooms.destroy', $hostelRoom->room_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            @if($hostelRoom->status !== 'full' && $hostelRoom->status !== 'under_maintenance')
                                <a href="{{ route('hostel-allocations.create', ['room_id' => $hostelRoom->room_id]) }}"
                                   class='btn btn-outline-success btn-xs' title="Quick Allocate">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                            @endif
                            <a href="{{ route('hostel-rooms.show', [$hostelRoom->room_id]) }}"
                               class='btn btn-outline-info btn-xs' title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostel-rooms.edit', [$hostelRoom->room_id]) }}"
                               class='btn btn-outline-primary btn-xs' title="Edit Room">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-danger btn-xs', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure you want to delete this room?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $hostelRooms])
        </div>
    </div>
</div>
