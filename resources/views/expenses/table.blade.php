<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="expenses-table">
            <thead>
            <tr>
                <th>Category Id</th>
                <th>Amount</th>
                <th>Expense Date</th>
                <th>Description</th>
                <th>Payment Method</th>
                <th>Reference Number</th>
                <th>Approved By</th>
                <th>Created By</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($expenses as $expenses)
                <tr>
                    <td>{{ $expenses->category_id }}</td>
                    <td>{{ $expenses->amount }}</td>
                    <td>{{ $expenses->expense_date }}</td>
                    <td>{{ $expenses->description }}</td>
                    <td>{{ $expenses->payment_method }}</td>
                    <td>{{ $expenses->reference_number }}</td>
                    <td>{{ $expenses->approved_by }}</td>
                    <td>{{ $expenses->created_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['expenses.destroy', $expenses->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('expenses.show', [$expenses->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('expenses.edit', [$expenses->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $expenses])
        </div>
    </div>
</div>
