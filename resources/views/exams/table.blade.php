<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="exams-table">
            <thead>
            <tr>
                <th>Exam Session</th>
                <th>Category</th>
                <th>Academic Year</th>
                <th>Duration</th>
                <th class="text-center">Results</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($exams as $exam)
                <tr>
                    <td>
                        <span class="text-bold">{{ $exam->name }}</span><br>
                        <small class="text-muted">{{ \Illuminate\Support\Str::limit($exam->description, 50) }}</small>
                    </td>
                    <td><span class="badge badge-secondary">{{ $exam->examType->name ?? 'None' }}</span></td>
                    <td>{{ $exam->academicYear->name ?? 'N/A' }}</td>
                    <td>
                        <small>
                            <i class="far fa-calendar-alt text-success"></i> {{ $exam->start_date->format('d M, Y') }}<br>
                            <i class="far fa-calendar-check text-danger"></i> {{ $exam->end_date->format('d M, Y') }}
                        </small>
                    </td>
                    <td class="text-center">
                        @if($exam->publish_result)
                            <span class="badge badge-success"><i class="fas fa-check-circle"></i> Published</span>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Draft</span>
                        @endif
                    </td>
                    <td style="width: 120px" class="text-center">
                        {!! Form::open(['route' => ['exams.destroy', $exam->exam_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exams.edit', [$exam->exam_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $exams])
        </div>
    </div>
</div>
