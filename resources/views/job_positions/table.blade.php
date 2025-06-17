<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="job-positions-table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Department </th>
                <th>Description</th>
                <th>Responsibilities</th>
                <th>Qualifications</th>
                <th>Is Active</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($jobPositions as $jobPosition)
                <tr>
                    <td>{{ $jobPosition->title }}</td>
                    <td>{{ $jobPosition->department_id }}</td>
                    <td>{{ $jobPosition->description }}</td>
                    <td>{{ $jobPosition->responsibilities }}</td>
                    <td>{{ $jobPosition->qualifications }}</td>
                    <td>{{ $jobPosition->is_active }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['job-positions.destroy', $jobPosition->position_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('job-positions.show', [$jobPosition->position_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('job-positions.edit', [$jobPosition->position_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $jobPositions])
        </div>
    </div>
</div>
