<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="route-stops-table">
            <thead>
            <tr>
                <th>Route </th>
                <th>Stop Name</th>
                <th>Stop Time</th>
                <th>Sequence</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($routeStops as $routeStop)
                <tr>
                    <td>{{ $routeStop->route_id }}</td>
                    <td>{{ $routeStop->stop_name }}</td>
                    <td>{{ $routeStop->stop_time }}</td>
                    <td>{{ $routeStop->sequence }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['route-stops.destroy', $routeStop->stop_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('route-stops.show', [$routeStop->stop_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('route-stops.edit', [$routeStop->stop_id]) }}"
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
