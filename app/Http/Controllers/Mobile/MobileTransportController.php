<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Route;
use App\Models\StudentTransportAssignment;
use Illuminate\Support\Facades\Auth;

class MobileTransportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // If parent or student, they mostly care about their own assignments
        if ($user->hasRole('Parent') || $user->hasRole('Student')) {
            $studentIds = [];
            if ($user->hasRole('Parent')) {
                $studentIds = collect($user->parents->students ?? [])->pluck('student_id')->toArray();
            } else {
                $studentIds = [$user->student->student_id ?? 0];
            }
            
            $assignments = StudentTransportAssignment::with(['route', 'stop'])
                ->whereIn('student_id', $studentIds)
                ->get();
                
            return response()->json([
                'data' => $assignments->map(function($a) {
                    return [
                        'type' => 'my_transport',
                        'route' => $a->route ? $a->route->route_name : 'Unknown',
                        'stop' => $a->stop ? $a->stop->stop_name : 'Unknown',
                        'pickup_time' => $a->stop ? $a->stop->pickup_time : null,
                        'dropoff_time' => $a->stop ? $a->stop->dropoff_time : null,
                        'vehicle_reg' => $a->route && $a->route->vehicle ? $a->route->vehicle->registration_number : 'N/A',
                        'driver_name' => $a->route && $a->route->driver ? $a->route->driver->name : 'N/A',
                        'driver_phone' => $a->route && $a->route->driver ? $a->route->driver->phone : 'N/A',
                    ];
                })
            ]);
        }

        // For Admins/Teachers, return all routes
        $routes = Route::withCount(['stops', 'studentAssignments'])->with('vehicle')->get();
        return response()->json([
            'data' => $routes->map(function($r) {
                return [
                    'type' => 'route_info',
                    'id' => $r->route_id,
                    'name' => $r->route_name,
                    'vehicle' => $r->vehicle ? $r->vehicle->registration_number : 'N/A',
                    'driver' => $r->driver ? $r->driver->name : 'N/A',
                    'stops_count' => $r->stops_count,
                    'students_count' => $r->student_assignments_count
                ];
            })
        ]);
    }
}
