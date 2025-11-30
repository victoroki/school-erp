<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="inventory-items-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Category</th>
                <th>Quantity</th>
                <th>Unit</th>
                <th>Minimum Quantity</th>
                <th>Cost Per Unit</th>
                <th>Supplier</th>
                <th>Location</th>
                <th>Description</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($inventoryItems as $inventoryItem)
                <tr>
                    <td>{{ $inventoryItem->name }}</td>
                    <td>{{ $inventoryItem->category->name ?? 'N/A' }}</td>
                    <td>
                        @if($inventoryItem->isLowStock())
                            <span class="badge badge-danger">{{ $inventoryItem->quantity }}</span>
                        @elseif($inventoryItem->quantity <= ($inventoryItem->minimum_quantity * 1.5))
                            <span class="badge badge-warning">{{ $inventoryItem->quantity }}</span>
                        @else
                            {{ $inventoryItem->quantity }}
                        @endif
                    </td>
                    <td>{{ $inventoryItem->unit }}</td>
                    <td>{{ $inventoryItem->minimum_quantity }}</td>
                    <td>{{ $inventoryItem->cost_per_unit }}</td>
                    <td>{{ $inventoryItem->supplier->name ?? 'N/A' }}</td>
                    <td>{{ $inventoryItem->location }}</td>
                    <td>{{ $inventoryItem->description }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['inventory-items.destroy', $inventoryItem->item_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('inventory-items.show', [$inventoryItem->item_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('inventory-items.edit', [$inventoryItem->item_id]) }}"
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
