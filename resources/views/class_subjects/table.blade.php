<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="class-subjects-table">
            <thead>
            <tr>
                <th>Class </th>
                <th>Subject </th>
                <th>Academic Year </th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($classSubjects as $classSubject)
                <tr>
                    <td>{{ $classSubject->class_id }}</td>
                    <td>{{ $classSubject->subject_id }}</td>
                    <td>{{ $classSubject->academic_year_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['class-subjects.destroy', $classSubject->class_subject_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('class-subjects.show', [$classSubject->class_subject_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('class-subjects.edit', [$classSubject->class_subject_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $classSubjects])
        </div>
    </div>
</div>
