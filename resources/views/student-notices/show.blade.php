@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-bullhorn mr-2"></i> {{ $notice->title }}
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Student</dt>
                    <dd class="col-sm-9">
                        {{ optional($notice->student)->first_name }} {{ optional($notice->student)->last_name }}
                        ({{ optional($notice->student)->admission_no }})
                    </dd>
                    <dt class="col-sm-3 text-muted">Type</dt>
                    <dd class="col-sm-9">
                        @php
                            $badgeClass = [
                                'general'    => 'badge-primary',
                                'behavior'   => 'badge-warning',
                                'academic'   => 'badge-info',
                                'medical'    => 'badge-danger',
                                'attendance' => 'badge-secondary',
                                'other'      => 'badge-dark',
                            ][$notice->notice_type] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ ucfirst($notice->notice_type) }}</span>
                    </dd>
                    <dt class="col-sm-3 text-muted">Posted By</dt>
                    <dd class="col-sm-9">{{ optional($notice->creator)->name }}</dd>
                    <dt class="col-sm-3 text-muted">Posted On</dt>
                    <dd class="col-sm-9">{{ $notice->created_at->format('d M, Y H:i') }}</dd>
                    <dt class="col-sm-3 text-muted">Message</dt>
                    <dd class="col-sm-9">{{ $notice->body }}</dd>
                </dl>
            </div>
        </div>
    </div>
@endsection