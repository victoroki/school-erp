@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Academic Terms</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-sm-right" href="{{ route('fees.terms.create') }}" style="background: linear-gradient(135deg, #0073e7 0%, #0056b3 100%); border: none; border-radius: 8px;">
                        <i class="fas fa-plus mr-1"></i> Add Term
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 20px;">
                <form action="{{ route('fees.terms.index') }}" method="GET" class="d-flex align-items-center" style="gap: 8px;">
                    <select name="academic_year_id" class="form-control form-control-sm" style="width: 200px; border-radius: 8px; border: 1px solid #d1d5db;">
                        <option value="">All Academic Years</option>
                        @foreach($academicYears as $id => $name)
                            <option value="{{ $id }}" {{ ($selectedYearId ?? request('academic_year_id')) == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-primary" type="submit" style="border-radius: 8px;">Filter</button>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="bg-light" style="border-bottom: 2px solid #e5e7eb;">
                            <tr>
                                <th class="px-4 py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Term</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Code</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Academic Year</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Period</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Fee Due Date</th>
                                <th class="py-3" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Status</th>
                                <th class="py-3 text-center" style="font-size: 0.8rem; font-weight: 600; color: #4b5563; text-transform: uppercase;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($terms as $term)
                                <tr>
                                    <td class="px-4 py-3" style="font-weight: 600; color: #1f2937;">{{ $term->name }}</td>
                                    <td class="py-3" style="color: #6b7280;">{{ $term->code }}</td>
                                    <td class="py-3" style="color: #4b5563;">{{ $term->academicYear->name ?? '-' }}</td>
                                    <td class="py-3" style="color: #4b5563;">{{ $term->start_date->format('d/m/Y') }} - {{ $term->end_date->format('d/m/Y') }}</td>
                                    <td class="py-3" style="color: #4b5563;">{{ $term->fee_due_date ? $term->fee_due_date->format('d/m/Y') : '-' }}</td>
                                    <td class="py-3">
                                        @if($term->status == 'active')
                                            <span style="background: #d1fae5; color: #047857; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem;">Active</span>
                                        @elseif($term->status == 'upcoming')
                                            <span style="background: #dbeafe; color: #1d4ed8; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem;">Upcoming</span>
                                        @else
                                            <span style="background: #f3f4f6; color: #4b5563; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem;">Completed</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="d-inline-flex" style="gap: 6px;">
                                            <a href="{{ route('fees.terms.show', $term->id) }}" class="btn btn-sm" style="width: 32px; height: 32px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px;" title="View">
                                                <i class="far fa-eye" style="font-size: 12px;"></i>
                                            </a>
                                            <a href="{{ route('fees.terms.edit', $term->id) }}" class="btn btn-sm" style="width: 32px; height: 32px; background: #dbeafe; color: #1d4ed8; border: none; border-radius: 8px;" title="Edit">
                                                <i class="fas fa-edit" style="font-size: 12px;"></i>
                                            </a>
                                            @if($term->status != 'active')
                                            <form action="{{ route('fees.terms.activate', $term->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm" style="width: 32px; height: 32px; background: #d1fae5; color: #047857; border: none; border-radius: 8px;" title="Activate">
                                                    <i class="fas fa-play" style="font-size: 12px;"></i>
                                                </button>
                                            </form>
                                            @endif
                                            {!! Form::open(['route' => ['fees.terms.destroy', $term->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                                <button type="submit" class="btn btn-sm" onclick="return confirm('Delete this term?')" style="width: 32px; height: 32px; background: #fee2e2; color: #dc2626; border: none; border-radius: 8px;" title="Delete">
                                                    <i class="far fa-trash-alt" style="font-size: 12px;"></i>
                                                </button>
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="fas fa-calendar-alt" style="font-size: 48px; color: #d1d5db; margin-bottom: 16px;"></i>
                                        <p style="color: #4b5563;">No terms found</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($terms->hasPages())
            <div class="card-footer bg-white" style="border-top: 1px solid #e5e7eb;">
                {{ $terms->links('pagination::bootstrap-4') }}
            </div>
            @endif
        </div>
    </div>
@endsection
