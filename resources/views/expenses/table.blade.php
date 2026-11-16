<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="expenses-table">
            <thead>
            <tr>
                <th>Category</th>
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
                    <td>{{ $expenses->category->name ?? 'N/A' }}</td>
                    <td>{{ \App\Support\Money::format($expenses->amount ?? 0) }}</td>
                    <td>{{ $expenses->expense_date ? \Carbon\Carbon::parse($expenses->expense_date)->format('d/m/Y') : '—' }}</td>
                    <td>{{ $expenses->description ?? '—' }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $expenses->payment_method ?? '')) }}</td>
                    <td>{{ $expenses->reference_number ?? '—' }}</td>
                    <td>{{ $expenses->approvedBy->name ?? '—' }}</td>
                    <td>{{ $expenses->createdBy->name ?? '—' }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['expenses.destroy', $expenses->expense_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('expenses.show', [$expenses->expense_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('expenses.edit', [$expenses->expense_id]) }}"
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
