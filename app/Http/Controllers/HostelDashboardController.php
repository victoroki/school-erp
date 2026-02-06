<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Hostel;
use App\Models\HostelRoom;
use App\Models\HostelAllocation;
use App\Models\Student;

class HostelDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_hostels' => Hostel::count(),
            'total_rooms' => HostelRoom::count(),
            'total_capacity' => HostelRoom::sum('capacity'),
            'total_occupied' => HostelRoom::sum('occupied'),
            'total_students' => HostelAllocation::where('status', 'active')->count(),
            'available_rooms' => HostelRoom::where('status', 'available')->count(),
            'maintenance_rooms' => HostelRoom::where('status', 'under_maintenance')->count(),
        ];

        $recentAllocations = HostelAllocation::with(['student', 'room', 'hostel'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $occupancyByHostel = Hostel::withCount(['hostelRooms as total_rooms'])
            ->get()
            ->map(function($hostel) {
                $hostel->occupied = $hostel->hostelRooms()->sum('occupied');
                $hostel->total_capacity = $hostel->hostelRooms()->sum('capacity');
                $hostel->occupancy_rate = $hostel->total_capacity > 0 
                    ? round(($hostel->occupied / $hostel->total_capacity) * 100) 
                    : 0;
                return $hostel;
            });

        return view('hostels.dashboard', compact('stats', 'recentAllocations', 'occupancyByHostel'));
    }
}
