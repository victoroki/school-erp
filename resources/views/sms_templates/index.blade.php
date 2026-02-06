@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>SMS Templates</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('smsTemplates.create') }}">
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
                    <table class="table table-hover" id="sms-templates-table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Preview</th>
                                <th>Usage</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($smsTemplates as $smsTemplate)
                                <tr>
                                    <td><strong>{{ $smsTemplate->title }}</strong></td>
                                    <td><span class="badge badge-secondary">{{ $smsTemplate->category ?? 'General' }}</span></td>
                                    <td>{{ Str::limit($smsTemplate->content, 50) }}</td>
                                    <td>{{ $smsTemplate->usage_count }}</td>
                                    <td>
                                        <span class="badge badge-{{ $smsTemplate->status == 'active' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($smsTemplate->status) }}
                                        </span>
                                    </td>
                                    <td style="width: 150px">
                                        <div class='btn-group'>
                                            <a href="{{ route('communication.compose') }}?template_id={{ $smsTemplate->template_id }}&type=SMS" class="btn btn-success btn-xs" title="Use Template">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                            <a href="{{ route('smsTemplates.edit', [$smsTemplate->template_id]) }}" class='btn btn-default btn-xs'>
                                                <i class="far fa-edit"></i>
                                            </a>
                                            {!! Form::open(['route' => ['smsTemplates.destroy', $smsTemplate->template_id], 'method' => 'delete', 'style' => 'display:inline']) !!}
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
                        {{ $smsTemplates->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
