<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="roles-table">
            <thead>
            <tr>
                <th>Role Name</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>{{ $role->role_name }}</td>
                    <td>{{ $role->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['roles.destroy', $role->role_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('roles.show', [$role->role_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('roles.edit', [$role->role_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $roles])
        </div>
    </div>
</div>
