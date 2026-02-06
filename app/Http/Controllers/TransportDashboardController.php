<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteStop;
use App\Models\StudentTransportAssignment;
use Illuminate\Http\Request;

class TransportDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_routes' => Route::count(),
            'total_stops' => RouteStop::count(),
            'total_students' => StudentTransportAssignment::where('status', 'active')->count(),
            'total_capacity' => Route::sum('vehicle_capacity'),
            'active_vehicles' => Route::where('status', 'active')->count(),
            'maintenance_vehicles' => Route::where('status', 'maintenance')->count(),
        ];

        $recentAssignments = StudentTransportAssignment::with(['student', 'route', 'pickupStop'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $routesOccupancy = Route::withCount(['studentAssignments as occupied_count' => function($query) {
            $query->where('status', 'active');
        }])->get();

        return view('transport.dashboard', compact('stats', 'recentAssignments', 'routesOccupancy'));
    }
}
