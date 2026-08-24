<div class="card-body p-0 table-responsive">
    <table class="table table-hover mb-0 align-middle" id="students-table">
        <thead class="bg-light">
            <tr>
                <th style="width: 40px;" class="text-center">
                    <input type="checkbox" id="checkAll">
                </th>
                <th style="width: 120px;">Admission No</th>
                <th>Student</th>
                <th>Class & Section</th>
                <th>Gender</th>
                <th>Guardian Info</th>
                <th class="text-center">Fees</th>
                <th class="text-center">Status</th>
                <th class="text-right pr-4" style="width: 150px">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $student)
                @php 
                    $enrollment = $student->studentClassEnrollments->where('is_current', true)->first();
                @endphp
                <tr>
                    <td class="text-center align-middle">
                        <input type="checkbox" name="student_ids[]" value="{{ $student->student_id }}" class="student-checkbox">
                    </td>
                    <td class="align-middle">
                        <span class="badge badge-light border font-weight-bold px-2 py-1">{{ $student->admission_no }}</span>
                    </td>
                    <td class="align-middle">
                        <div class="d-flex align-items-center">
                            @include('students._avatar', ['student' => $student, 'size' => 38])
                            <div class="ml-2">
                                <div class="font-weight-bold">{{ $student->full_name }}</div>
                                <div class="x-small text-muted">{{ $student->nemis_number ? 'NEMIS: '.$student->nemis_number : '' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="align-middle">
                        @if($enrollment && $enrollment->classSection)
                            <div class="font-weight-600">{{ $enrollment->classSection->schoolClass->name ?? 'N/A' }}</div>
                            <div class="x-small text-muted">{{ $enrollment->classSection->section->name ?? 'Default' }} Section</div>
                        @else
                            <span class="text-muted small">Not Assigned</span>
                        @endif
                    </td>
                    <td class="align-middle">
                        <span class="text-capitalize small">
                            <i class="fas fa-{{ $student->gender == 'male' ? 'mars text-info' : ($student->gender == 'female' ? 'venus text-pink' : 'user text-muted') }} mr-1"></i>
                            {{ $student->gender }}
                        </span>
                    </td>
                    <td class="align-middle">
                        <div class="small font-weight-600">{{ $student->emergency_contact_name ?? 'No Primary' }}</div>
                        <div class="x-small text-muted"><i class="fas fa-phone mr-1"></i> {{ $student->formatted_phone == 'N/A' ? $student->formatted_emergency_phone : $student->formatted_phone }}</div>
                    </td>
                    <td class="align-middle text-center">
                        <div class="small font-weight-bold mb-1">{{ $student->formatted_fee_balance }}</div>
                        {!! $student->payment_status_badge !!}
                    </td>
                    <td class="align-middle text-center">
                        {!! $student->status_badge !!}
                    </td>
                    <td class="align-middle text-right pr-4">
                        <div class='btn-group shadow-sm'>
                            <a href="{{ route('students.show', [$student->student_id]) }}" class='btn btn-light btn-sm border' title="View Profile">
                                <i class="far fa-eye text-primary"></i>
                            </a>
                            <a href="{{ route('students.edit', [$student->student_id]) }}" class='btn btn-light btn-sm border' title="Edit">
                                <i class="far fa-edit text-secondary"></i>
                            </a>
                            <a href="{{ route('students.id-card', [$student->student_id]) }}" target="_blank" class='btn btn-light btn-sm border' title="Generate ID Card">
                                <i class="fas fa-id-card text-warning"></i>
                            </a>
                            <button type="button" class="btn btn-light btn-sm border text-danger" onclick="confirmDelete('{{ $student->student_id }}')" title="Delete">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>
                        <form id="delete-form-{{ $student->student_id }}" action="{{ route('students.destroy', [$student->student_id]) }}" method="POST" style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="card-footer clearfix">
    <div class="float-right">
        @include('adminlte-templates::common.paginate', ['records' => $students])
    </div>
</div>

<style>
    .font-weight-600 { font-weight: 600; }
    .x-small { font-size: 0.75rem; }
    .text-pink { color: #e83e8c; }
    .table thead th { border-top: 0; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .table td { vertical-align: middle !important; border-top: 1px solid #f8f9fa; }
    .btn-group .btn { padding: 0.25rem 0.6rem; }
    .pagination { margin-bottom: 0; }
</style>

<script>
    $(function() {
        $('#checkAll').change(function() {
            $('.student-checkbox').prop('checked', $(this).is(':checked'));
        });
    });

    function confirmDelete(id) {
        if(confirm('Are you sure you want to delete this student record?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
