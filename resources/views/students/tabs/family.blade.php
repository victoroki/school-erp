<div class="row">
    <!-- Parents/Guardians -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-user-friends mr-2 text-primary"></i> Parents / Guardians</h6>
                <a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> Add Parent</a>
            </div>
            <div class="card-body">
                @if($student->parents->count() > 0)
                    <div class="row">
                        @foreach($student->parents as $parent)
                            <div class="col-md-6 mb-3">
                                <div class="card border">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold">{{ $parent->first_name }} {{ $parent->last_name }}</h6>
                                        <p class="text-muted small mb-2">
                                            <i class="fas fa-link mr-1"></i> {{ $parent->pivot->relationship ?? 'Guardian' }}
                                        </p>
                                        <table class="table table-sm table-borderless mb-0">
                                            @if($parent->phone)
                                            <tr>
                                                <td width="30%"><i class="fas fa-phone text-muted"></i></td>
                                                <td>{{ $parent->phone }}</td>
                                            </tr>
                                            @endif
                                            @if($parent->email)
                                            <tr>
                                                <td><i class="fas fa-envelope text-muted"></i></td>
                                                <td>{{ $parent->email }}</td>
                                            </tr>
                                            @endif
                                            @if($parent->occupation)
                                            <tr>
                                                <td><i class="fas fa-briefcase text-muted"></i></td>
                                                <td>{{ $parent->occupation }}</td>
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No parent/guardian information available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Siblings -->
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 font-weight-bold"><i class="fas fa-child mr-2 text-info"></i> Siblings</h6>
                <a href="#" class="btn btn-sm btn-outline-info"><i class="fas fa-plus"></i> Add Sibling</a>
            </div>
            <div class="card-body">
                @if($student->siblings->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Relationship</th>
                                    <th>Class</th>
                                    <th>Admission No</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($student->siblings as $sibling)
                                    <tr>
                                        <td class="font-weight-bold">
                                            <a href="{{ route('students.show', $sibling->student_id) }}">
                                                {{ $sibling->full_name }}
                                            </a>
                                        </td>
                                        <td class="text-capitalize">{{ str_replace('_', ' ', $sibling->pivot->relationship_type) }}</td>
                                        <td>{{ $sibling->current_enrollment->classSection->schoolClass->class_name ?? 'N/A' }}</td>
                                        <td>{{ $sibling->admission_no }}</td>
                                        <td>{!! $sibling->status_badge !!}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted mb-0">No sibling information available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
