<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="class-subjects-table">
            <thead>
            <tr>
                <th>Class Id</th>
                <th>Subject Id</th>
                <th>Academic Year Id</th>
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
                        {!! Form::open(['route' => ['classSubjects.destroy', $classSubject->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('classSubjects.show', [$classSubject->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('classSubjects.edit', [$classSubject->id]) }}"
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
