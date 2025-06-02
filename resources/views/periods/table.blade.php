<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="periods-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($periods as $period)
                <tr>
                    <td>{{ $period->name }}</td>
                    <td>{{ $period->start_time }}</td>
                    <td>{{ $period->end_time }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['periods.destroy', $period->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('periods.show', [$period->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('periods.edit', [$period->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $periods])
        </div>
    </div>
</div>
