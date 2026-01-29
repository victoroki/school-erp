@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>
Book Details
                    </h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('books.index') }}">
                                                    Back
                                            </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
                <div class="row">
                    <!-- Left Column: Book Image & Actions -->
                    <div class="col-md-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                @if($book->cover_url)
                                    <img src="{{ $book->cover_url }}" alt="{{ $book->title }}" class="img-fluid mb-3 elevation-2" style="max-height: 300px;">
                                @else
                                    <div class="bg-secondary d-flex justify-content-center align-items-center mb-3 mx-auto elevation-2" style="width: 200px; height: 300px;">
                                        <i class="fas fa-book fa-4x"></i>
                                    </div>
                                @endif
                                <h3 class="profile-username text-center font-weight-bold">{{ $book->title }}</h3>
                                <p class="text-muted text-center">{{ $book->author }}</p>

                                <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item">
                                        <b>Status</b> 
                                        <div class="float-right">
                                            @if($book->available_quantity > 0)
                                                <span class="badge badge-success">Available</span>
                                            @else
                                                <span class="badge badge-danger">Out of Stock</span>
                                            @endif
                                        </div>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Total Copies</b> <a class="float-right text-dark">{{ $book->quantity }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Available</b> <a class="float-right text-dark">{{ $book->available_quantity }}</a>
                                    </li>
                                    <li class="list-group-item">
                                        <b>Shelf</b> <a class="float-right text-dark">{{ $book->shelf_location ?? 'N/A' }}</a>
                                    </li>
                                </ul>

                                <a href="#" class="btn btn-success btn-block {{ $book->available_quantity == 0 ? 'disabled' : '' }}"><b>Issue Book</b></a>
                                <a href="{{ route('books.edit', $book->book_id) }}" class="btn btn-outline-primary btn-block"><b>Edit Details</b></a>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Details & History -->
                    <div class="col-md-9">
                        <div class="card shadow-sm card-primary card-outline card-tabs">
                            <div class="card-header p-0 pt-1 border-bottom-0">
                                <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="custom-tabs-details-tab" data-toggle="pill" href="#custom-tabs-details" role="tab">Book Details</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-history-tab" data-toggle="pill" href="#custom-tabs-history" role="tab">Borrowing History</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="custom-tabs-three-tabContent">
                                    <div class="tab-pane fade show active" id="custom-tabs-details" role="tabpanel">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-barcode mr-1"></i> Identifiers</strong>
                                                <p class="text-muted">
                                                    ISBN: {{ $book->isbn ?? 'N/A' }}<br>
                                                    Barcode: {{ $book->barcode ?? 'N/A' }}
                                                </p>
                                                <hr>

                                                <strong><i class="fas fa-book mr-1"></i> Category</strong>
                                                <p class="text-muted">{{ $book->category->name ?? 'Uncategorized' }}</p>
                                                <hr>
                                                
                                                <strong><i class="fas fa-file-alt mr-1"></i> Description</strong>
                                                <p class="text-muted">{{ $book->description ?? 'No description available for this book.' }}</p>
                                            </div>
                                            <div class="col-md-6">
                                                <strong><i class="fas fa-calendar-alt mr-1"></i> Publication</strong>
                                                <p class="text-muted">
                                                    Publisher: {{ $book->publisher ?? 'N/A' }}<br>
                                                    Year: {{ $book->publication_year ?? 'N/A' }}<br>
                                                    Edition: {{ $book->edition ?? 'N/A' }}<br>
                                                    Pages: {{ $book->pages ?? 'N/A' }}
                                                </p>
                                                <hr>

                                                <strong><i class="fas fa-tag mr-1"></i> Condition & Price</strong>
                                                <p class="text-muted">
                                                    Condition: <span class="badge badge-light border">{{ ucfirst($book->condition ?? 'Good') }}</span><br>
                                                    Price: KSh {{ number_format($book->price, 2) }}
                                                </p>
                                                <hr>
                                                 <strong><i class="fas fa-clock mr-1"></i> Added On</strong>
                                                 <p class="text-muted">{{ $book->added_date instanceof \Carbon\Carbon ? $book->added_date->format('M d, Y') : $book->added_date }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-history" role="tabpanel">
                                        <!-- Placeholder for Issue History -->
                                        <div class="alert alert-info">
                                            <i class="icon fas fa-info"></i> No borrowing history available yet.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
@endsection
