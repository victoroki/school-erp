<div class="card-body p-0 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" id="periods-table">
            <thead style="background-color: #f8fafc; border-bottom: 2px solid #f1f5f9;">
            <tr>
                <th class="px-4 py-3 text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Period Name</th>
                <th class="py-3 text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Type</th>
                <th class="py-3 text-muted small font-weight-bold text-uppercase" style="letter-spacing: 0.5px;">Schedule Duration</th>
                <th class="px-4 py-3 text-muted small font-weight-bold text-uppercase text-right" style="letter-spacing: 0.5px;">Actions</th>
            </tr>
            </thead>
            <tbody class="text-dark">
            @foreach($periods as $period)
                <tr style="transition: background 0.2s;">
                    <td class="px-4 py-3 align-middle">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded text-primary d-flex align-items-center justify-content-center mr-3" style="width: 35px; height: 35px; border-radius: 8px !important;">
                                <i class="fas fa-list-ol"></i>
                            </div>
                            <span class="font-weight-bold" style="font-size: 0.95rem;">{{ $period->name }}</span>
                        </div>
                    </td>
                    <td class="py-3 align-middle">
                        @if(($period->type ?? 'period') === 'break')
                            <span class="badge" style="background-color: #fff7ed; color: #c2410c; padding: 6px 12px; border-radius: 6px;">
                                <i class="fas fa-mug-hot mr-2"></i> Break
                            </span>
                        @else
                            <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; padding: 6px 12px; border-radius: 6px;">
                                <i class="fas fa-book mr-2"></i> Period
                            </span>
                        @endif
                    </td>
                    <td class="py-3 align-middle">
                        <div class="d-flex align-items-center">
                            <span class="badge" style="background-color: #f0fdf4; color: #166534; padding: 6px 12px; border-radius: 6px;">
                                <i class="far fa-clock mr-2"></i> {{ \Carbon\Carbon::parse($period->start_time)->format('h:i A') }}
                            </span>
                            <i class="fas fa-long-arrow-alt-right mx-3 text-muted"></i>
                            <span class="badge" style="background-color: #fef2f2; color: #991b1b; padding: 6px 12px; border-radius: 6px;">
                                <i class="far fa-clock mr-2"></i> {{ \Carbon\Carbon::parse($period->end_time)->format('h:i A') }}
                            </span>
                        </div>
                    </td>
                    <td class="px-4 py-3 align-middle text-right" style="width: 150px">
                        {!! Form::open(['route' => ['periods.destroy', $period->period_id], 'method' => 'delete', 'class' => 'm-0']) !!}
                        <div class='btn-group shadow-xs rounded' style="overflow: hidden;">
                            <a href="{{ route('periods.show', [$period->period_id]) }}"
                               class='btn btn-light btn-sm text-info' title="View" style="background-color: #ffffff; border-color: #f1f5f9;">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('periods.edit', [$period->period_id]) }}"
                               class='btn btn-light btn-sm text-primary' title="Edit" style="background-color: #ffffff; border-color: #f1f5f9;">
                                <i class="fas fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="fas fa-trash-alt"></i>', [
                                'type' => 'submit', 
                                'class' => 'btn btn-light btn-sm text-danger', 
                                'style' => 'background-color: #ffffff; border-color: #f1f5f9;',
                                'onclick' => "return confirm('Are you sure?')",
                                'title' => 'Delete'
                            ]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white border-top shadow-xs clearfix py-3 px-4">
        <div class="float-right m-0">
            @include('adminlte-templates::common.paginate', ['records' => $periods])
        </div>
    </div>
</div>
