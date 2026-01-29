@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-university mr-2"></i>Library Dashboard</h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="container-fluid">
            <!-- Info boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-info elevation-1"><i class="fas fa-book"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Books</span>
                            <span class="info-box-number">{{ $stats['total_books'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Available</span>
                            <span class="info-box-number">{{ $stats['books_available'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-book-reader"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Currently Issued</span>
                            <span class="info-box-number">{{ $stats['total_issued'] }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-exclamation-triangle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Overdue</span>
                            <span class="info-box-number">{{ $stats['overdue_books'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Issues -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header border-transparent">
                            <h3 class="card-title font-weight-bold">Recent Issues</h3>
                            <div class="card-tools">
                                <a href="{{ route('book-issues.index') }}" class="btn btn-sm btn-tool">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table m-0 table-hover table-striped">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>Book</th>
                                        <th>Member</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($recentIssues as $issue)
                                        <tr>
                                            <td>{{ Str::limit($issue->book->title, 30) }}</td>
                                            <td>{{ $issue->member->user->name ?? 'N/A' }}</td>
                                            <td>{{ $issue->due_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $issue->status == 'returned' ? 'success' : ($issue->status == 'overdue' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($issue->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No recent issues found</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <a href="{{ route('book-issues.create') }}" class="btn btn-sm btn-primary">Issue New Book</a>
                        </div>
                    </div>

                    <!-- Overdue Alert -->
                    @if($overdueBooks->count() > 0)
                        <div class="card card-outline card-danger shadow-sm">
                            <div class="card-header">
                                <h3 class="card-title font-weight-bold text-danger"><i class="fas fa-bell mr-2"></i>Urgent: Overdue Books</h3>
                            </div>
                            <div class="card-body p-0">
                                <ul class="products-list product-list-in-card pl-2 pr-2">
                                    @foreach($overdueBooks as $overdue)
                                        <li class="item">
                                            <div class="product-info">
                                                <a href="{{ route('book-issues.show', $overdue->issue_id) }}" class="product-title">
                                                    {{ $overdue->book->title }}
                                                    <span class="badge badge-danger float-right">{{ $overdue->due_date->diffForHumans() }}</span>
                                                </a>
                                                <span class="product-description text-dark">
                                                    Borrowed by: {{ $overdue->member->user->name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Fast Statistics & Categories -->
                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card bg-gradient-primary shadow-sm">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold"><i class="fas fa-bolt mr-2"></i>Quick Actions</h3>
                        </div>
                        <div class="card-body pt-0">
                            <div class="row text-center mt-2">
                                <div class="col-6 mb-3">
                                    <a href="{{ route('books.create') }}" class="btn btn-light btn-block shadow-sm">
                                        <i class="fas fa-plus-circle text-primary mb-1 d-block fa-lg"></i> Add Book
                                    </a>
                                </div>
                                <div class="col-6 mb-3">
                                    <a href="{{ route('libraryMembers.create') }}" class="btn btn-light btn-block shadow-sm">
                                        <i class="fas fa-user-plus text-info mb-1 d-block fa-lg"></i> Add Member
                                    </a>
                                </div>
                                <div class="col-12 mb-3">
                                    <a href="{{ route('book-issues.create') }}" class="btn btn-success btn-block shadow-sm">
                                        <i class="fas fa-book-reader mr-2"></i> Issue a Book
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Books -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Popular This Month</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="products-list product-list-in-card pl-2 pr-2">
                                @forelse($topBooks as $top)
                                    <li class="item">
                                        <div class="product-img">
                                            @if($top->book->cover_url)
                                                <img src="{{ $top->book->cover_url }}" alt="Book cover" class="img-size-50">
                                            @else
                                                <div class="bg-secondary text-center rounded" style="width:50px; height:50px; line-height:50px">
                                                    <i class="fas fa-book"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="product-info ml-2">
                                            <a href="{{ route('books.show', $top->book->book_id) }}" class="product-title text-primary">
                                                {{ $top->book->title }}
                                                <span class="badge badge-info float-right">{{ $top->issue_count }} loans</span>
                                            </a>
                                            <span class="product-description font-italic small">By {{ $top->book->author }}</span>
                                        </div>
                                    </li>
                                @empty
                                    <li class="item text-center py-3 text-muted">No popular books recorded</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Categories -->
                    <div class="card shadow-sm card-outline card-info">
                        <div class="card-header border-0">
                            <h3 class="card-title font-weight-bold">By Category</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @foreach($categoryStats as $cat)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        {{ $cat->name }}
                                        <span class="badge badge-info badge-pill">{{ $cat->books_count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
