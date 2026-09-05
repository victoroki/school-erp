@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-folder-open mr-2"></i>Staff Document Details</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        @if($staffDocument->file_path)
                            <a class="btn btn-primary"
                               href="{{ route('staffDocuments.download', [$staffDocument->document_id]) }}">
                                <i class="fas fa-download mr-1"></i> Download
                            </a>
                        @endif
                        <a class="btn btn-default mr-1"
                           href="{{ route('staffDocuments.edit', [$staffDocument->document_id]) }}">
                            <i class="far fa-edit mr-1"></i> Edit
                        </a>
                        <a class="btn btn-default"
                           href="{{ route('staffDocuments.index') }}">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @include('staff_documents.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection