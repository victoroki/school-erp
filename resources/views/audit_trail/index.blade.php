@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <h1 class="text-dark font-weight-bold"><i class="fas fa-history text-secondary mr-2"></i>Financial Audit Trail</h1>
        </div>
    </section>

    <div class="content px-3">
        <div class="card border-0 shadow-sm rounded-lg">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="bg-light text-muted small text-uppercase">
                            <th class="pl-4 border-0">Timestamp</th>
                            <th class="border-0">User</th>
                            <th class="border-0">Module</th>
                            <th class="border-0">Action</th>
                            <th class="border-0">Record ID</th>
                            <th class="border-0">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td class="pl-4 py-3 small text-muted font-weight-bold">{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                                <td class="py-3">{{ $log->user ? $log->user->name : 'System' }}</td>
                                <td class="py-3"><span class="badge badge-light px-3 py-1">{{ $log->module }}</span></td>
                                <td class="py-3 font-weight-bold">{{ $log->action }}</td>
                                <td class="py-3 text-muted">#{{ $log->record_id }}</td>
                                <td class="py-3 small text-muted">{{ $log->ip_address }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white border-0 py-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
