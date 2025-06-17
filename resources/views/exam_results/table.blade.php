<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="exam-results-table">
            <thead>
            <tr>
                <th>Exam </th>
                <th>Student </th>
                <th>Class Section </th>
                <th>Subject </th>
                <th>Marks Obtained</th>
                <th>Grade </th>
                <th>Remarks</th>
                <th>Created By</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($examResults as $examResult)
                <tr>
                    <td>{{ $examResult->exam_id }}</td>
                    <td>{{ $examResult->student_id }}</td>
                    <td>{{ $examResult->class_section_id }}</td>
                    <td>{{ $examResult->subject_id }}</td>
                    <td>{{ $examResult->marks_obtained }}</td>
                    <td>{{ $examResult->grade_id }}</td>
                    <td>{{ $examResult->remarks }}</td>
                    <td>{{ $examResult->created_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['exam-results.destroy', $examResult->result_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exam-results.show', [$examResult->result_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('exam-results.edit', [$examResult->result_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $examResults])
        </div>
    </div>
</div>
