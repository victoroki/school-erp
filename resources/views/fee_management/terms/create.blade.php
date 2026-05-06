@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Add Term</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-default float-right" href="{{ route('fees.terms.index') }}" style="border-radius: 8px;">Back</a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('adminlte-templates::common.errors')

        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px;">
            {!! Form::open(['route' => 'fees.terms.store']) !!}
            <div class="card-body" style="padding: 24px;">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Academic Year</label>
                            <select name="academic_year_id" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                                <option value="">Select</option>
                                @foreach($academicYears as $id => $name)
                                    <option value="{{ $id }}" {{ old('academic_year_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Term Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g., Term 1" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Code</label>
                            <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="e.g., T1-2026" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Display Order</label>
                            <input type="number" name="display_order" class="form-control" value="{{ old('display_order', 0) }}" style="border-radius: 8px; border: 1px solid #d1d5db;">
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label style="font-weight: 600; color: #374151;">Fee Due Date</label>
                            <input type="date" name="fee_due_date" class="form-control" value="{{ old('fee_due_date') }}" style="border-radius: 8px; border: 1px solid #d1d5db;">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 600; color: #374151;">Status</label>
                    <select name="status" class="form-control" style="border-radius: 8px; border: 1px solid #d1d5db;" required>
                        <option value="upcoming" {{ old('status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>

            <div class="card-footer bg-white" style="border-top: 1px solid #e5e7eb; padding: 16px 24px;">
                <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #0073e7 0%, #0056b3 100%); border: none; border-radius: 8px;">
                    <i class="fas fa-save mr-2"></i> Save Term
                </button>
                <a href="{{ route('fees.terms.index') }}" class="btn btn-default ml-2" style="border: 1px solid #d1d5db; border-radius: 8px;">Cancel</a>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
@endsection
