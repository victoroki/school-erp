<div class="row">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0"><i class="fas fa-heartbeat mr-2"></i> Medical Information</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @if($student->blood_group)
                    <div class="col-md-3 mb-3">
                        <p class="text-muted mb-1"><i class="fas fa-tint mr-2"></i> Blood Group</p>
                        <h5><span class="badge badge-danger badge-lg">{{ $student->blood_group }}</span></h5>
                    </div>
                    @endif

                    @if($student->medical_conditions)
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-warning">
                            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle mr-2"></i> Medical Conditions</h6>
                            <p class="mb-0">{{ $student->medical_conditions }}</p>
                        </div>
                    </div>
                    @endif

                    @if($student->allergies)
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-danger">
                            <h6 class="alert-heading"><i class="fas fa-allergies mr-2"></i> Allergies</h6>
                            <p class="mb-0">{{ $student->allergies }}</p>
                        </div>
                    </div>
                    @endif

                    @if($student->medications)
                    <div class="col-md-12 mb-3">
                        <div class="alert alert-info">
                            <h6 class="alert-heading"><i class="fas fa-pills mr-2"></i> Current Medications</h6>
                            <p class="mb-0">{{ $student->medications }}</p>
                        </div>
                    </div>
                    @endif

                    @if($student->doctor_name || $student->doctor_phone)
                    <div class="col-md-12">
                        <div class="card border-left-primary">
                            <div class="card-body">
                                <h6 class="font-weight-bold mb-3"><i class="fas fa-user-md mr-2 text-primary"></i> Family Doctor</h6>
                                <table class="table table-sm table-borderless mb-0">
                                    @if($student->doctor_name)
                                    <tr>
                                        <td width="20%" class="text-muted">Name:</td>
                                        <td class="font-weight-bold">{{ $student->doctor_name }}</td>
                                    </tr>
                                    @endif
                                    @if($student->doctor_phone)
                                    <tr>
                                        <td class="text-muted">Phone:</td>
                                        <td class="font-weight-bold">{{ $student->formatKenyanPhone($student->doctor_phone) }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

    <div class="col-md-12 mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-notes-medical text-primary mr-2"></i> Sick Bay Visits & Incidents</h6>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#logMedicalModal">
                    <i class="fas fa-plus mr-1"></i> Log Incident
                </button>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light x-small text-uppercase">
                        <tr>
                            <th>Date</th>
                            <th>Symptoms</th>
                            <th>Treatment</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($student->medicalIncidents as $incident)
                        <tr>
                            <td>{{ $incident->incident_date->format('d/m/Y') }}</td>
                            <td class="font-weight-bold">{{ $incident->symptoms }}</td>
                            <td>{{ $incident->treatment_given }}</td>
                            <td>
                                @if($incident->notified_parents)
                                    <span class="badge badge-success">Parents Notified</span>
                                @else
                                    <span class="badge badge-secondary">Internal only</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-xs btn-link"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No medical incidents recorded.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="logMedicalModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Log Medical Incident / Sick Bay Visit</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('medical-incidents.store') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->student_id }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Incident Date</label>
                        <input type="date" name="incident_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label>Symptoms / Complaint</label>
                        <input type="text" name="symptoms" class="form-control" placeholder="e.g. Headache, Fever, Injury" required>
                    </div>
                    <div class="form-group">
                        <label>Details / Observation</label>
                        <textarea name="details" class="form-control" rows="3" placeholder="Additional details..."></textarea>
                    </div>
                    <div class="form-group">
                        <label>Treatment Given</label>
                        <textarea name="treatment_given" class="form-control" rows="2" placeholder="e.g. First aid, Paracetamol, Sent home"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="notified_parents" class="form-check-input" id="notifyParentsCheckbox">
                        <label class="form-check-label" for="notifyParentsCheckbox">Parents / Guardian Notified</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Log Incident</button>
                </div>
            </form>
        </div>
    </div>
</div>
