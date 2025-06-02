<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="exams-table">
            <thead>
            <tr>
                <th>Exam Type Id</th>
                <th>Name</th>
                <th>Description</th>
                <th>Academic Year Id</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Publish Result</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($exams as $exam)
                <tr>
                    <td>{{ $exam->exam_type_id }}</td>
                    <td>{{ $exam->name }}</td>
                    <td>{{ $exam->description }}</td>
                    <td>{{ $exam->academic_year_id }}</td>
                    <td>{{ $exam->start_date }}</td>
                    <td>{{ $exam->end_date }}</td>
                    <td>{{ $exam->publish_result }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['exams.destroy', $exam->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exams.show', [$exam->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('exams.edit', [$exam->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $exams])
        </div>
    </div>
</div>
