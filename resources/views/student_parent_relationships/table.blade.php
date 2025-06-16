<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="student-parent-relationships-table">
            <thead>
            <tr>
                <th>Student</th>
                <th>Parent</th>
                <th>Is Primary Contact</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentParentRelationships as $studentParentRelationship)
                <tr>
                    <td>{{ $studentParentRelationship->student_id }}</td>
                    <td>{{ $studentParentRelationship->parent_id }}</td>
                    <td>{{ $studentParentRelationship->is_primary_contact }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['studentParentRelationships.destroy', $studentParentRelationship->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('studentParentRelationships.show', [$studentParentRelationship->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('studentParentRelationships.edit', [$studentParentRelationship->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $studentParentRelationships])
        </div>
    </div>
</div>
