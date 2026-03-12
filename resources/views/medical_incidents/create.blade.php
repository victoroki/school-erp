@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="text-success font-weight-bold">
                        <i class="fas fa-notes-medical mr-2"></i> Log Medical Incident
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        <div class="card card-outline card-success shadow-sm">
            <form action="{{ route('medical-incidents.store') }}" method="POST">
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
                            <label>Symptoms / Complaint <span class="text-danger">*</span></label>
                            <input type="text" name="symptoms" class="form-control" placeholder="e.g. Headache, Fever, Injury" required>
                        </div>
                        <div class="form-group col-sm-6 d-flex align-items-center mt-3">
                            <div class="custom-control custom-switch custom-switch-off-danger custom-switch-on-success">
                                <input type="checkbox" name="notified_parents" class="custom-control-input" id="notifyParentsSwitch">
                                <label class="custom-control-label font-weight-bold" for="notifyParentsSwitch">Parents / Guardian Notified</label>
                            </div>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Details / Observation</label>
                            <textarea name="details" class="form-control" rows="3" placeholder="Describe the symptoms or findings..."></textarea>
                        </div>
                        <div class="form-group col-sm-12">
                            <label>Treatment Given</label>
                            <textarea name="treatment_given" class="form-control" rows="2" placeholder="e.g. First aid, Medication, Hospital referral..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right bg-white">
                    <a href="{{ route('medical-incidents.index') }}" class="btn btn-light border mr-2">Cancel</a>
                    <button type="submit" class="btn btn-success px-4">Log Incident</button>
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
