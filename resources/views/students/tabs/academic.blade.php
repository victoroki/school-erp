<div class="row">
    <!-- Current Enrollment -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="fas fa-graduation-cap mr-2"></i> Current Enrollment</h6>
            </div>
            <div class="card-body">
                @if($student->current_enrollment)
                    <div class="row">
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Class</p>
                            <h5 class="font-weight-bold">{{ $student->current_enrollment->classSection->schoolClass->name ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Section</p>
                            <h5 class="font-weight-bold">{{ $student->current_enrollment->classSection->section->name ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Academic Year</p>
                            <h5 class="font-weight-bold">{{ $student->current_enrollment->academicYear->name ?? 'N/A' }}</h5>
                        </div>
                        <div class="col-md-3">
                            <p class="text-muted mb-1">Enrollment Date</p>
                            <h5 class="font-weight-bold">
                                {{ $student->current_enrollment->enrollment_date ? $student->current_enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}
                            </h5>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0">No active enrollment found.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Academic Journey Timeline -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-history mr-2"></i> Academic Journey</h6>
            </div>
            <div class="card-body">
                @if($student->academic_journey->count() > 0)
                    @foreach($student->academic_journey as $enrollment)
                        <div class="timeline-item">
                            <h6 class="font-weight-bold">
                                {{ $enrollment->classSection->schoolClass->name ?? 'Unknown Class' }} - {{ $enrollment->classSection->section->name ?? 'Unknown Section' }}
                            </h6>
                            <p class="text-muted mb-1">
                                <i class="fas fa-calendar mr-2"></i> {{ $enrollment->academicYear->name ?? 'Unknown Year' }}
                            </p>
                            <p class="small text-muted mb-0">
                                Enrolled on 
                                {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}

                                @if($enrollment->status != 'active')
                                    <span class="badge badge-secondary ml-2 text-capitalize">{{ $enrollment->status }}</span>
                                @else
                                    <span class="badge badge-success ml-2">Current</span>
                                @endif
                            </p>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted mb-0">No enrollment history available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
