<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="book-categories-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($bookCategories as $bookCategory)
                <tr>
                    <td>{{ $bookCategory->name }}</td>
                    <td>{{ $bookCategory->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['bookCategories.destroy', $bookCategory->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('bookCategories.show', [$bookCategory->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('bookCategories.edit', [$bookCategory->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $bookCategories])
        </div>
    </div>
</div>
