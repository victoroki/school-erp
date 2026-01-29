@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Books</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('books.create') }}">
                        Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('books.index') }}" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search by Title, Author, ISBN..." value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    {!! Form::select('category_id', $categories, request('category_id'), ['class' => 'form-control select2', 'onchange' => 'this.form.submit()']) !!}
                                </div>
                                <div class="col-md-3">
                                    <select name="availability" class="form-control" onchange="this.form.submit()">
                                        <option value="">Status: All</option>
                                        <option value="available" {{ request('availability') == 'available' ? 'selected' : '' }}>Available</option>
                                        <option value="out_of_stock" {{ request('availability') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                    </select>
                                </div>
                                <div class="col-md-2 text-right">
                                    <div class="btn-group">
                                        <a href="{{ route('books.index', array_merge(request()->all(), ['view' => 'grid'])) }}" class="btn btn-default {{ request('view') != 'list' ? 'active' : '' }}" title="Grid View">
                                            <i class="fas fa-th"></i>
                                        </a>
                                        <a href="{{ route('books.index', array_merge(request()->all(), ['view' => 'list'])) }}" class="btn btn-default {{ request('view') == 'list' ? 'active' : '' }}" title="List View">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        @if(request('view') == 'list')
            <div class="card shadow-sm">
                @include('books.table')
            </div>
        @else
            <div class="row">
                @forelse($books as $book)
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex align-items-stretch">
                        <div class="card bg-light d-flex flex-fill shadow-sm hover-shadow">
                            <div class="card-header text-muted border-bottom-0 small">
                                {{ $book->category->name ?? 'Uncategorized' }}
                                <span class="float-right badge {{ $book->available_quantity > 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $book->available_quantity > 0 ? 'Available' : 'Out of Stock' }}
                                </span>
                            </div>
                            <div class="card-body pt-0">
                                <div class="row">
                                    <div class="col-7">
                                        <h2 class="lead"><b>{{ Str::limit($book->title, 25) }}</b></h2>
                                        <p class="text-muted text-sm"><b>Author: </b> {{ $book->author }} </p>
                                        <ul class="ml-4 mb-0 fa-ul text-muted">
                                            <li class="small"><span class="fa-li"><i class="fas fa-barcode"></i></span> ISBN: {{ $book->isbn ?? 'N/A' }}</li>
                                            <li class="small"><span class="fa-li"><i class="fas fa-layer-group"></i></span> Copies: {{ $book->available_quantity }} / {{ $book->quantity }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-5 text-center">
                                        @if($book->cover_url)
                                            <img src="{{ $book->cover_url }}" alt="Book Cover" class="img-fluid border" style="max-height: 120px;">
                                        @else
                                            <div class="bg-secondary d-flex justify-content-center align-items-center border" style="height: 120px; width: 80px; margin: 0 auto;">
                                                <i class="fas fa-book fa-2x"></i>
                                            </div>
                                            <p class="small text-muted mt-1">No Cover</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-right">
                                    <a href="{{ route('books.show', $book->book_id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @if($book->available_quantity > 0)
                                        <a href="#" class="btn btn-sm btn-success">
                                            <i class="fas fa-book-reader"></i> Issue
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center p-5">
                        <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No books found matching your criteria.</p>
                    </div>
                @endforelse
            </div>
            <div class="row">
                <div class="col-12">
                   {{ $books->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

@endsection
