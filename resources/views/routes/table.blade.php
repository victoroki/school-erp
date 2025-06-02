<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="routes-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Start Point</th>
                <th>End Point</th>
                <th>Distance</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($routes as $route)
                <tr>
                    <td>{{ $route->name }}</td>
                    <td>{{ $route->description }}</td>
                    <td>{{ $route->start_point }}</td>
                    <td>{{ $route->end_point }}</td>
                    <td>{{ $route->distance }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['routes.destroy', $route->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('routes.show', [$route->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('routes.edit', [$route->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $routes])
        </div>
    </div>
</div>
