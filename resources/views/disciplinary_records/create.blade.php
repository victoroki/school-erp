@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-danger font-weight-bold">
                        <i class="fas fa-gavel mr-2"></i> Log Disciplinary Action
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-danger shadow-sm">
            <form action="{{ route('disciplinary-records.store') }}" method="POST">
                @csrf
                <div class="card-body">
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
                            <label>Incident Date <span class="text-danger">*</span></label>
                            <input type="date" name="incident_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Incident Type <span class="text-danger">*</span></label>
                            <select name="incident_type" class="form-control select2" required>
                                <option value="Bullying">Bullying</option>
                                <option value="Tardiness">Tardiness</option>
                                <option value="Dress Code">Dress Code</option>
                                <option value="Cheating">Cheating</option>
                                <option value="Disruptive Behavior">Disruptive Behavior</option>
                                <option value="Theft">Theft</option>
                                <option value="Vandalism">Vandalism</option>
                                <option value="Assault">Assault</option>
                                <option value="Cell Phone Violation">Cell Phone Violation</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-6">
                            <label>Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="open">Open</option>
                                <option value="investigating">Under Investigation</option>
                                <option value="closed">Closed</option>
                            </select>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Describe the incident in detail..." required></textarea>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Action Taken (Optional)</label>
                            <textarea name="action_taken" class="form-control" rows="2" placeholder="Any immediate action taken?"></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right bg-white">
                    <a href="{{ route('disciplinary-records.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-danger px-4">Log Incident</button>
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
