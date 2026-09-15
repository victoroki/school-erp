@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-warning font-weight-bold">
                        <i class="fas fa-bullhorn mr-2"></i> Post Student Notice
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-primary shadow-sm">
            <form action="{{ route('student-notices.store') }}" method="POST">
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
                            <label>Select Student <span class="text-danger">*</span></label>
                            <select name="student_id" class="form-control select2" required>
                                <option value="">Search Student...</option>
                                @foreach($students as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Type <span class="text-danger">*</span></label>
                            <select name="notice_type" class="form-control" required>
                                <option value="general">General</option>
                                <option value="behavior">Behavior</option>
                                <option value="academic">Academic</option>
                                <option value="medical">Medical</option>
                                <option value="attendance">Attendance</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Outstanding Performance in Science Fair" required>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Message</label>
                            <textarea name="body" class="form-control" rows="4" placeholder="Notice details..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right bg-white">
                    <a href="{{ route('student-notices.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Post Notice</button>
                </div>
            </form>
        </div>
    </div>

    @push('page_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css">
    @endpush

    @push('page_scripts')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            });
        </script>
    @endpush
@endsection