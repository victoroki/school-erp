<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="exams-table">
            <thead>
            <tr>
                <th class="pl-4">Exam Session</th>
                <th>Category</th>
                <th>Academic Year</th>
                <th>Schedule</th>
                <th class="text-center">Status</th>
                <th class="text-right pr-4">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach($exams as $exam)
                <tr>
                    <td class="pl-4">
                        <div class="d-flex align-items-center">
                            <div class="bg-light-danger p-2 rounded mr-3 text-danger">
                                <i class="fas fa-file-invoice fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold">{{ $exam->name }}</h6>
                                <small class="text-muted">{{ \Illuminate\Support\Str::limit($exam->description, 40) }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-outline-danger">{{ $exam->examType->name ?? 'Standard' }}</span>
                    </td>
                    <td>{{ $exam->academicYear->name ?? 'N/A' }}</td>
                    <td>
                        <div class="small">
                            <span class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ $exam->start_date->format('d/m/Y') }}</span>
                            <br>
                            <span class="text-muted"><i class="far fa-calendar-check mr-1"></i> {{ $exam->end_date->format('d/m/Y') }}</span>
                        </div>
                    </td>
                    <td class="text-center">
                        @if($exam->publish_result)
                            <span class="badge badge-success px-3 py-2 elevation-1"><i class="fas fa-check-circle mr-1"></i> Published</span>
                        @else
                            <span class="badge badge-warning px-3 py-2 elevation-1 text-white"><i class="fas fa-edit mr-1"></i> Draft</span>
                        @endif
                    </td>
                    <td class="text-right pr-4">
                        <div class='btn-group'>
                            <a href="{{ route('exams.show', [$exam->exam_id]) }}" class='btn btn-light btn-sm shadow-sm' title="View Session Details">
                                <i class="far fa-eye text-primary"></i>
                            </a>
                            <a href="{{ route('exams.edit', [$exam->exam_id]) }}" class='btn btn-light btn-sm shadow-sm ml-1' title="Edit Session">
                                <i class="far fa-edit text-info"></i>
                            </a>
                            {!! Form::open(['route' => ['exams.destroy', $exam->exam_id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-light btn-sm shadow-sm ml-1 text-danger', 'onclick' => "return confirm('Are you sure you want to delete this exam session and all its results?')"]) !!}
                            {!! Form::close() !!}
                        </div>
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
