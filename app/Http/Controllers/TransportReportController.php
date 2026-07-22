<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteStop;
use App\Models\StudentTransportAssignment;
use Illuminate\Http\Request;

class TransportReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:transport.view');
    }

    public function index()
    {
        return view('transport.reports.index');
    }

    public function routeWiseStudentList(Request $request)
    {
        $routes = Route::all();
        $selectedRouteId = $request->get('route_id');
        
        $students = [];
        if ($selectedRouteId) {
            $students = StudentTransportAssignment::with(['student', 'pickupStop', 'dropStop'])
                ->where('route_id', $selectedRouteId)
                ->where('status', 'active')
                ->get();
        }

        return view('transport.reports.route_wise_students', compact('routes', 'students', 'selectedRouteId'));
    }

    public function occupancyReport()
    {
        $routesPercentage = Route::all()->map(function($route) {
            return [
                'name' => $route->name,
                'capacity' => $route->vehicle_capacity,
                'occupied' => $route->getCurrentOccupancy(),
                'percentage' => $route->getOccupancyPercentage()
            ];
        });

        return view('transport.reports.occupancy', compact('routesPercentage'));
    }
}
