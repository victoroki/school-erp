<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="grading-scales-table">
            <thead>
            <tr>
                <th>Grade</th>
                <th class="text-center">Range (%)</th>
                <th class="text-center">GPA</th>
                <th>Performance Remark</th>
                <th class="text-center">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($gradingScales as $gradingScale)
                <tr>
                    <td>
                        <span class="badge badge-primary px-3 py-2" style="font-size: 1rem;">
                            {{ $gradingScale->name }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="text-bold">{{ number_format($gradingScale->min_percentage, 1) }}%</span> 
                        <i class="fas fa-arrows-alt-h mx-2 text-muted"></i>
                        <span class="text-bold">{{ number_format($gradingScale->max_percentage, 1) }}%</span>
                    </td>
                    <td class="text-center">
                        <code class="text-dark" style="font-size: 1rem;">{{ number_format($gradingScale->grade_point, 2) }}</code>
                    </td>
                    <td>
                        <span class="text-muted">{{ $gradingScale->description ?? '--' }}</span>
                    </td>
                    <td style="width: 120px" class="text-center">
                        {!! Form::open(['route' => ['grading-scales.destroy', $gradingScale->grade_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('grading-scales.edit', [$gradingScale->grade_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $gradingScales])
        </div>
    </div>
</div>
