@php
    $statusTone = ['active' => 'is-good', 'transferred' => 'is-warning', 'completed' => 'is-info', 'dropped' => 'is-danger'];
@endphp

<div class="table-responsive">
    <table class="table table-hover" id="student-class-enrollments-table" data-no-empty-state>
        <thead>
        <tr>
            <th>Student</th>
            <th>Class</th>
            <th>Roll no.</th>
            <th>Academic year</th>
            <th>Enrolled</th>
            <th>Status</th>
            <th class="text-right"><span class="sr-only">Actions</span></th>
        </tr>
        </thead>
        <tbody>
        @forelse($studentClassEnrollments as $enrollment)
            @php
                $student = $enrollment->student;
                $section = $enrollment->classSection;
            @endphp
            <tr>
                <td>
                    <span class="cell-main">{{ $student?->full_name ?? 'Student removed' }}</span>
                    @if($student?->admission_no)<span class="cell-sub">{{ $student->admission_no }}</span>@endif
                </td>
                <td>{{ $section?->class?->name ?? '—' }}@if($section?->section?->name) <span class="text-muted">· {{ $section->section->name }}</span>@endif</td>
                <td>{{ $enrollment->roll_number ?: '—' }}</td>
                <td>{{ $enrollment->academicYear?->name ?? '—' }}</td>
                <td>{{ $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->format('j M Y') : '—' }}</td>
                <td><span class="status {{ $statusTone[$enrollment->status] ?? '' }}">{{ ucfirst($enrollment->status) }}</span></td>
                <td class="text-right">
                    <div class="row-actions">
                        <a href="{{ route('student-class-enrollments.show', $enrollment->enrollment_id) }}" class="icon-btn" title="View" aria-label="View enrollment of {{ $student?->full_name }}">
                            <i class="far fa-eye" aria-hidden="true"></i>
                        </a>
                        @can('students.manage')
                            <a href="{{ route('student-class-enrollments.edit', $enrollment->enrollment_id) }}" class="icon-btn" title="Edit" aria-label="Edit enrollment of {{ $student?->full_name }}">
                                <i class="far fa-edit" aria-hidden="true"></i>
                            </a>
                            {!! Form::open(['route' => ['student-class-enrollments.destroy', $enrollment->enrollment_id], 'method' => 'delete']) !!}
                                <button type="submit" class="icon-btn is-danger" title="Delete" aria-label="Delete enrollment of {{ $student?->full_name }}"
                                        onclick="return confirm('Delete this enrollment? The student stays, but loses this class placement.')">
                                    <i class="far fa-trash-alt" aria-hidden="true"></i>
                                </button>
                            {!! Form::close() !!}
                        @endcan
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <strong class="d-block mb-1">No enrollments match</strong>
                    <span class="text-muted">
                        @if(request()->hasAny(['q', 'class_section_id', 'academic_year_id', 'status']))
                            Try clearing the filters.
                        @else
                            Enrolled students will be listed here.
                        @endif
                    </span>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($studentClassEnrollments->total())
    <div class="list-foot">
        <span>Showing {{ $studentClassEnrollments->firstItem() }}–{{ $studentClassEnrollments->lastItem() }} of {{ number_format($studentClassEnrollments->total()) }}</span>
        {{ $studentClassEnrollments->onEachSide(1)->links() }}
    </div>
@endif
