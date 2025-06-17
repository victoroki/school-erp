<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="grading-scales-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Min Percentage</th>
                <th>Max Percentage</th>
                <th>Grade Point</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($gradingScales as $gradingScale)
                <tr>
                    <td>{{ $gradingScale->name }}</td>
                    <td>{{ $gradingScale->min_percentage }}</td>
                    <td>{{ $gradingScale->max_percentage }}</td>
                    <td>{{ $gradingScale->grade_point }}</td>
                    <td>{{ $gradingScale->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['grading-scales.destroy', $gradingScale->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('gradingScales.show', [$gradingScale->grade_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('gradingScales.edit', [$grading-scale->grade_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $gradingScales])
        </div>
    </div>
</div>
