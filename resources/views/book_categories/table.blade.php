<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover table-striped" id="book-categories-table">
            <thead class="bg-light">
            <tr>
                <th style="width: 5%">#</th>
                <th style="width: 30%">Category Name</th>
                <th style="width: 40%">Description</th>
                <th style="width: 10%" class="text-center">Books</th>
                <th colspan="3" class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bookCategories as $bookCategory)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="align-middle">
                        <span class="font-weight-bold text-primary">{{ $bookCategory->name }}</span>
                    </td>
                    <td class="align-middle small text-muted">
                        {{ Str::limit($bookCategory->description, 80) ?? 'No description' }}
                    </td>
                    <td class="text-center align-middle">
                        <span class="badge badge-info badge-pill px-3 py-2">{{ $bookCategory->books_count ?? 0 }}</span>
                    </td>
                    <td class="text-center align-middle" style="width: 120px">
                        {!! Form::open(['route' => ['book-categories.destroy', $bookCategory->category_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('bookCategories.show', [$bookCategory->category_id]) }}"
                               class='btn btn-default btn-sm shadow-sm' title="View">
                                <i class="far fa-eye text-primary"></i>
                            </a>
                            <a href="{{ route('bookCategories.edit', [$bookCategory->category_id]) }}"
                               class='btn btn-default btn-sm shadow-sm' title="Edit">
                                <i class="far fa-edit text-warning"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt text-danger"></i>', ['type' => 'submit', 'class' => 'btn btn-default btn-sm shadow-sm', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure?')"]) !!}
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
            @include('adminlte-templates::common.paginate', ['records' => $bookCategories])
        </div>
    </div>
</div>
