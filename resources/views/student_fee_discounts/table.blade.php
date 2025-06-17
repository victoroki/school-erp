<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="student-fee-discounts-table">
            <thead>
            <tr>
                <th>Student </th>
                <th>Discount</th>
                <th>Academic Year</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($studentFeeDiscounts as $studentFeeDiscount)
                <tr>
                    <td>{{ $studentFeeDiscount->student_id }}</td>
                    <td>{{ $studentFeeDiscount->discount_id }}</td>
                    <td>{{ $studentFeeDiscount->academic_year_id }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['student-fee-discounts.destroy', $studentFeeDiscount->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('student-fee-discounts.show', [$studentFeeDiscount->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('student-fee-discounts.edit', [$studentFeeDiscount->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $studentFeeDiscounts])
        </div>
    </div>
</div>
