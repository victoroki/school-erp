<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\HostelAllocation;
use App\Models\Parents;
use App\Models\Student;
use App\Models\StudentParentRelationship;
use App\Models\TransportRegistration;
use App\Models\User;
use App\Services\PortalScopeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileLogisticsController extends Controller
{
    /**
     * GET /api/mobile/logistics/transport
     *
     * Read-only, server-owned transport allocation:
     *  - Student → own registration.
     *  - Parent → a ?student_id= child (must be a linked child; a single
     *    linked child is used when no ?student_id= is sent).
     *  - Staff → only with transport.view AND a ?student_id= inside the
     *    caller's existing student scope.
     */
    public function transport(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentIds = $this->resolvePortalStudentIds($request, $user, 'transport.view');

        if (empty($studentIds)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $registration = TransportRegistration::whereIn('student_id', $studentIds)
            ->with(['route', 'stop'])
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'No transport registration found.'], 404);
        }

        $route = $registration->route;

        return response()->json([
            'route'       => $route->name ?? 'Unknown Route',
            'route_code'  => $route->route_code ?? null,
            'vehicle'     => $route && $route->vehicle_number ? [
                'name'   => $route->vehicle_name,
                'number' => $route->vehicle_number,
            ] : null,
            'driver'      => $route && $route->driver_name ? [
                'name'    => $route->driver_name,
                'contact' => $route->driver_contact,
            ] : null,
            'morning'     => [
                'start' => $route && $route->morning_start_time ? (string) $route->morning_start_time : null,
                'end'   => $route && $route->morning_end_time ? (string) $route->morning_end_time : null,
            ],
            'evening'     => [
                'start' => $route && $route->evening_start_time ? (string) $route->evening_start_time : null,
                'end'   => $route && $route->evening_end_time ? (string) $route->evening_end_time : null,
            ],
            'stop'        => $registration->stop->stop_name ?? 'Unknown Stop',
            'stop_time'   => $registration->stop?->stop_time ? (string) $registration->stop->stop_time : null,
            'status'      => $registration->payment_status,
            'fee'         => (float) $registration->fee_amount,
        ]);
    }

    /**
     * GET /api/mobile/logistics/hostel
     *
     * Read-only hostel allocation, same ownership resolution as transport.
     */
    public function hostel(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentIds = $this->resolvePortalStudentIds($request, $user, 'hostel.view');

        if (empty($studentIds)) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $allocation = HostelAllocation::whereIn('student_id', $studentIds)
            ->where('status', 'active')
            ->with(['hostel', 'room'])
            ->first();

        if (!$allocation) {
            return response()->json(['message' => 'No active hostel allocation found.'], 404);
        }

        return response()->json([
            'hostel'      => $allocation->hostel->name ?? 'Unknown Hostel',
            'hostel_type' => $allocation->hostel->type ?? null,
            'room'        => $allocation->room->room_number ?? 'N/A',
            'room_type'   => $allocation->room->room_type ?? null,
            'bed'         => $allocation->bed_number !== null ? (int) $allocation->bed_number : null,
            'date'        => $allocation->allocation_date?->toDateString(),
        ]);
    }

    /**
     * Resolve up to one student id the caller may read for logistics, using
     * the same ownership union as every portal read:
     *  - Student → self;
     *  - Parent → linked children (?student_id= validated; single child
     *    auto-selected when none is passed);
     *  - Staff → explicit ?student_id= inside the caller's student scope,
     *    gated on $permission.
     */
    private function resolvePortalStudentIds(Request $request, User $user, string $permission): array
    {
        if ($user->hasRole('Student')) {
            $student = PortalScopeService::selfStudent($user);
            return $student ? [(int) $student->student_id] : [];
        }

        if ($user->hasRole('Parent')) {
            $parent = Parents::where('user_id', $user->id)->first();
            if (!$parent) {
                return [];
            }
            $children = StudentParentRelationship::where('parent_id', $parent->parent_id)
                ->pluck('student_id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $requested = (int) $request->query('student_id', 0);
            if ($requested > 0) {
                return $children->contains($requested) ? [$requested] : [];
            }

            // No picker: auto-resolve a single-child parent, honest otherwise.
            return $children->count() === 1 ? [$children->first()] : [];
        }

        if ($user->hasPermission($permission)) {
            $requested = (int) $request->query('student_id', 0);
            if ($requested <= 0) {
                return [];
            }
            $visible = app(PortalScopeService::class)->visibleStudentIds($user);
            return in_array($requested, $visible, true) ? [$requested] : [];
        }

        return [];
    }
}