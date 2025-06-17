<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="fee-structures-table">
            <thead>
            <tr>
                <th>Academic Year </th>
                <th>Class </th>
                <th>Category </th>
                <th>Amount</th>
                <th>Due Date</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($feeStructures as $feeStructure)
                <tr>
                    <td>{{ $feeStructure->academic_year_id }}</td>
                    <td>{{ $feeStructure->class_id }}</td>
                    <td>{{ $feeStructure->category_id }}</td>
                    <td>{{ $feeStructure->amount }}</td>
                    <td>{{ $feeStructure->due_date }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['fee-structures.destroy', $feeStructure->fee_structure_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('fee-structures.show', [$feeStructure->fee_structure_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('fee-structures.edit', [$feeStructure->fee_structure_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $feeStructures])
        </div>
    </div>
</div>
