<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="parents-table">
            <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Relationship</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Alternate Phone</th>
                <th>Occupation</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($parents as $parents)
                <tr>
                    <td>{{ $parents->first_name }}</td>
                    <td>{{ $parents->last_name }}</td>
                    <td>{{ $parents->relationship }}</td>
                    <td>{{ $parents->email }}</td>
                    <td>{{ $parents->phone }}</td>
                    <td>{{ $parents->alternate_phone }}</td>
                    <td>{{ $parents->occupation }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['parents.destroy', $parents->parent_id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('parents.show', [$parents->parent_id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('parents.edit', [$parents->parent_id]) }}"
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

    </div>
</div>
<style>

</style>