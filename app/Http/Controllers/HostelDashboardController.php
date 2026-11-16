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
    public function __construct()
    {
        $this->middleware('can:hostel.view');
    }

    public function index()
    {
        // Occupancy is counted from the active allocation rows rather than the
        // cached hostel_rooms.occupied column, so this screen can never show
        // "7 residents" and "9 occupied beds" side by side.
        $occupied = HostelAllocation::where('status', HostelAllocation::STATUS_ACTIVE)->count();

        $stats = [
            'total_hostels' => Hostel::count(),
            'total_rooms' => HostelRoom::count(),
            'total_capacity' => (int) HostelRoom::sum('capacity'),
            'total_occupied' => $occupied,
            'total_students' => $occupied,
            'available_rooms' => HostelRoom::where('status', HostelRoom::STATUS_AVAILABLE)->count(),
            'maintenance_rooms' => HostelRoom::where('status', HostelRoom::STATUS_UNDER_MAINTENANCE)->count(),
        ];

        $stats['vacant_beds'] = max(0, $stats['total_capacity'] - $stats['total_occupied']);

        $recentAllocations = HostelAllocation::withDisplayContext()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $occupancyByHostel = Hostel::withCount(['hostelRooms as total_rooms'])
            ->get()
            ->map(function (Hostel $hostel) {
                $hostel->occupied = $hostel->getCurrentOccupancy();
                $hostel->total_capacity = $hostel->getBedCapacity();
                $hostel->occupancy_rate = $hostel->total_capacity > 0
                    ? round(($hostel->occupied / $hostel->total_capacity) * 100)
                    : 0;
                $hostel->capacity_mismatch = $hostel->capacityMismatch();

                return $hostel;
            });

        return view('hostels.dashboard', compact('stats', 'recentAllocations', 'occupancyByHostel'));
    }
}
