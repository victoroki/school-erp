<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped" id="route-stops-table">
            <thead>
            <tr>
                <th>Stop Info</th>
                <th>Route</th>
                <th>Sequence</th>
                <th>Est. Time</th>
                <th>Fee</th>
                <th>Students</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($routeStops as $routeStop)
                <tr>
                    <td>
                        <strong>{{ $routeStop->stop_name }}</strong><br>
                        <small class="text-muted"><i class="fas fa-map-marker-alt"></i> {{ $routeStop->landmark }}</small>
                    </td>
                    <td><span class="badge badge-info">{{ $routeStop->route->name ?? 'N/A' }}</span></td>
                    <td><span class="badge badge-secondary">{{ $routeStop->sequence }}</span></td>
                    <td>{{ $routeStop->stop_time }}</td>
                    <td>{{ number_format($routeStop->stop_fee, 2) }}</td>
                    <td><span class="badge badge-primary">{{ $routeStop->getStudentCount() }}</span></td>
                    <td>
                        <span class="badge badge-{{ $routeStop->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($routeStop->status) }}
                        </span>
                    </td>
                    <td style="width: 120px">
                        {!! Form::open(['route' => ['routeStops.destroy', $routeStop->stop_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('routeStops.show', [$routeStop->stop_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('routeStops.edit', [$routeStop->stop_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $routeStops])
        </div>
    </div>
</div>
