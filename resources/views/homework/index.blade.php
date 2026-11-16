@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-book-open mr-2"></i> Homework
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('homework.create') }}" class="btn btn-primary shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Assign Homework
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="clearfix"></div>

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Title</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Due Date</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($homework as $item)
                                <tr>
                                    <td>
                                        <a href="{{ route('homework.show', [$item->id]) }}" class="font-weight-bold">
                                            {{ $item->title }}
                                        </a>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($item->description, 40) }}</small>
                                    </td>
                                    <td>{{ $item->class_name ?: '—' }}</td>
                                    <td>{{ $item->subject ?: '—' }}</td>
                                    <td>
                                        @if($item->due_date)
                                            <span class="badge {{ $item->due_date->isPast() ? 'badge-danger' : 'badge-success' }}">
                                                {{ $item->due_date->format('d M, Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ optional($item->creator)->name }}</td>
                                    <td>
                                        <a href="{{ route('homework.edit', [$item->id]) }}" class="btn btn-sm btn-light border mr-1" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('homework.destroy', [$item->id]) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this homework?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-book-open mr-1"></i> No homework assigned yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix bg-white">
                <div class="float-right">
                    {{ $homework->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection