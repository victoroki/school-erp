<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="hostel-rooms-table">
            <thead>
            <tr>
                <th>Hostel </th>
                <th>Room Number</th>
                <th>Room Type</th>
                <th>Capacity</th>
                <th>Occupied</th>
                <th>Floor</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostelRooms as $hostelRoom)
                <tr>
                    <td>{{ $hostelRoom->hostel->name ?? 'N/A' }}</td>
                    <td>{{ $hostelRoom->room_number }}</td>
                    <td>{{ $hostelRoom->room_type }}</td>
                    <td>{{ $hostelRoom->capacity }}</td>
                    <td>
                        <div>
                            {{ $hostelRoom->occupied ?? 0 }} / {{ $hostelRoom->capacity }}
                            <div class="progress" style="height: 5px; margin-top: 3px;">
                                @php
                                    $percentage = $hostelRoom->getOccupancyPercentage();
                                    $progressClass = $percentage >= 100 ? 'bg-danger' : ($percentage >= 75 ? 'bg-warning' : 'bg-success');
                                @endphp
                                <div class="progress-bar {{ $progressClass }}" style="width: {{ min($percentage, 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>{{ $hostelRoom->floor }}</td>
                    <td>
                        @if($hostelRoom->status === 'available')
                            <span class="badge badge-success">Available</span>
                        @elseif($hostelRoom->status === 'full')
                            <span class="badge badge-danger">Full</span>
                        @elseif($hostelRoom->status === 'under_maintenance')
                            <span class="badge badge-warning">Maintenance</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($hostelRoom->status) }}</span>
                        @endif
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['hostel-rooms.destroy', $hostelRoom->room_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('hostel-rooms.show', [$hostelRoom->room_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostel-rooms.edit', [$hostelRoom->room_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
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
