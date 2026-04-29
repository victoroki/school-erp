<div class="table-responsive">
    <table class="table table-hover" id="roles-table">
        <thead>
            <tr>
                <th>Role Identity</th>
                <th>Assigned Access</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>
                        <span class="role-name">{{ $role->role_name }}</span>
                        <span class="role-desc">{{ $role->description ?? 'No description provided' }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge-perm">
                                <i class="fas fa-key me-1"></i>
                                {{ $role->permissions->count() }} Permissions
                            </span>
                        </div>
                    </td>
                    <td class="text-right align-middle">
                        {!! Form::open(['route' => ['roles.destroy', $role->role_id], 'method' => 'delete', 'class' => 'm-0 d-flex justify-content-end gap-1']) !!}
                        <a href="{{ route('roles.show', [$role->role_id]) }}" class="action-btn" title="View Details">
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('roles.edit', [$role->role_id]) }}" class="action-btn" title="Edit Role">
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', [
                            'type' => 'submit', 
                            'class' => 'action-btn btn-delete', 
                            'title' => 'Delete Role',
                            'onclick' => "return confirm('Are you sure you want to delete this role?')"
                        ]) !!}
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($roles instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="dash-panel-foot p-3 border-top bg-white">
        <div class="d-flex justify-content-end">
            {{ $roles->appends(request()->query())->links() }}
        </div>
    </div>
@endif
