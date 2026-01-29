<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="exam-schedules-table">
            <thead>
            <tr>
                <th>Exam Session</th>
                <th>Class & Subject</th>
                <th>Date & Time</th>
                <th>Venue</th>
                <th class="text-center">Marks</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($examSchedules as $examSchedule)
                <tr>
                    <td>{{ $examSchedule->exam->name ?? 'N/A' }}</td>
                    <td>
                        <span class="text-bold">{{ $examSchedule->class->name ?? 'N/A' }}</span><br>
                        <span class="text-muted">{{ $examSchedule->subject->name ?? 'N/A' }}</span>
                    </td>
                    <td>
                        <small>
                            <span class="text-bold">{{ $examSchedule->exam_date->format('d M, Y') }}</span><br>
                            <span class="text-info">{{ \Carbon\Carbon::parse($examSchedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($examSchedule->end_time)->format('h:i A') }}</span>
                        </small>
                    </td>
                    <td>
                        <span class="badge badge-light border">{{ $examSchedule->room->name ?? 'Not Assigned' }}</span>
                    </td>
                    <td class="text-center">
                        <small>
                            Pass: <span class="text-success">{{ number_format($examSchedule->passing_marks, 0) }}</span> / <span class="text-dark text-bold">{{ number_format($examSchedule->max_marks, 0) }}</span>
                        </small>
                    </td>
                    <td style="width: 120px" class="text-center">
                        {!! Form::open(['route' => ['exam-schedules.destroy', $examSchedule->schedule_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('exam-schedules.edit', [$examSchedule->schedule_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $examSchedules])
        </div>
    </div>
</div>
