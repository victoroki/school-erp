<div class="col-lg-5 col-md-6">
    <div class="card card-outline card-primary elevation-2 h-100 mb-4 mb-lg-0">
        <div class="card-header bg-light">
            <h3 class="card-title text-uppercase small font-weight-bold">Guardian Details</h3>
        </div>
        <div class="card-body">
            <div class="text-center mb-4">
                <span class="badge badge-primary badge-pill px-3 py-2">
                    <i class="fas fa-user-tie mr-1"></i>
                    {{ ucfirst($parents->relationship ?: 'Guardian') }}
                </span>
            </div>

            <!-- Full Name -->
            <div class="mb-3">
                <div class="text-uppercase text-muted small font-weight-bold mb-1">Full name</div>
                <div class="h5 mb-0">{{ $parents->full_name }}</div>
            </div>

            <!-- Contact Fields -->
            <div class="row">
                <div class="col-sm-6 mb-3">
                    <div class="text-uppercase text-muted small font-weight-bold mb-1">Phone</div>
                    <div>{{ $parents->phone ? ($parents->formatted_phone ?? $parents->phone) : '—' }}</div>
                </div>
                <div class="col-sm-6 mb-3">
                    <div class="text-uppercase text-muted small font-weight-bold mb-1">Alternate phone</div>
                    <div>{{ $parents->alternate_phone ?? '—' }}</div>
                </div>
                <div class="col-sm-6 mb-3">
                    <div class="text-uppercase text-muted small font-weight-bold mb-1">Email</div>
                    <div class="text-break">{{ $parents->email ?? '—' }}</div>
                </div>
                <div class="col-sm-6 mb-3">
                    <div class="text-uppercase text-muted small font-weight-bold mb-1">Occupation</div>
                    <div>{{ $parents->occupation ?? '—' }}</div>
                </div>
            </div>

            <!-- User Id Field -->
            <div class="mb-0">
                <div class="text-uppercase text-muted small font-weight-bold mb-1">Portal account</div>
                @if($parents->user)
                    <span class="badge badge-success">
                        <i class="fas fa-id-badge mr-1"></i> {{ $parents->user->name }}
                    </span>
                @else
                    <span class="badge badge-secondary">No portal account linked</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="col-lg-7 col-md-6">
    <div class="card card-outline card-primary elevation-2">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h3 class="card-title text-uppercase small font-weight-bold mb-0">
                Linked Students
                <span class="badge badge-info ml-1">{{ $parents->students->count() }}</span>
            </h3>
            <a href="{{ route('parents.index') }}" class="btn btn-light btn-sm border">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
        </div>
        <div class="card-body p-0">
            @if($parents->students->isEmpty())
                <div class="card-body text-center py-5">
                    <i class="fas fa-child fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">This guardian is not linked to any learner yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                        <tr>
                            <th>Student</th>
                            <th>Admission No.</th>
                            <th>Current Class</th>
                            <th class="text-right pr-3">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($parents->students as $student)
                            @php
                                $enrollment = $student->studentClassEnrollments
                                    ->where('is_current', true)
                                    ->first();
                                $section = optional($enrollment)->classSection;
                                $className = optional(optional($section)->schoolClass)->name
                                    ?? (optional($section)->section ? 'Section ' . optional($section)->section->name : null);
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $student->full_name }}</div>
                                    <div class="small text-muted text-capitalize">{{ $student->gender ?? '—' }}</div>
                                </td>
                                <td>{{ $student->admission_no ?? '—' }}</td>
                                <td>
                                    @if($className)
                                        <span class="badge badge-primary">{{ $className }}</span>
                                    @else
                                        <span class="badge badge-warning">Unassigned</span>
                                    @endif
                                </td>
                                <td class="text-right pr-3">
                                    <a href="{{ route('students.show', [$student->student_id]) }}"
                                       class="btn btn-light btn-sm border" title="Open learner profile">
                                        <i class="far fa-eye text-primary"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
