<div class="table-responsive">
    <table class="table table-hover" id="users-table">
        <thead>
            <tr>
                <th>User Identity</th>
                <th>Roles</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
                <tr>
                    <td>
                        <span class="user-name">{{ $user->name }}</span>
                        <span class="user-email">{{ $user->email }}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center flex-wrap gap-1">
                            @foreach($user->roles as $role)
                                <span class="badge-role">{{ $role->role_name }}</span>
                            @endforeach
                            @if($user->roles->isEmpty())
                                <span class="text-muted" style="font-size: .75rem;">No roles assigned</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-right align-middle">
                        <div class="d-flex justify-content-end gap-1">
                            <a href="{{ route('users.show', [$user->id]) }}" class="action-btn" title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('users.edit', [$user->id]) }}" class="action-btn" title="Edit User">
                                <i class="far fa-edit"></i>
                            </a>
                            <button type="button" class="action-btn" title="Reset Password"
                                    data-toggle="modal" data-target="#resetPasswordModal"
                                    data-user-id="{{ $user->id }}"
                                    data-user-name="{{ $user->name }}">
                                <i class="fas fa-key"></i>
                            </button>
                            {!! Form::open(['route' => ['users.destroy', $user->id], 'method' => 'delete', 'class' => 'm-0']) !!}
                                {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                    'type' => 'submit',
                                    'class' => 'action-btn btn-delete',
                                    'title' => 'Delete User',
                                    'onclick' => "return confirm('Are you sure you want to delete this user?')"
                                ]) !!}
                            {!! Form::close() !!}
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($users instanceof \Illuminate\Pagination\LengthAwarePaginator || isset($users->links))
    <div class="dash-panel-foot p-3 border-top bg-white">
        <div class="d-flex justify-content-end">
            @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $users->links() }}
            @else
                @include('adminlte-templates::common.paginate', ['records' => $users])
            @endif
        </div>
    </div>
@endif
