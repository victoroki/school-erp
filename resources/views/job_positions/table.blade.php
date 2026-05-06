<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="job-positions-table">
        <thead class="bg-light border-bottom">
            <tr>
                <th class="ps-4">Title & Description</th>
                <th>Department</th>
                <th>Status</th>
                <th class="text-end pe-4" style="width: 150px">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jobPositions as $jobPosition)
                <tr>
                    <td class="ps-4 py-3">
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark mb-1">{{ $jobPosition->title }}</span>
                            <small class="text-muted text-truncate" style="max-width: 300px;">{{ $jobPosition->description }}</small>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-indigo-light text-indigo border-indigo-subtle px-2 py-1">
                            {{ $jobPosition->department->name ?? 'Unassigned' }}
                        </span>
                    </td>
                    <td>
                        @if($jobPosition->is_active)
                            <span class="badge bg-emerald-light text-emerald px-2 py-1">Active</span>
                        @else
                            <span class="badge bg-slate-light text-slate px-2 py-1">Inactive</span>
                        @endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="dropdown">
                            <button class="btn btn-icon-dash" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('job-positions.show', [$jobPosition->position_id]) }}">
                                        <i class="far fa-eye me-2 text-indigo"></i> View Details
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('job-positions.edit', [$jobPosition->position_id]) }}">
                                        <i class="far fa-edit me-2 text-amber"></i> Edit Role
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    {!! Form::open(['route' => ['job-positions.destroy', $jobPosition->position_id], 'method' => 'delete']) !!}
                                    {!! Form::button('<i class="far fa-trash-alt me-2"></i> Delete Position', [
                                        'type' => 'submit', 
                                        'class' => 'dropdown-item text-danger py-2', 
                                        'onclick' => "return confirm('Are you sure? All associated records will be affected.')"
                                    ]) !!}
                                    {!! Form::close() !!}
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="dash-panel-footer border-top bg-light p-3">
    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted font-weight-500">
            Showing {{ $jobPositions->firstItem() }} to {{ $jobPositions->lastItem() }} of {{ $jobPositions->total() }} roles
        </small>
        <div class="pagination-impeccable">
            {{ $jobPositions->links() }}
        </div>
    </div>
</div>

<style>
.table thead th { font-size: 0.75rem; font-weight: 750; color: var(--slate); text-transform: uppercase; letter-spacing: 0.05em; padding: 1rem 0.5rem; }
.table tbody td { font-size: 0.875rem; vertical-align: middle; }
.btn-icon-dash { width: 32px; height: 32px; border-radius: 8px; border: none; background: transparent; color: var(--slate); display: inline-flex; align-items: center; justify-content: center; transition: all 200ms var(--ease-out); }
.btn-icon-dash:hover { background: var(--slate-light); color: var(--text); }
.bg-indigo-light { background-color: #eef2ff; color: #4f46e5; }
.text-indigo { color: #4f46e5; }
.bg-emerald-light { background-color: #ecfdf5; color: #10b981; }
.text-emerald { color: #10b981; }
.bg-slate-light { background-color: #f1f5f9; color: #64748b; }
.text-slate { color: #64748b; }
.dropdown-item { font-size: 0.813rem; font-weight: 600; border-radius: 6px; margin: 0 4px; width: calc(100% - 8px); transition: all 150ms ease; }
.dropdown-item:hover { background: #f8fafc; color: var(--indigo); }
.pagination-impeccable .pagination { margin-bottom: 0; gap: 4px; }
.pagination-impeccable .page-link { border: none; border-radius: 6px; font-weight: 700; color: var(--slate); padding: 0.5rem 0.75rem; }
.pagination-impeccable .page-item.active .page-link { background: var(--indigo); color: #fff; }
</style>
