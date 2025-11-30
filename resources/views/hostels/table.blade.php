<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="hostels-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Address</th>
                <th>Warden Id</th>
                <th>Capacity</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($hostels as $hostel)
                <tr>
                    <td>{{ $hostel->name }}</td>
                    <td>{{ $hostel->type }}</td>
                    <td>{{ $hostel->address }}</td>
                    <td>{{ $hostel->warden_id }}</td>
                    <td>{{ $hostel->capacity }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['hostels.destroy', $hostel->hostel_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('hostels.show', [$hostel->hostel_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('hostels.edit', [$hostel->hostel_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $hostels])
        </div>
    </div>
</div>
