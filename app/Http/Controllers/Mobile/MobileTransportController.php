<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Route;
use App\Models\StudentTransportAssignment;
use App\Services\PortalScopeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * GET /api/mobile/transport
 *
 * This endpoint read columns and relations the transport models do not have
 * (route_name, vehicle, driver, stop, $user->parents), so the staff branch
 * answered 500 and the parent branch always came back empty. It now reads the
 * real schema: routes carry their vehicle and driver inline, assignments link
 * a pickup and a drop stop, and children are resolved by PortalScopeService
 * exactly as the rest of the portal does.
 */
class MobileTransportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $user->loadMissing('roles');

        if ($user->hasRole('Parent') || $user->hasRole('Student')) {
            $studentIds = app(PortalScopeService::class)->visibleStudentIds($user);

            $assignments = StudentTransportAssignment::with(['route', 'pickupStop', 'dropStop'])
                ->whereIn('student_id', $studentIds ?: [0])
                ->get();

            return response()->json([
                'data' => $assignments->map(fn ($a) => [
                    'type' => 'my_transport',
                    'student_id' => $a->student_id,
                    'route' => $a->route?->name ?? 'Unknown',
                    'stop' => $a->pickupStop?->stop_name ?? 'Unknown',
                    'drop_stop' => $a->dropStop?->stop_name,
                    'pickup_time' => $a->pickupStop?->stop_time,
                    'dropoff_time' => $a->dropStop?->stop_time,
                    'vehicle_reg' => $a->route?->vehicle_number ?: 'N/A',
                    'driver_name' => $a->route?->driver_name ?: 'N/A',
                    'driver_phone' => $a->route?->driver_contact ?: 'N/A',
                ])->values(),
            ]);
        }

        // Staff see every route, but only with the transport module's view
        // permission, the same gate the web transport screens apply.
        abort_unless($user->hasPermission('transport.view'), 403, 'You do not have access to transport.');

        $routes = Route::withCount(['routeStops', 'studentAssignments'])->get();

        return response()->json([
            'data' => $routes->map(fn ($r) => [
                'type' => 'route_info',
                'id' => $r->route_id,
                'name' => $r->name,
                'vehicle' => $r->vehicle_number ?: 'N/A',
                'driver' => $r->driver_name ?: 'N/A',
                'stops_count' => $r->route_stops_count,
                'students_count' => $r->student_assignments_count,
            ])->values(),
        ]);
    }
}
