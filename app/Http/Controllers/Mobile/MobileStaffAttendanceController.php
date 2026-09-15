<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MobileStaffAttendanceController extends Controller
{
    /**
     * POST /api/mobile/attendance/staff/clock-in
     *
     * Records clock-in for the authenticated teacher.
     */
    public function clockIn(Request $request): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Staff record not found.'], 404);
        }

        $today = now()->toDateString();
        $record = StaffAttendance::where('staff_id', $staff->staff_id)
            ->whereDate('date', $today)
            ->first();

        if ($record && $record->time_in) {
            return response()->json(['error' => 'Already clocked in for today.'], 422);
        }

        StaffAttendance::updateOrCreate(
            ['staff_id' => $staff->staff_id, 'date' => $today],
            ['time_in' => now()]
        );

        return response()->json(['message' => 'Clocked in successfully.', 'time' => now()->toTimeString()]);
    }

    /**
     * POST /api/mobile/attendance/staff/clock-out
     *
     * Records clock-out for the authenticated teacher.
     */
    public function clockOut(Request $request): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['error' => 'Staff record not found.'], 404);
        }

        $today = now()->toDateString();
        $record = StaffAttendance::where('staff_id', $staff->staff_id)
            ->whereDate('date', $today)
            ->first();

        if (!$record || !$record->time_in) {
            return response()->json(['error' => 'You must clock in first.'], 422);
        }

        if ($record->time_out) {
            return response()->json(['error' => 'Already clocked out for today.'], 422);
        }

        $record->update(['time_out' => now()]);

        return response()->json(['message' => 'Clocked out successfully.', 'time' => now()->toTimeString()]);
    }

    /**
     * GET /api/mobile/attendance/staff/my-history
     *
     * Returns attendance history for the authenticated teacher.
     */
    public function myHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) return response()->json(['error' => 'Staff record not found.'], 404);

        $history = StaffAttendance::where('staff_id', $staff->staff_id)
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get()
            ->map(fn($a) => [
                'date'        => $a->date,
                'clock_in'    => $a->time_in ? $a->time_in->toTimeString() : null,
                'clock_out'   => $a->time_out ? $a->time_out->toTimeString() : null,
                'total_hours' => ($a->time_in && $a->time_out) ? $a->time_in->diff($a->time_out)->format('%H:%I') : null,
            ]);

        return response()->json(['history' => $history]);
    }
}