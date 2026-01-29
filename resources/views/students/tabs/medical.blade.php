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
                                        <td class="font-weight-bold">{{ $student->doctor_phone }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!$student->medical_conditions && !$student->allergies && !$student->medications && !$student->doctor_name)
                    <div class="col-md-12">
                        <p class="text-muted text-center mb-0">No medical information available.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
