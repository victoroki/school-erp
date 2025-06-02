<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="bank-accounts-table">
            <thead>
            <tr>
                <th>Account Name</th>
                <th>Account Number</th>
                <th>Bank Name</th>
                <th>Branch Name</th>
                <th>Ifsc Code</th>
                <th>Opening Balance</th>
                <th>Current Balance</th>
                <th>Account Type</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bankAccounts as $bankAccount)
                <tr>
                    <td>{{ $bankAccount->account_name }}</td>
                    <td>{{ $bankAccount->account_number }}</td>
                    <td>{{ $bankAccount->bank_name }}</td>
                    <td>{{ $bankAccount->branch_name }}</td>
                    <td>{{ $bankAccount->ifsc_code }}</td>
                    <td>{{ $bankAccount->opening_balance }}</td>
                    <td>{{ $bankAccount->current_balance }}</td>
                    <td>{{ $bankAccount->account_type }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['bankAccounts.destroy', $bankAccount->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('bankAccounts.show', [$bankAccount->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('bankAccounts.edit', [$bankAccount->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $bankAccounts])
        </div>
    </div>
</div>
