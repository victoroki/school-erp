@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Book Issue Details</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right"
                       href="{{ route('book-issues.index') }}">
                        Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <!-- Book Field -->
                    <div class="col-sm-12">
                        {!! Form::label('book', 'Book:') !!}
                        <p>{{ $bookIssue->book->title ?? 'N/A' }}</p>
                    </div>

                    <!-- Member Field -->
                    <div class="col-sm-12">
                        {!! Form::label('member', 'Member:') !!}
                        <p>{{ $bookIssue->member->reference_id ?? 'N/A' }}</p>
                    </div>

                    <!-- Issue Date Field -->
                    <div class="col-sm-12">
                        {!! Form::label('issue_date', 'Issue Date:') !!}
                        <p>{{ $bookIssue->issue_date->format('d M Y') }}</p>
                    </div>

                    <!-- Due Date Field -->
                    <div class="col-sm-12">
                        {!! Form::label('due_date', 'Due Date:') !!}
                        <p>{{ $bookIssue->due_date->format('d M Y') }}</p>
                    </div>

                    <!-- Return Date Field -->
                    <div class="col-sm-12">
                        {!! Form::label('return_date', 'Return Date:') !!}
                        <p>{{ $bookIssue->return_date ? $bookIssue->return_date->format('d M Y') : 'Not Returned' }}</p>
                    </div>

                    <!-- Status Field -->
                    <div class="col-sm-12">
                        {!! Form::label('status', 'Status:') !!}
                        <p>{{ ucfirst($bookIssue->status) }}</p>
                    </div>

                    <!-- Remarks Field -->
                    <div class="col-sm-12">
                        {!! Form::label('remarks', 'Remarks:') !!}
                        <p>{{ $bookIssue->remarks }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
