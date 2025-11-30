<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="books-table">
            <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Category</th>
                <th>Isbn</th>
                <th>Publisher</th>
                <th>Publication Year</th>
                <th>Edition</th>
                <th>Price</th>
                <th>Pages</th>
                <th>Quantity</th>
                <th>Available Quantity</th>
                <th>Shelf Location</th>
                <th>Added Date</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($books as $book)
                <tr>
                    <td>{{ $book->title }}</td>
                    <td>{{ $book->author }}</td>
                    <td>{{ $book->category->name ?? 'N/A' }}</td>
                    <td>{{ $book->isbn }}</td>
                    <td>{{ $book->publisher }}</td>
                    <td>{{ $book->publication_year }}</td>
                    <td>{{ $book->edition }}</td>
                    <td>{{ $book->price }}</td>
                    <td>{{ $book->pages }}</td>
                    <td>{{ $book->quantity }}</td>
                    <td>{{ $book->available_quantity }}</td>
                    <td>{{ $book->shelf_location }}</td>
                    <td>{{ $book->added_date }}</td>
                    <td>{{ $book->description }}</td>
                    <td  style="width: 120px">
                        @if($book->id)
                        {!! Form::open(['route' => ['books.destroy', $book->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('books.show', [$book->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('books.edit', [$book->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $books])
        </div>
    </div>
</div>
