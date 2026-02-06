@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark">
                    <i class="fas fa-users text-warning mr-2"></i>Student Strength Report
                </h1>
            </div>
            <div class="col-sm-6 text-right">
                <button onclick="window.print()" class="btn btn-outline-info shadow-sm">
                    <i class="fas fa-print mr-1"></i> Print Report
                </button>
                <a href="{{ route('student-reports.index') }}" class="btn btn-default shadow-sm border ml-2">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content px-3">
    <div class="card card-outline card-warning elevation-2">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Class Name</th>
                        <th>Section</th>
                        <th class="text-center">Boys</th>
                        <th class="text-center">Girls</th>
                        <th class="text-center font-weight-bold">Total Strength</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $grandTotal = 0;
                        $totalMale = 0;
                        $totalFemale = 0;
                    @endphp
                    @forelse($reportData as $row)
                        @php 
                            $grandTotal += $row->total;
                            $totalMale += $row->male;
                            $totalFemale += $row->female;
                        @endphp
                        <tr>
                            <td class="font-weight-bold">{{ $row->class_name }}</td>
                            <td>{{ $row->section_name }}</td>
                            <td class="text-center">{{ $row->male }}</td>
                            <td class="text-center">{{ $row->female }}</td>
                            <td class="text-center font-weight-bold">{{ $row->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No enrollment data found.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td colspan="2">GRAND TOTAL</td>
                        <td class="text-center">{{ $totalMale }}</td>
                        <td class="text-center">{{ $totalFemale }}</td>
                        <td class="text-center text-primary" style="font-size: 1.1rem;">{{ $grandTotal }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    @media print {
        .btn, .main-header, .main-sidebar { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
    }
</style>
@endsection
