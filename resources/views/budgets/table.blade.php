<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="budgets-table">
            <thead>
            <tr class="bg-light text-muted small text-uppercase">
                <th class="pl-4 border-0">Financial Year</th>
                <th class="border-0">Category</th>
                <th class="border-0">Type</th>
                <th class="border-0 text-right">Amount</th>
                <th class="border-0">Threshold</th>
                <th class="border-0 text-center pr-4">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($budgets as $budget)
                <tr>
                    <td class="pl-4 py-3 align-middle font-weight-bold">{{ $budget->financialYear->name }}</td>
                    <td class="py-3 align-middle">
                        @php $isIncome = $budget->category_type === 'income'; @endphp
                        <span class="badge {{ $isIncome ? 'badge-success-light text-success' : 'badge-danger-light text-danger' }} px-3 py-2 rounded-pill font-weight-bold">
                            {{ $budget->category ? $budget->category->name : 'N/A' }}
                        </span>
                    </td>
                    <td class="py-3 align-middle">
                        <span class="small font-weight-bold text-uppercase text-muted">{{ ucfirst($budget->category_type) }}</span>
                    </td>
                    <td class="py-3 align-middle text-right {{ $isIncome ? 'text-success' : 'text-danger' }}" style="font-family: 'SFMono-Regular', Consolas, monospace; font-weight: 700;">
                        {{ \App\Support\Money::format($budget->amount ?? 0) }}
                    </td>
                    <td class="py-3 align-middle">{{ \App\Support\Money::whole($budget->alert_threshold) }}%</td>
                    <td class="py-3 align-middle text-center pr-4">
                        <div class="btn-group">
                            <a href="{{ route('budgets.show', [$budget->id]) }}"
                               class="btn btn-sm btn-outline-info rounded-circle mr-1" title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('budgets.edit', [$budget->id]) }}"
                               class="btn btn-sm btn-outline-primary rounded-circle mr-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            {!! Form::open(['route' => ['budgets.destroy', $budget->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                            {!! Form::button('<i class="fas fa-trash"></i>', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-danger rounded-circle', 'onclick' => "return confirm('Are you sure?')", 'title' => 'Delete']) !!}
                            {!! Form::close() !!}
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-chart-pie fa-3x mb-3 opacity-20"></i><br>
                        No budgets found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($budgets->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            <div class="d-flex justify-content-between align-items-center">
                <p class="mb-0 text-muted small">Showing {{ $budgets->firstItem() }} to {{ $budgets->lastItem() }} of {{ $budgets->total() }} budgets</p>
                {{ $budgets->appends(request()->query())->links() }}
            </div>
        </div>
    @endif
</div>