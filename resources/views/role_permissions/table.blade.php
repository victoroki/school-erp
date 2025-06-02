<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="role-permissions-table">
            <thead>
            <tr>
                <th>Permission Id</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($rolePermissions as $rolePermission)
                <tr>
                    <td>{{ $rolePermission->permission_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['rolePermissions.destroy', $rolePermission->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('rolePermissions.show', [$rolePermission->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('rolePermissions.edit', [$rolePermission->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $rolePermissions])
        </div>
    </div>
</div>
