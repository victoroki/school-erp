<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\HostelAllocation;

class HostelReportController extends Controller
{
    public function index()
    {
        return view('hostels.reports');
    }

    public function vacancyReport()
    {
        $rooms = HostelRoom::with('hostel')
            ->where('status', '!=', 'full')
            ->where('status', '!=', 'under_maintenance')
            ->get();
            
        return view('hostels.reports.vacancy', compact('rooms'));
    }

    public function studentList(Request $request)
    {
        $query = HostelAllocation::with(['student', 'room', 'hostel'])
            ->where('status', 'active');

        if ($request->filled('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        $allocations = $query->get();
        $hostel = $request->filled('hostel_id') ? Hostel::find($request->hostel_id) : null;

        return view('hostels.reports.student_list', compact('allocations', 'hostel'));
    }
}
