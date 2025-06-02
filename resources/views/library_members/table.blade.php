<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="library-members-table">
            <thead>
            <tr>
                <th>User Id</th>
                <th>Member Type</th>
                <th>Reference Id</th>
                <th>Membership Date</th>
                <th>Max Allowed Books</th>
                <th>Status</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($libraryMembers as $libraryMember)
                <tr>
                    <td>{{ $libraryMember->user_id }}</td>
                    <td>{{ $libraryMember->member_type }}</td>
                    <td>{{ $libraryMember->reference_id }}</td>
                    <td>{{ $libraryMember->membership_date }}</td>
                    <td>{{ $libraryMember->max_allowed_books }}</td>
                    <td>{{ $libraryMember->status }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['libraryMembers.destroy', $libraryMember->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('libraryMembers.show', [$libraryMember->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('libraryMembers.edit', [$libraryMember->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $libraryMembers])
        </div>
    </div>
</div>
