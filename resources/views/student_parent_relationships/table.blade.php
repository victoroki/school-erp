<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm" id="student-parent-relationships-table">
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
                    <td>{{ optional($studentParentRelationship->student)->first_name }} {{ optional($studentParentRelationship->student)->last_name }}</td>
                    <td>{{ optional($studentParentRelationship->parent)->first_name }} {{ optional($studentParentRelationship->parent)->last_name }}</td>
                    <td>
                        @php $cls = $studentParentRelationship->is_primary_contact ? 'success' : 'secondary'; @endphp
                        <span class="badge badge-{{ $cls }}">{{ $studentParentRelationship->is_primary_contact ? 'Primary' : 'Secondary' }}</span>
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['student-parent-relationships.destroy', $studentParentRelationship->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('student-parent-relationships.show', [$studentParentRelationship->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('student-parent-relationships.edit', [$studentParentRelationship->id]) }}"
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
