<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\TransportRegistration;
use App\Models\HostelAllocation;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileLogisticsController extends Controller
{
    /**
     * GET /api/mobile/logistics/transport
     *
     * Returns transport details for the authenticated student.
     */
    public function transport(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student record not found.'], 404);
        }

        $registration = TransportRegistration::where('student_id', $student->student_id)
            ->with(['route', 'stop'])
            ->first();

        if (!$registration) {
            return response()->json(['message' => 'No transport registration found.'], 404);
        }

        return response()->json([
            'route'  => $registration->route->name ?? 'Unknown Route',
            'stop'   => $registration->stop->stop_name ?? 'Unknown Stop',
            'status' => $registration->payment_status,
            'fee'    => (float) $registration->fee_amount,
        ]);
    }

    /**
     * GET /api/mobile/logistics/hostel
     *
     * Returns hostel allocation details for the authenticated student.
     */
    public function hostel(Request $request): JsonResponse
    {
        $user = $request->user();
        $student = Student::where('user_id', $user->id)->first();

        if (!$student) {
            return response()->json(['error' => 'Student record not found.'], 404);
        }

        $allocation = HostelAllocation::where('student_id', $student->student_id)
            ->where('status', 'active')
            ->with(['hostel', 'room'])
            ->first();

        if (!$allocation) {
            return response()->json(['message' => 'No active hostel allocation found.'], 404);
        }

        return response()->json([
            'hostel' => $allocation->hostel->name ?? 'Unknown Hostel',
            'room'   => $allocation->room->room_number ?? 'N/A',
            'bed'    => $allocation->bed_number,
            'date'   => $allocation->allocation_date->toDateString(),
        ]);
    }
}