<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="exam-results-table">
            <thead>
            <tr>
                <th>Student</th>
                <th>Exam & Subject</th>
                <th class="text-center">Marks</th>
                <th class="text-center">Grade</th>
                <th>Remarks</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($examResults as $examResult)
                <tr>
                    <td>
                        <span class="text-bold">{{ $examResult->student->first_name ?? 'N/A' }} {{ $examResult->student->last_name ?? '' }}</span><br>
                        <small class="text-muted">{{ $examResult->classSection->section_name ?? 'N/A' }}</small>
                    </td>
                    <td>
                        <span class="text-dark">{{ $examResult->exam->name ?? 'N/A' }}</span><br>
                        <small class="text-primary">{{ $examResult->subject->name ?? 'N/A' }}</small>
                    </td>
                    <td class="text-center">
                        <span class="h6 text-bold">{{ number_format($examResult->marks_obtained, 1) }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $gradeBadge = 'badge-secondary';
                            if($examResult->grade) {
                                if(in_array($examResult->grade->name, ['A', 'A+', 'B'])) $gradeBadge = 'badge-success';
                                elseif(in_array($examResult->grade->name, ['C', 'D'])) $gradeBadge = 'badge-warning';
                                else $gradeBadge = 'badge-danger';
                            }
                        @endphp
                        <span class="badge {{ $gradeBadge }} px-3 py-2" style="font-size: 0.9rem;">
                            {{ $examResult->grade->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <small class="text-muted italic">{{ $examResult->remarks ?? '--' }}</small>
                    </td>
                    <td style="width: 120px" class="text-center">
                        {!! Form::open(['route' => ['exam-results.destroy', $examResult->result_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exam-results.edit', [$examResult->result_id]) }}"
                               class='btn btn-light btn-sm' title="Edit">
                                <i class="far fa-edit text-primary"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt text-danger"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm', 'title' => 'Delete', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $examResults])
        </div>
    </div>
</div>
