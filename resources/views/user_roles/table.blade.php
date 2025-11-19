<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="user-roles-table">
            <thead>
            <tr>
                <th>Role </th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($userRoles as $userRole)
                <tr>
                    <td>{{ $userRole->user->name }} - {{ $userRole->role->role_name }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['user-roles.destroy', $userRole->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('user-roles.show', [$userRole->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('user-roles.edit', [$userRole->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $userRoles])
        </div>
    </div>
</div>
