<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $academicYear->name }}</p>
</div>

<!-- Start Date Field -->
<div class="col-sm-12">
    {!! Form::label('start_date', 'Start Date:') !!}
    <p>{{ $academicYear->start_date ? $academicYear->start_date->format('d M Y') : 'N/A' }}</p>
</div>

<!-- End Date Field -->
<div class="col-sm-12">
    {!! Form::label('end_date', 'End Date:') !!}
    <p>{{ $academicYear->end_date ? $academicYear->end_date->format('d M Y') : 'N/A' }}</p>
</div>

<!-- Is Current Field -->
<div class="col-sm-12">
    {!! Form::label('is_current', 'Is Current:') !!}
    <p>
        @if($academicYear->is_current)
            <span class="badge badge-success">Yes</span>
        @else
            <span class="badge badge-secondary">No</span>
        @endif
    </p>
</div>

<!-- Terms -->
<div class="col-sm-12">
    {!! Form::label('terms', 'Terms:') !!}

    @if($academicYear->terms->isEmpty())
        <div class="alert alert-warning mb-0">
            <i class="fas fa-exclamation-triangle"></i>
            This academic year has no terms, so fees cannot be assigned to it.
            Edit it and save again, or set it up from the terms page.
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Term</th>
                        <th>Code</th>
                        <th>Starts</th>
                        <th>Ends</th>
                        <th>Fees Due</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($academicYear->terms as $term)
                        <tr>
                            <td>{{ $term->name }}</td>
                            <td><code>{{ $term->code }}</code></td>
                            <td>{{ $term->start_date->format('d M Y') }}</td>
                            <td>{{ $term->end_date->format('d M Y') }}</td>
                            <td>{{ $term->fee_due_date ? $term->fee_due_date->format('d M Y') : '—' }}</td>
                            <td class="text-center">
                                <span class="badge badge-{{ $term->status === 'active' ? 'success' : ($term->status === 'completed' ? 'secondary' : 'info') }}">
                                    {{ ucfirst($term->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

