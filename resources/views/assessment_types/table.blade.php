<div class="table-responsive">
    <table class="table table-hover" id="assessment-types-table">
        <thead>
        <tr>
            <th class="pl-4">Name</th>
            <th>Code</th>
            <th>Max Marks</th>
            <th>Weightage</th>
            <th>CBC?</th>
            <th>Status</th>
            <th class="text-right pr-4">Actions</th>
        </tr>
        </thead>
        <tbody>
        @foreach($assessmentTypes as $type)
            <tr>
                <td class="pl-4 font-weight-bold">{{ $type->name }}</td>
                <td><code class="text-danger">{{ $type->code }}</code></td>
                <td>{{ $type->max_marks }}</td>
                <td><span class="badge badge-info px-2 py-1">{{ $type->weightage }}%</span></td>
                <td>
                    @if($type->is_cbc)
                        <span class="badge badge-success px-2 py-1">Yes</span>
                    @else
                        <span class="text-muted small">No</span>
                    @endif
                </td>
                <td>
                    @if($type->status)
                        <span class="badge badge-success px-2 py-1">Active</span>
                    @else
                        <span class="badge badge-secondary px-2 py-1">Inactive</span>
                    @endif
                </td>
                <td class="text-right pr-4">
                    <div class='btn-group'>
                        <a href="{{ route('assessment-types.edit', [$type->id]) }}"
                           class='btn btn-light btn-sm shadow-sm' title="Edit">
                            <i class="far fa-edit text-primary"></i>
                        </a>
                        {!! Form::open(['route' => ['assessment-types.destroy', $type->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                        {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm shadow-sm text-danger', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        {!! Form::close() !!}
                    </div>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="card-footer clearfix">
    <div class="float-right">
        {{ $assessmentTypes->links() }}
    </div>
</div>
