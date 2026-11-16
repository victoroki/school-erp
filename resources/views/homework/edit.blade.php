@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-book-open mr-2"></i> Edit Homework
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary shadow-sm">
            <form action="{{ route('homework.update', [$homework->id]) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="form-group col-sm-6">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="{{ old('title', $homework->title) }}" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="form-control"
                                   value="{{ old('due_date', $homework->due_date?->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Class</label>
                            <input type="text" name="class_name" class="form-control" value="{{ old('class_name', $homework->class_name) }}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" value="{{ old('subject', $homework->subject) }}">
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $homework->description) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right bg-white">
                    <a href="{{ route('homework.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection