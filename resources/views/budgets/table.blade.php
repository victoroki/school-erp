<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="budgets-table">
            <thead>
            <tr>
                <th>Financial Year</th>
                <th>Category</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Threshold</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($budgets as $budget)
                <tr>
                    <td>{{ $budget->financialYear->name }}</td>
                    <td>{{ $budget->category ? $budget->category->name : 'N/A' }}</td>
                    <td>{{ ucfirst($budget->category_type) }}</td>
                    <td>{{ number_format($budget->amount, 2) }}</td>
                    <td>{{ $budget->alert_threshold }}%</td>
                    <td style="width: 120px">
                        {!! Form::open(['route' => ['budgets.destroy', $budget->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('budgets.show', [$budget->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('budgets.edit', [$budget->id]) }}"
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
            {{-- @include('adminlte-templates::common.paginate', ['records' => $budgets]) --}}
        </div>
    </div>
</div>
