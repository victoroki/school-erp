<div class="table-responsive">
    <table class="table table-hover align-middle mb-0" id="income-categories-table">
        <thead>
        <tr>
            <th class="ps-4">Category Details</th>
            <th>Description</th>
            <th class="text-end pe-4" style="width: 140px;">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($incomeCategories as $incomeCategory)
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-wrap bg-emerald-light text-emerald" style="width: 40px; height: 40px; border-radius: 10px; font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <span class="d-block font-weight-bold text-dark" style="font-size: .875rem; line-height: 1.2;">
                                {{ $incomeCategory->name }}
                            </span>
                            <span class="text-muted" style="font-size: .688rem; font-weight: 500;">
                                ID: #{{ str_pad($incomeCategory->category_id, 4, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="text-slate" style="font-size: .813rem; line-height: 1.4; display: block; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ $incomeCategory->description ?: 'No description provided for this income stream.' }}
                    </span>
                </td>
                <td class="text-end pe-4">
                    {!! Form::open(['route' => ['incomeCategories.destroy', $incomeCategory->category_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                    <div class="d-flex justify-content-end gap-1">
                        <a href="{{ route('incomeCategories.show', [$incomeCategory->category_id]) }}"
                           class='action-btn btn-view' title="View Details">
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('incomeCategories.edit', [$incomeCategory->category_id]) }}"
                           class='action-btn btn-edit' title="Edit Category">
                            <i class="far fa-edit"></i>
                        </a>
                        {!! Form::button('<i class="far fa-trash-alt"></i>', [
                            'type' => 'submit', 
                            'class' => 'action-btn btn-delete border-0', 
                            'title' => 'Delete Category',
                            'onclick' => "return confirm('Are you sure you want to delete this category?')"
                        ]) !!}
                    </div>
                    {!! Form::close() !!}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center py-4">
                        <div class="icon-wrap mb-3" style="width: 56px; height: 56px; font-size: 1.5rem; background: #ecfdf5; color: #10b981; border-radius: 16px; border: 1px solid #d1fae5; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <h5 class="mb-1 text-dark" style="font-size: 1rem; font-weight: 700; letter-spacing: -0.01em;">No revenue streams found</h5>
                        <p class="text-muted mb-4" style="font-size: .813rem; max-width: 300px;">Organize your revenue by creating your first income category.</p>
                        <a class="btn-dash btn-primary-dash" href="{{ route('incomeCategories.create') }}">
                            <i class="fas fa-plus me-1"></i> Create Category
                        </a>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($incomeCategories->hasPages())
<div class="card-footer bg-white border-top-0 d-flex justify-content-center px-4 py-3">
    @include('adminlte-templates::common.paginate', ['records' => $incomeCategories])
</div>
@endif
