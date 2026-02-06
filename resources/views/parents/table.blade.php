<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle" id="parents-table">
            <thead class="bg-light">
            <tr>
                <th>Parent Name</th>
                <th>Relationship</th>
                <th>Email</th>
                <th>Phone Numbers</th>
                <th>Occupation</th>
                <th class="text-right pr-4">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($parents as $parent)
                <tr>
                    <td>
                        <div class="font-weight-bold">{{ $parent->full_name }}</div>
                    </td>
                    <td class="text-capitalize">{{ $parent->relationship }}</td>
                    <td>{{ $parent->email ?? 'N/A' }}</td>
                    <td>
                        <div><i class="fas fa-phone mr-1 text-success small"></i> {{ $parent->formatted_phone }}</div>
                        @if($parent->alternate_phone)
                            <div class="small text-muted"><i class="fas fa-phone mr-1 small"></i> {{ $parent->formatted_alternate_phone }}</div>
                        @endif
                    </td>
                    <td>{{ $parent->occupation ?? 'N/A' }}</td>
                    <td class="text-right pr-4">
                        <div class='btn-group shadow-sm'>
                            <a href="{{ route('parents.show', [$parent->parent_id]) }}"
                               class='btn btn-light btn-sm border' title="View">
                                <i class="far fa-eye text-primary"></i>
                            </a>
                            <a href="{{ route('parents.edit', [$parent->parent_id]) }}"
                               class='btn btn-light btn-sm border' title="Edit">
                                <i class="far fa-edit text-secondary"></i>
                            </a>
                            <button type="button" class="btn btn-light btn-sm border text-danger" 
                                    onclick="if(confirm('Are you sure?')) document.getElementById('delete-parent-{{ $parent->parent_id }}').submit()" title="Delete">
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

    <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center">
        <div class="small text-muted">
            Showing {{ $parents->firstItem() }} to {{ $parents->lastItem() }} of {{ $parents->total() }} parents
        </div>
        <div>
            {{ $parents->links() }}
        </div>
    </div>
</div>
<style>

</style>