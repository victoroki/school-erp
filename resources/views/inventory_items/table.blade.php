<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="inventory-items-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Category Id</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Minimum Quantity</th>
                <th>Cost Per Unit</th>
                <th>Supplier Id</th>
                <th>Location</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($inventoryItems as $inventoryItem)
                <tr>
                    <td>{{ $inventoryItem->name }}</td>
                    <td>{{ $inventoryItem->category_id }}</td>
                    <td>{{ $inventoryItem->quantity }}</td>
                    <td>{{ $inventoryItem->unit }}</td>
                    <td>{{ $inventoryItem->minimum_quantity }}</td>
                    <td>{{ $inventoryItem->cost_per_unit }}</td>
                    <td>{{ $inventoryItem->supplier_id }}</td>
                    <td>{{ $inventoryItem->location }}</td>
                    <td>{{ $inventoryItem->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['inventoryItems.destroy', $inventoryItem->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('inventoryItems.show', [$inventoryItem->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('inventoryItems.edit', [$inventoryItem->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $inventoryItems])
        </div>
    </div>
</div>
