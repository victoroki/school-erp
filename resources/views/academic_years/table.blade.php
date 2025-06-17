<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="academic-years-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Is Current</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($academicYears as $academicYear)
                <tr>
                    <td>{{ $academicYear->name }}</td>
                    <td>{{ $academicYear->start_date->format('Y-m-d') }}</td>
                    <td>{{ $academicYear->end_date->format('Y-m-d') }}</td>
                    <td>{{ $academicYear->is_current ? 'Yes':'No' }}</td>
                    <td style="width: 120px">
                        {!! Form::open(['route' => ['academic-years.destroy', $academicYear->academic_year_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('academic-years.show', [$academicYear->academic_year_id]) }}"
                                class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('academic-years.edit', [$academicYear->academic_year_id]) }}"
                                class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
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
            @include('adminlte-templates::common.paginate', ['records' => $academicYears])
        </div>
    </div>
</div>