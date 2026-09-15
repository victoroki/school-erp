@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-book-open mr-2"></i> Assign Homework
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary shadow-sm">
            <form action="{{ route('homework.store') }}" method="POST">
                @csrf
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
                            <input type="text" name="title" class="form-control" placeholder="e.g. Chapter 5 Algebra Questions" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Due Date</label>
                            <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Class</label>
                            <input type="text" name="class_name" class="form-control" placeholder="e.g. Form 2A">
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Subject</label>
                            <input type="text" name="subject" class="form-control" placeholder="e.g. Mathematics">
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Description (Optional)</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Detailed instructions for the assignment..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right bg-white">
                    <a href="{{ route('homework.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Assign Homework</button>
                </div>
            </form>
        </div>
    </div>
@endsection