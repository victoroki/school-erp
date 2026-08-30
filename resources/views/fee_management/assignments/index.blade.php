@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fee Assignments</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-sm-right" href="{{ route('fees.assignments.create') }}" style="
                        background: linear-gradient(135deg, #0073e7 0%, #0056b3 100%);
                        border: none;
                        border-radius: 8px;
                        padding: 8px 16px;
                        font-weight: 500;
                        transition: transform 160ms ease-out, box-shadow 160ms ease-out;
                    ">
                        <i class="fas fa-plus mr-1"></i> Assign Fees
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('flash::message')

        {{-- Stats Cards --}}
        <div class="row mb-4 g-3">
            <div class="col-lg-3 col-6">
                <div class="p-3" style="
                    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
                    border-radius: 12px;
                    border: 1px solid #bfdbfe;
                    transition: transform 160ms ease-out, box-shadow 160ms ease-out;
                ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Total</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #1e40af;">{{ $assignments->total() }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <i class="fas fa-clipboard-list" style="color: #0073e7; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="p-3" style="
                    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
                    border-radius: 12px;
                    border: 1px solid #a7f3d0;
                    transition: transform 160ms ease-out, box-shadow 160ms ease-out;
                ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Active</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #047857;">{{ $assignments->where('status', 'active')->count() }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <i class="fas fa-check-circle" style="color: #10b981; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="p-3" style="
                    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                    border-radius: 12px;
                    border: 1px solid #fde68a;
                    transition: transform 160ms ease-out, box-shadow 160ms ease-out;
                ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Total (KES)</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #b45309;">{{ number_format($assignments->sum('amount'), 0) }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <i class="fas fa-dollar-sign" style="color: #f59e0b; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="p-3" style="
                    background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%);
                    border-radius: 12px;
                    border: 1px solid #f5d0fe;
                    transition: transform 160ms ease-out, box-shadow 160ms ease-out;
                ">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Net (KES)</div>
                            <div style="font-size: 1.75rem; font-weight: 700; color: #a855f7;">{{ number_format($assignments->sum('final_amount'), 0) }}</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: white; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                            <i class="fas fa-wallet" style="color: #a855f7; font-size: 20px;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table Card --}}
        <div class="card" style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div class="card-header bg-white" style="border-bottom: 1px solid #e5e7eb; padding: 16px 20px;">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 12px;">
                    <h3 class="card-title mb-0" style="font-weight: 600; color: #1f2937; font-size: 1rem;">
                        <span style="color: #0073e7;">✦</span> All Fee Assignments
                    </h3>
                    <form action="{{ route('fees.assignments.index') }}" method="GET" class="d-flex align-items-center" style="gap: 8px;">
                        <select name="class_id" class="form-control form-control-sm" style="width: 140px; border-radius: 8px; border: 1px solid #d1d5db;" placeholder="All Classes">
                            <option value="">All Classes</option>
                            @foreach($classes ?? [] as $id => $name)
                                <option value="{{ $id }}" {{ request('class_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" name="student_name" class="form-control" placeholder="Search student..." value="{{ request('student_name') }}" style="border-radius: 8px 0 0 8px; border: 1px solid #d1d5db; border-right: 0;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit" style="border-radius: 0 8px 8px 0; border: 1px solid #d1d5db;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="min-width: 800px;">
                        <thead class="bg-light" style="border-bottom: 2px solid #e5e7eb;">
                            <tr>
                                <th class="px-4 py-3" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Student</th>
                                <th class="py-3" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Class</th>
                                <th class="py-3" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Category</th>
                                <th class="py-3" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Term / Year</th>
                                <th class="py-3 text-right" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Amount</th>
                                <th class="py-3 text-right" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Discount</th>
                                <th class="py-3 text-right" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Net</th>
                                <th class="py-3" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                                <th class="py-3 text-center" style="font-weight: 600; color: #4b5563; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($assignments as $assignment)
                                <tr style="transition: background-color 150ms ease;">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px; font-size: 13px; color: white; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                {{ strtoupper(substr($assignment->student->first_name ?? 'U', 0, 1) . substr($assignment->student->last_name ?? '', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-semibold" style="color: #1f2937; font-size: 0.9rem;">{{ $assignment->student->first_name ?? '' }} {{ $assignment->student->last_name ?? '' }}</div>
                                                <small style="color: #6b7280; font-size: 0.8rem;">{{ $assignment->student->admission_no ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span style="background: #eff6ff; color: #1d4ed8; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                            {{ $assignment->student->current_enrollment->classSection->schoolClass->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3" style="color: #4b5563;">{{ $assignment->feeStructure->category->name ?? '-' }}</td>
                                    <td class="py-3">
                                        <div style="color: #1f2937; font-size: 0.9rem;">{{ $assignment->term }}</div>
                                        <small style="color: #6b7280; font-size: 0.8rem;">{{ $assignment->academicYear->name ?? '-' }}</small>
                                    </td>
                                    <td class="py-3 text-right font-weight-semibold" style="color: #1f2937;">{{ number_format($assignment->amount, 2) }}</td>
                                    <td class="py-3 text-right">
                                        @if($assignment->discount_amount > 0)
                                            <span style="color: #dc2626;">-{{ number_format($assignment->discount_amount, 2) }}</span>
                                        @else
                                            <span style="color: #9ca3af;">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right">
                                        <span class="font-weight-bold" style="color: #059669; font-size: 0.95rem;">{{ number_format($assignment->final_amount, 2) }}</span>
                                    </td>
                                    <td class="py-3">
                                        @if($assignment->status == 'active')
                                            <span style="background: #d1fae5; color: #047857; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                                <i class="fas fa-check-circle mr-1" style="font-size: 10px;"></i> Active
                                            </span>
                                        @else
                                            <span style="background: #f3f4f6; color: #4b5563; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                                {{ ucfirst($assignment->status) }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="d-inline-flex" style="gap: 6px;">
                                            <a href="{{ route('fees.assignments.student-summary', $assignment->student_id) }}" 
                                               class="btn btn-sm d-inline-flex align-items-center justify-content-center"
                                               style="width: 32px; height: 32px; background: #f3f4f6; color: #4b5563; border: none; border-radius: 8px; transition: transform 160ms ease-out;"
                                               title="View Summary">
                                                <i class="far fa-eye" style="font-size: 12px;"></i>
                                            </a>
                                            {!! Form::open(['route' => ['fees.assignments.destroy', $assignment->id], 'method' => 'delete', 'class' => 'd-inline']) !!}
                                                <button type="submit" 
                                                    class="btn btn-sm d-inline-flex align-items-center justify-content-center"
                                                    onclick="return confirm('Remove this fee assignment?')"
                                                    style="width: 32px; height: 32px; background: #fee2e2; color: #dc2626; border: none; border-radius: 8px; transition: transform 160ms ease-out;"
                                                    title="Remove">
                                                    <i class="far fa-trash-alt" style="font-size: 12px;"></i>
                                                </button>
                                            {!! Form::close() !!}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div style="color: #6b7280;">
                                            <div style="width: 64px; height: 64px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                                                <i class="fas fa-folder-open" style="font-size: 28px; color: #9ca3af;"></i>
                                            </div>
                                            <p class="mb-2" style="font-size: 1rem; color: #4b5563; font-weight: 500;">No fee assignments found</p>
                                            <a href="{{ route('fees.assignments.create') }}" style="color: #0073e7; text-decoration: none; font-weight: 500;">
                                                <i class="fas fa-plus mr-1"></i> Create your first assignment
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($assignments->hasPages())
            <div class="card-footer bg-white" style="border-top: 1px solid #e5e7eb; padding: 12px 20px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div style="color: #6b7280; font-size: 0.875rem;">
                        Showing {{ $assignments->firstItem() ?? 0 }} to {{ $assignments->lastItem() ?? 0 }} of {{ $assignments->total() }} entries
                    </div>
                    <div>
                        {{ $assignments->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('page_styles')
    <style>
        .btn:active {
            transform: scale(0.95);
        }
        .table tbody tr:hover {
            background-color: #f9fafb !important;
        }
        .form-control:focus {
            border-color: #0073e7;
            box-shadow: 0 0 0 3px rgba(0, 115, 231, 0.1);
        }

        @media (max-width: 768px) {
            .content-header .row { flex-direction: column; gap: 0.5rem; }
            .content-header .col-sm-6 { width: 100%; }
            .content-header .btn { width: 100%; justify-content: center; }

            .row.g-3 > [class*="col-"] { flex: 0 0 50%; max-width: 50%; }
            .row.g-3 > [class*="col-"] > div { padding: 0.75rem !important; }
            .row.g-3 > [class*="col-"] > div > div > div:first-child { font-size: 0.6rem !important; }
            .row.g-3 > [class*="col-"] > div > div > div:last-child { font-size: 1.2rem !important; }

            .card-header .d-flex { flex-direction: column; gap: 0.5rem !important; }
            .card-header form { width: 100%; flex-wrap: wrap; }
            .card-header form select,
            .card-header form .input-group { width: 100% !important; min-width: 0; }

            .table { min-width: 0 !important; }
            .table th:nth-child(n+5),
            .table td:nth-child(n+5) { display: none; }
            .table th:first-child, .table td:first-child { padding-left: 0.75rem; }
            .table td:first-child { font-size: 0.8rem; }

            .card-footer .d-flex { flex-direction: column; gap: 0.5rem; align-items: center; }
        }

        @media (max-width: 420px) {
            .row.g-3 > [class*="col-"] { flex: 0 0 100%; max-width: 100%; }
        }
    </style>
    @endpush
@endsection