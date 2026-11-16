@push('page_css')
<style>
    .enrollments-table-wrapper {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #f1f5f9;
        background: #fff;
    }
    #student-class-enrollments-table th {
        font-weight: 600;
        color: #64748b;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 16px;
    }
    #student-class-enrollments-table td {
        vertical-align: middle;
        color: #334155;
        border-bottom: 1px solid #f1f5f9;
        padding: 16px;
        font-size: 0.9rem;
    }
    #student-class-enrollments-table tr:last-child td {
        border-bottom: none;
    }
    .modern-badge {
        padding: 0.4em 0.8em;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        text-transform: uppercase;
    }
    .btn-action {
        border-radius: 8px;
        width: 34px;
        height: 34px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        border: none;
        margin-right: 4px;
    }
    .btn-action-view { background: #e0f2fe; color: #0284c7; }
    .btn-action-view:hover { background: #bae6fd; color: #0369a1; transform: translateY(-1px); }
    .btn-action-edit { background: #fef3c7; color: #d97706; }
    .btn-action-edit:hover { background: #fde68a; color: #b45309; transform: translateY(-1px); }
    .btn-action-delete { background: #fee2e2; color: #dc2626; }
    .btn-action-delete:hover { background: #fecaca; color: #b91c1c; transform: translateY(-1px); }
</style>
@endpush

<div class="card-body p-0 enrollments-table-wrapper shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="student-class-enrollments-table">
            <thead>
            <tr>
                <th>Student Name</th>
                <th>Class - Section</th>
                <th>Roll Number</th>
                <th>Academic Year</th>
                <th>Enrollment Date</th>
                <th>Status</th>
                <th class="text-center" style="width: 150px;">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentClassEnrollments as $studentClassEnrollment)
                <tr>
                    <td class="font-weight-bold">{{ optional($studentClassEnrollment->student)->first_name }} {{ optional($studentClassEnrollment->student)->last_name }}</td>
                    <td><i class="fas fa-layer-group text-info mr-1"></i> {{ optional(optional($studentClassEnrollment->classSection)->class)->name }} - {{ optional(optional($studentClassEnrollment->classSection)->section)->name }}</td>
                    <td>{{ $studentClassEnrollment->roll_number ?: '-' }}</td>
                    <td>{{ optional($studentClassEnrollment->academicYear)->name }}</td>
                    <td><i class="far fa-calendar-alt text-muted mr-1"></i> {{ \Carbon\Carbon::parse($studentClassEnrollment->enrollment_date)->format('M d, Y') }}</td>
                    <td>
                        @php
                            $map = [
                                'active' => 'success',
                                'transferred' => 'warning',
                                'completed' => 'primary',
                                'dropped' => 'danger'
                            ];
                            $cls = $map[$studentClassEnrollment->status] ?? 'light';
                        @endphp
                        <span class="badge badge-{{ $cls }} modern-badge">{{ ucfirst($studentClassEnrollment->status) }}</span>
                    </td>
                    <td class="text-center">
                        {!! Form::open(['route' => ['student-class-enrollments.destroy', $studentClassEnrollment->enrollment_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                        <div class='d-flex justify-content-center'>
                            <a href="{{ route('student-class-enrollments.show', [$studentClassEnrollment->enrollment_id]) }}"
                               class='btn-action btn-action-view' title="View Details">
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('student-class-enrollments.edit', [$studentClassEnrollment->enrollment_id]) }}"
                               class='btn-action btn-action-edit' title="Edit">
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn-action btn-action-delete', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure you want to delete this record?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            @if($studentClassEnrollments->isEmpty())
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">No enrollments found.</td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>

    @if($studentClassEnrollments->hasPages())
    <div class="card-footer bg-white border-top">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $studentClassEnrollments])
        </div>
    </div>
    @endif
</div>
