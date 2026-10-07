@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Import Books</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a class="btn btn-outline-secondary mr-2" href="{{ route('books.index') }}">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Books
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('flash::message')

        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title">1. Download the template</h3>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    The template is a real Excel workbook with the expected columns, a worked
                    example row and an <em>Instructions</em> sheet. Fill it in and upload it below.
                </p>
                <p class="mb-3 text-muted">
                    <strong>Title</strong> and <strong>Author</strong> are required. Everything
                    else is optional. Leave <strong>Copies</strong> blank for a single copy.
                </p>
                <a class="btn btn-success" href="{{ route('books.import-template') }}">
                    <i class="fas fa-download mr-1"></i> Download Excel Template
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <h3 class="card-title">2. Upload the filled template</h3>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 pl-3">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {!! Form::open(['route' => ['books.import.store'], 'method' => 'POST', 'enctype' => 'multipart/form-data']) !!}
                    <div class="form-group">
                        {!! Form::label('file', 'Spreadsheet file') !!}
                        {!! Form::file('file', ['class' => 'form-control-file', 'accept' => '.xlsx,.csv', 'required']) !!}
                        <small class="form-text text-muted">
                            .xlsx or .csv, up to 5&nbsp;MB. Re-save .xls files as .xlsx first.
                        </small>
                    </div>

                    @if (count($categories))
                        <details class="mb-3">
                            <summary class="text-muted">
                                Category names accepted in the <strong>Category</strong> column
                            </summary>
                            <p class="mt-2 mb-0">
                                @foreach ($categories as $category)
                                    <span class="badge badge-secondary mr-1">{{ $category }}</span>
                                @endforeach
                            </p>
                        </details>
                    @else
                        <div class="alert alert-warning">
                            There are no book categories yet. Leave the <strong>Category</strong>
                            column blank, or
                                <a href="{{ route('bookCategories.create') }}">create a category</a>
                            first.
                        </div>
                    @endif

                    {!! Form::submit('Import Books', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        </div>

        @isset($import_result)
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">3. Result</h3>
                </div>
                <div class="card-body">
                    @php
                        $imported = $import_result['imported'];
                        $rowErrors = $import_result['errors'];
                    @endphp

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted text-uppercase small">Rows read</div>
                                <div class="h4 mb-0">{{ $import_result['total'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted text-uppercase small">Imported</div>
                                <div class="h4 mb-0 text-success">{{ $imported }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="text-muted text-uppercase small">Skipped</div>
                                <div class="h4 mb-0 {{ $rowErrors ? 'text-danger' : '' }}">
                                    {{ count($rowErrors) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($imported > 0)
                        <div class="alert alert-success">
                            {{ $imported }} {{ Str::plural('book', $imported) }} imported.
                            @if ($rowErrors)
                                The rows listed below were skipped.
                            @endif
                        </div>
                    @elseif (! $rowErrors)
                        <div class="alert alert-secondary mb-0">
                            Nothing to import — the file had no book rows.
                        </div>
                    @else
                        <div class="alert alert-danger">
                            No books were imported. Fix the rows listed below and upload the file again.
                        </div>
                    @endif

                    @if ($rowErrors)
                        <h4 class="mt-4">Rows that were not imported</h4>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                <tr>
                                    <th style="width: 90px">Row</th>
                                    <th>Problem</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($rowErrors as $error)
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $error['row'] == 1 ? 'header' : $error['row'] }}
                                        </td>
                                        <td>{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        <p class="text-muted mb-0">
                            Row numbers count from 1 and include the header row, matching what
                            Excel shows. Fix only these rows and upload the same file again — the
                            books that did import will be reported as duplicates.
                        </p>
                    @endif
                </div>
            </div>
        @endisset
    </div>
@endsection
