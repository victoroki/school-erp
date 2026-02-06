<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped" id="routes-table">
            <thead>
            <tr>
                <th>Route Info</th>
                <th>Vehicle & Driver</th>
                <th>Capacity & Occupancy</th>
                <th>Schedule</th>
                <th>Fee</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($routes as $route)
                <tr>
                    <td>
                        <strong>{{ $route->name }}</strong><br>
                        <small class="text-muted">{{ $route->route_code }} | {{ $route->distance }} km</small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-bus mr-2 text-danger"></i>
                            <div>
                                {{ $route->vehicle_name }} ({{ $route->vehicle_number }})<br>
                                <small><i class="fas fa-user-circle"></i> {{ $route->driver_name }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        @php $percent = $route->getOccupancyPercentage(); @endphp
                        <div class="progress-group">
                            <span class="progress-number"><b>{{ $route->getCurrentOccupancy() }}</b>/{{ $route->vehicle_capacity }}</span>
                            <div class="progress progress-xs">
                                <div class="progress-bar bg-{{ $percent > 90 ? 'danger' : ($percent > 70 ? 'warning' : 'success') }}" 
                                     style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <small>
                            <strong>AM:</strong> {{ $route->morning_start_time }} - {{ $route->morning_end_time }}<br>
                            <strong>PM:</strong> {{ $route->evening_start_time }} - {{ $route->evening_end_time }}
                        </small>
                    </td>
                    <td>{{ number_format($route->route_fee, 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $route->status == 'active' ? 'success' : ($route->status == 'maintenance' ? 'warning' : 'secondary') }}">
                            {{ ucfirst($route->status) }}
                        </span>
                    </td>
                    <td style="width: 120px">
                        {!! Form::open(['route' => ['routes.destroy', $route->route_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('routes.show', [$route->route_id]) }}"
                               class='btn btn-default btn-xs' title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('routes.edit', [$route->route_id]) }}"
                               class='btn btn-default btn-xs' title="Edit Route">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')", 'title' => 'Delete Route']) !!}
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
            @include('adminlte-templates::common.paginate', ['records' => $routes])
        </div>
    </div>
</div>