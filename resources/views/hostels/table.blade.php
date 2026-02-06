<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-striped mb-0" id="hostels-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Address</th>
                <th>Capacity</th>
                <th>Occupied</th>
                <th>Availability</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostels as $hostel)
                @php
                    $occupancy = $hostel->getOccupancyPercentage();
                    $badgeColor = $occupancy >= 100 ? 'danger' : ($occupancy > 70 ? 'warning' : 'success');
                @endphp
                <tr>
                    <td>
                        <strong>{{ $hostel->name }}</strong><br>
                        <small class="text-muted"><i class="fas fa-user-tie mr-1"></i>Warden: {{ $hostel->warden->first_name ?? 'Not Assigned' }}</small>
                    </td>
                    <td>
                        <span class="badge badge-{{ $hostel->type == 'boys' ? 'primary' : ($hostel->type == 'girls' ? 'danger' : 'info') }}">
                            {{ ucfirst($hostel->type) }}
                        </span>
                    </td>
                    <td class="text-truncate" style="max-width: 200px;">{{ $hostel->address }}</td>
                    <td><span class="badge badge-light border">{{ $hostel->capacity }} Beds</span></td>
                    <td><span class="badge badge-{{ $badgeColor }}">{{ $hostel->getCurrentOccupancy() }} Occupied</span></td>
                    <td>
                        <div class="progress progress-xs" style="width: 100px;">
                            <div class="progress-bar bg-{{ $badgeColor }}" role="progressbar" style="width: {{ $occupancy }}%" aria-valuenow="{{ $occupancy }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small>{{ $occupancy }}% Full</small>
                    </td>
                    <td class="text-center">
                        {!! Form::open(['route' => ['hostels.destroy', $hostel->hostel_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('hostels.show', [$hostel->hostel_id]) }}"
                               class='btn btn-outline-info btn-xs' title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostels.edit', [$hostel->hostel_id]) }}"
                               class='btn btn-outline-primary btn-xs' title="Edit Hostel">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-danger btn-xs', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure you want to delete this hostel? This may affect room and allocation data.')"]) !!}
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
            @include('adminlte-templates::common.paginate', ['records' => $hostels])
        </div>
    </div>
</div>
