@if($permissions->isEmpty())
    <div class="dash-alert alert-info">
        <div class="da-icon"><i class="fas fa-info-circle"></i></div>
        <div class="da-body">
            <p class="da-title">No Permissions Found</p>
            <p class="da-desc">Create some permissions to get started managing system access.</p>
        </div>
    </div>
@else
    <div class="row" id="permissions-grid">
        @foreach($permissions as $groupName => $permissionGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 permission-group mb-4" data-group-name="{{ strtolower($groupName) }}">
                <div class="pg-card h-100">
                    <div class="pg-header">
                        <div class="pg-title">
                            <i class="fas fa-layer-group"></i>
                            {{ str_replace(['-', '_'], ' ', $groupName) }}
                            <span class="badge-count ms-2">{{ $permissionGroup->count() }}</span>
                        </div>
                        <button type="button" class="btn btn-link p-0 text-muted pg-toggle">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                    <div class="pg-body">
                        @foreach($permissionGroup as $permission)
                            <div class="pi-item" title="{{ $permission->description }}">
                                <span class="pi-name">{{ $permission->permission_name }}</span>
                                @if($permission->description)
                                    <span class="pi-desc">{{ Str::limit($permission->description, 50) }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
