@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-id-card text-warning mr-2"></i>Bulk ID Cards
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <a class="btn btn-warning shadow-sm" href="{{ route('students.bulk-id-cards.form') }}">
                    <i class="fas fa-sync mr-1"></i> Refresh
                </a>
                <a class="btn btn-outline-warning shadow-sm" href="{{ route('students.index') }}">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Students
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    @include('flash::message')

    <div class="card card-outline card-warning elevation-2 overflow-hidden">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-check-square text-warning mr-2"></i>Select Students</h3>
            <div class="card-tools">
                <button type="submit" form="bulkIdCardForm" class="btn btn-warning shadow-sm btn-sm">
                    <i class="fas fa-id-card mr-1"></i> Generate ID Cards for Selected
                </button>
            </div>
        </div>

        <form id="bulkIdCardForm" method="POST" action="{{ route('students.bulk-id-cards') }}" target="_blank">
            @csrf
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center" style="width: 40px">
                                    <input type="checkbox" id="select-all-checkbox">
                                </th>
                                <th>Admission No</th>
                                <th>Student Name</th>
                                <th>Gender</th>
                                <th>Class - Section</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($students as $student)
                                @php
                                    $enrollment = $student->studentClassEnrollments
                                        ->where('status', 'active')
                                        ->sortByDesc('academic_year_id')
                                        ->first();
                                    $classSection = $enrollment?->classSection;
                                @endphp
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}" class="student-checkbox">
                                    </td>
                                    <td>{{ $student->admission_no }}</td>
                                    <td class="align-middle">
                                        <span class="font-weight-bold text-primary">{{ $student->full_name ?? $student->first_name . ' ' . $student->last_name }}</span>
                                    </td>
                                    <td>{{ ucfirst($student->gender) }}</td>
                                    <td>
                                        @if($classSection)
                                            {{ optional($classSection->schoolClass)->name ?? 'N/A' }} - {{ optional($classSection->section)->name ?? '' }}
                                        @else
                                            <span class="text-muted">Not enrolled</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No active students found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right">
                <button type="submit" class="btn btn-warning shadow-sm">
                    <i class="fas fa-id-card mr-1"></i> Generate ID Cards for Selected
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page_scripts')
<script>
    document.getElementById('select-all-checkbox').addEventListener('change', function () {
        document.querySelectorAll('.student-checkbox').forEach(function (cb) {
            cb.checked = this.checked;
        }, this);
    });
</script>
@endpush
