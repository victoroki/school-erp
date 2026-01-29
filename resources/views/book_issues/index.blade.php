@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Book Issues</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-success float-right"
                       href="{{ route('book-issues.create') }}">
                        <i class="fas fa-book-reader"></i> Issue Book
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')


        <!-- Filter Section -->
        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="{{ route('book-issues.index') }}" method="GET">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Search by member name or book title..." value="{{ request('search') }}">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control" onchange="this.form.submit()">
                                        <option value="">All Status</option>
                                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Issued</option>
                                        <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                        <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
                                    </select>
                                </div>
                                <div class="col-md-4 text-right">
                                    @if(request('search') || request('status'))
                                        <a href="{{ route('book-issues.index') }}" class="btn btn-default">Clear Filters</a>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                @include('book_issues.table')

                <div class="card-footer clearfix bg-white">
                    <div class="float-right">
                        @include('adminlte-templates::common.paginate', ['records' => $bookIssues])
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection
