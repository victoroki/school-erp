<div class="card-body p-0">
    @if($parents->isEmpty())
        <div class="card-body text-center py-5">
            <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No guardians found</h5>
            @if(request()->filled('q') || request()->filled('relationship') || request()->filled('class_id'))
                <p class="text-muted small mb-3">No guardian matches the current filters.</p>
                <a href="{{ route('parents.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-redo mr-1"></i> Clear filters
                </a>
            @else
                <p class="text-muted small mb-3">Add the first guardian to start recording parent and learner links.</p>
                <a href="{{ route('parents.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-user-plus mr-1"></i> Add guardian
                </a>
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="parents-table">
                <thead class="bg-light">
                <tr>
                    <th>Parent / Guardian</th>
                    <th>Relationship</th>
                    <th>Contact</th>
                    <th>Linked Students</th>
                    <th>Occupation</th>
                    <th class="text-right pr-4">Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($parents as $parent)
                    <tr>
                        <td>
                            <div class="font-weight-bold">{{ $parent->full_name }}</div>
                            @if($parent->user)
                                <div class="small text-muted">
                                    <i class="fas fa-id-badge mr-1"></i> Portal account linked
                                </div>
                            @endif
                        </td>
                        <td class="text-capitalize">{{ $parent->relationship ?? '—' }}</td>
                        <td>
                            <div><i class="fas fa-envelope mr-1 text-muted small"></i> {{ $parent->email ?? 'N/A' }}</div>
                            <div class="small text-muted">
                                <i class="fas fa-phone mr-1 text-success"></i> {{ $parent->formatted_phone }}
                                @if($parent->alternate_phone)
                                    &nbsp;/&nbsp;{{ $parent->formatted_alternate_phone }}
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($parent->students_count > 0)
                                <span class="badge badge-info">{{ $parent->students_count }}</span>
                                <span class="small text-muted ml-1">
                                    {{ \Illuminate\Support\Str::plural('student', $parent->students_count) }}
                                </span>
                            @else
                                <span class="badge badge-secondary">Not linked</span>
                            @endif
                        </td>
                        <td>{{ $parent->occupation ?? 'N/A' }}</td>
                        <td class="text-right pr-4">
                            <div class='btn-group shadow-sm'>
                                <a href="{{ route('parents.show', [$parent->parent_id]) }}"
                                   class='btn btn-light btn-sm border' title="View profile">
                                    <i class="far fa-eye text-primary"></i>
                                </a>
                                <a href="{{ route('parents.edit', [$parent->parent_id]) }}"
                                   class='btn btn-light btn-sm border' title="Edit guardian">
                                    <i class="far fa-edit text-secondary"></i>
                                </a>
                                <button type="button" class="btn btn-light btn-sm border text-danger"
                                        title="Delete guardian"
                                        onclick="if (confirm('Delete {{ $parent->full_name }}?\n\nGuardians who are the primary or only guardian of an active learner are kept and you will be told which learners are blocking the delete.')) { document.getElementById('delete-parent-{{ $parent->parent_id }}').submit(); }">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                            <form id="delete-parent-{{ $parent->parent_id }}" action="{{ route('parents.destroy', $parent->parent_id) }}" method="POST" style="display:none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center">
            <div class="small text-muted">
                Showing {{ $parents->firstItem() }} to {{ $parents->lastItem() }} of {{ $parents->total() }} guardians
            </div>
            <div>
                {{ $parents->links() }}
            </div>
        </div>
    @endif
</div>
