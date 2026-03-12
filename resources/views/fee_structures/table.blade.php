<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-valign-middle" id="fee-structures-table">
            <thead>
            <tr>
                <th>Academic Year</th>
                <th>Class</th>
                <th>Fee Category</th>
                <th class="text-right">Amount</th>
                <th>Due Date</th>
                <th class="text-center">Total Assigned</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($feeStructures as $feeStructure)
                <tr>
                    <td>
                        <span class="badge badge-light p-2 border">{{ $feeStructure->academicYear->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="font-weight-bold text-primary">{{ $feeStructure->schoolClass->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <span class="text-dark">{{ $feeStructure->category->name ?? 'N/A' }}</span>
                    </td>
                    <td class="text-right">
                        <span class="text-lg font-weight-bold">KSh {{ number_format($feeStructure->amount, 2) }}</span>
                    </td>
                    <td>
                        <span class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ $feeStructure->due_date ? $feeStructure->due_date->format('M d, Y') : 'N/A' }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ $feeStructure->assignments_count ?? $feeStructure->assignments()->count() }}</span>
                    </td>
                    <td class="text-center">
                        {!! Form::open(['route' => ['fee-structures.destroy', $feeStructure->fee_structure_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('fee-structures.show', [$feeStructure->fee_structure_id]) }}"
                               class='btn btn-outline-secondary btn-sm' title="View">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('fee-structures.edit', [$feeStructure->fee_structure_id]) }}"
                               class='btn btn-outline-primary btn-sm' title="Edit">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-outline-danger btn-sm', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure? This may affect student accounts.')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix bg-white border-0">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $feeStructures])
        </div>
    </div>
</div>
