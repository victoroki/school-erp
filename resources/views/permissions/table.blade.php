@if($permissions->isEmpty())
    <div class="alert alert-info text-center">
        <i class="fas fa-info-circle mr-2"></i> No permissions found. Create some permissions to get started.
    </div>
@else
    <div class="row" id="permissions-grid">
        @foreach($permissions as $groupName => $permissionGroup)
            <div class="col-lg-4 col-md-6 col-sm-12 permission-group" data-group-name="{{ strtolower($groupName) }}">
                <div class="card card-primary card-outline mb-4 h-100">
                    <div class="card-header">
                        <h3 class="card-title text-bold text-capitalize">
                           <i class="fas fa-layer-group text-primary mr-2"></i> {{ str_replace(['-', '_'], ' ', $groupName) }}
                        </h3>
                        <div class="card-tools">
                             <span class="badge badge-primary mr-2">{{ $permissionGroup->count() }}</span>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0" style="max-height: 350px; overflow-y: auto;">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped mb-0 table-sm">
                                <thead class="bg-light sticky-top">
                                <tr>
                                    <th style="width: 100%">Permission</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($permissionGroup as $permission)
                                    <tr class="permission-row" data-permission-name="{{ strtolower($permission->permission_name) }}">
                                        <td class="align-middle" title="{{ $permission->description }}">
                                            <span class="d-block font-weight-bold">{{ $permission->permission_name }}</span>
                                            <small class="text-muted">{{ Str::limit($permission->description, 30) }}</small>
                                        </td>
                                        <!-- Action column removed -->
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
