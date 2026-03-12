@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Email Templates</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('emailTemplates.create') }}">
                        <i class="fas fa-plus mr-1"></i> Add New
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')
        <div class="clearfix"></div>
        <div class="card card-primary card-outline">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover" id="email-templates-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Subject</th>
                                <th>Usage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($emailTemplates as $emailTemplate)
                                <tr>
                                    <td><strong>{{ $emailTemplate->title }}</strong></td>
                                    <td><span class="badge badge-secondary">{{ $emailTemplate->category ?? 'General' }}</span></td>
                                    <td>{{ $emailTemplate->subject }}</td>
                                    <td>{{ $emailTemplate->usage_count }}</td>
                                    <td>
                                        <span class="badge badge-{{ $emailTemplate->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($emailTemplate->status) }}
                                        </span>
                                    </td>
                                    <td style="width: 150px">
                                        <div class='btn-group'>
                                            <a href="{{ route('communication.compose') }}?template_id={{ $emailTemplate->template_id }}&type=Email" class="btn btn-success btn-xs" title="Use Template">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <a href="{{ route('emailTemplates.edit', [$emailTemplate->template_id]) }}" class='btn btn-default btn-xs'>
                                                <i class="far fa-edit"></i>
                                            </a>
                                            {!! Form::open(['route' => ['emailTemplates.destroy', $emailTemplate->template_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
                                                {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    <div class="float-right">
                        {{ $emailTemplates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
