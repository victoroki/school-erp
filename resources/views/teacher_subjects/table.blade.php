<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="teacher-subjects-table">
            <thead>
            <tr>
                <th>Staff </th>
                <th>Subject</th>
                <th>Class Section</th>
                <th>Academic Year</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($teacherSubjects as $teacherSubject)
                <tr>
                    <td>{{ $teacherSubject->staff_id }}</td>
                    <td>{{ $teacherSubject->subject_id }}</td>
                    <td>{{ $teacherSubject->class_section_id }}</td>
                    <td>{{ $teacherSubject->academic_year_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['teacherSubjects.destroy', $teacherSubject->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('teacherSubjects.show', [$teacherSubject->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('teacherSubjects.edit', [$teacherSubject->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $teacherSubjects])
        </div>
    </div>
</div>
