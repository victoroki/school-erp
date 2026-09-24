<!-- Title Field -->
<div class="col-sm-12">
    {!! Form::label('title', 'Title:') !!}
    <p>{{ $book->title }}</p>
</div>

<!-- Author Field -->
<div class="col-sm-12">
    {!! Form::label('author', 'Author:') !!}
    <p>{{ $book->author }}</p>
</div>

<!-- Category Id Field -->
<div class="col-sm-12">
    {!! Form::label('category_id', 'Category:') !!}
    <p>{{ $book->category->name ?? 'N/A' }}</p>
</div>

<!-- Isbn Field -->
<div class="col-sm-12">
    {!! Form::label('isbn', 'Isbn:') !!}
    <p>{{ $book->isbn }}</p>
</div>

<!-- Publisher Field -->
<div class="col-sm-12">
    {!! Form::label('publisher', 'Publisher:') !!}
    <p>{{ $book->publisher }}</p>
</div>

<!-- Publication Year Field -->
<div class="col-sm-12">
    {!! Form::label('publication_year', 'Publication Year:') !!}
    <p>{{ $book->publication_year }}</p>
</div>

<!-- Edition Field -->
<div class="col-sm-12">
    {!! Form::label('edition', 'Edition:') !!}
    <p>{{ $book->edition }}</p>
</div>

<!-- Price Field -->
<div class="col-sm-12">
    {!! Form::label('price', 'Price:') !!}
    <p>{{ \App\Support\Money::format($book->price ?? 0) }}</p>
</div>

<!-- Pages Field -->
<div class="col-sm-12">
    {!! Form::label('pages', 'Pages:') !!}
    <p>{{ $book->pages }}</p>
</div>

<!-- Quantity Field -->
<div class="col-sm-12">
    {!! Form::label('quantity', 'Quantity:') !!}
    <p>{{ $book->quantity }}</p>
</div>

<!-- Available Quantity Field -->
<div class="col-sm-12">
    {!! Form::label('available_quantity', 'Available Quantity:') !!}
    <p>{{ $book->available_quantity }}</p>
</div>

<!-- Shelf Location Field -->
<div class="col-sm-12">
    {!! Form::label('shelf_location', 'Shelf Location:') !!}
    <p>{{ $book->shelf_location }}</p>
</div>

<!-- Added Date Field -->
<div class="col-sm-12">
    {!! Form::label('added_date', 'Added Date:') !!}
    <p>{{ $book->added_date ? \Carbon\Carbon::parse($book->added_date)->format('d/m/Y') : '—' }}</p>
</div>

<!-- Description Field -->
<div class="col-sm-12">
    {!! Form::label('description', 'Description:') !!}
    <p>{{ $book->description ?? '—' }}</p>
</div>

