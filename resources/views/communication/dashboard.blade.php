@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Communication Dashboard</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('communication.compose') }}" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Compose Message
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_sms_sent'] }}</h3>
                        <p>SMS Sent</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-sms"></i>
                    </div>
                    <a href="{{ route('communication.history.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_email_sent'] }}</h3>
                        <p>Emails Sent</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <a href="{{ route('communication.history.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_templates'] }}</h3>
                        <p>Active Templates</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <a href="{{ route('smsTemplates.index') }}" class="small-box-footer">Manage <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['failed_messages'] }}</h3>
                        <p>Failed Messages</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <a href="{{ route('communication.history.index') }}" class="small-box-footer">View Logs <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Activity -->
            <div class="col-md-8">
                <div class="card card-outline card-primary">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">Recent Messages</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Subject/Content</th>
                                        <th>Recipients</th>
                                        <th>Status</th>
                                        <th>Sent At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stats['recent_messages'] as $message)
                                        <tr>
                                            <td>
                                                <span class="badge badge-{{ $message->message_type == 'SMS' ? 'info' : 'success' }}">
                                                    {{ $message->message_type }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($message->subject ?? $message->content, 40) }}</td>
                                            <td>{{ $message->recipient_count }} ({{ $message->recipient_type }})</td>
                                            <td>
                                                <span class="badge badge-{{ $message->status == 'Sent' ? 'success' : ($message->status == 'Failed' ? 'danger' : 'warning') }}">
                                                    {{ $message->status }}
                                                </span>
                                            </td>
                                            <td>{{ $message->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">No recent messages found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <a href="{{ route('communication.compose') }}" class="btn btn-sm btn-primary float-left">New Message</a>
                        <a href="{{ route('communication.history.index') }}" class="btn btn-sm btn-secondary float-right">View All History</a>
                    </div>
                </div>
            </div>

            <!-- Popular Templates -->
            <div class="col-md-4">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Popular Templates</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @foreach($stats['popular_sms_templates'] as $template)
                                <li class="item">
                                    <div class="product-info ml-0">
                                        <a href="{{ route('communication.compose') }}?template_id={{ $template->id }}&type=SMS" class="product-title">
                                            {{ $template->title }} (SMS)
                                            <span class="badge badge-warning float-right">{{ $template->usage_count }} Uses</span>
                                        </a>
                                        <span class="product-description">
                                            {{ Str::limit($template->content, 50) }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                            @foreach($stats['popular_email_templates'] as $template)
                                <li class="item">
                                    <div class="product-info ml-0">
                                        <a href="{{ route('communication.compose') }}?template_id={{ $template->id }}&type=Email" class="product-title">
                                            {{ $template->title }} (Email)
                                            <span class="badge badge-success float-right">{{ $template->usage_count }} Uses</span>
                                        </a>
                                        <span class="product-description">
                                            {{ Str::limit($template->subject, 50) }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
