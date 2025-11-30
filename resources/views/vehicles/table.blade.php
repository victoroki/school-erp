<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="vehicles-table">
            <thead>
            <tr>
                <th>Vehicle Number</th>
                <th>Vehicle Type</th>
                <th>Model</th>
                <th>Make</th>
                <th>Year</th>
                <th>Seating Capacity</th>
                <th>Status</th>
                <th>Insurance Expiry</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($vehicles as $vehicle)
                <tr class="{{ $vehicle->isInsuranceExpired() ? 'table-danger' : '' }}">
                    <td>{{ $vehicle->vehicle_number }}</td>
                    <td>{{ $vehicle->vehicle_type }}</td>
                    <td>{{ $vehicle->model }}</td>
                    <td>{{ $vehicle->make }}</td>
                    <td>{{ $vehicle->year }}</td>
                    <td>{{ $vehicle->seating_capacity }}</td>
                    <td>{{ $vehicle->status }}</td>
                    <td>{{ $vehicle->insurance_expiry_date }}
                        @if($vehicle->isInsuranceExpired())
                            <span class="badge badge-danger">Expired</span>
                        @endif
                    </td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['vehicles.destroy', $vehicle->vehicle_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('vehicles.show', [$vehicle->vehicle_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('vehicles.edit', [$vehicle->vehicle_id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $vehicles])
        </div>
    </div>
</div>