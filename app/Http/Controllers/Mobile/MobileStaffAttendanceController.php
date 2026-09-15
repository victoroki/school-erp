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
     * GET /api/mobile/attendance/staff/my-status
     *
     * PHASE 5: server-authoritative "where is my day at" for the clock screen,
     * so the client never guesses today's state from cached history rows
     * (timezone drift) or assumes a clock-in succeeded.
     */
    public function myStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $staff = Staff::where('user_id', $user->id)->first();

        if (!$staff) {
            return response()->json(['message' => 'Staff record not found.'], 404);
        }

        $today = now()->toDateString();
        $record = StaffAttendance::where('staff_id', $staff->staff_id)
            ->whereDate('date', $today)
            ->first();

        return response()->json([
            'date'        => $today,
            'status'      => $record?->status,
            'clock_in'    => $record?->time_in ? $record->time_in->toTimeString() : null,
            'clock_out'   => $record?->time_out ? $record->time_out->toTimeString() : null,
            'can_clock_in'  => !$record || !$record->time_in,
            'can_clock_out' => (bool) ($record && $record->time_in && !$record->time_out),
        ]);
    }

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

        // PHASE 5 fix: `status` is a NOT NULL enum with no DB default — the
        // old insert omitted it and MySQL strict mode 500'd on a first-ever
        // clock-in of the day. Clocking in means present (a staff member
        // clocking out/on-leave is guarded by the time_in/time_out checks);
        // an existing row (e.g. created by the web bulk marker) keeps its
        // recorded status.
        if ($record) {
            $record->update(['time_in' => now()]);
        } else {
            StaffAttendance::create([
                'staff_id' => $staff->staff_id,
                'date'     => $today,
                'status'   => 'present',
                'time_in'  => now(),
                'marked_by' => $user->id,
            ]);
        }

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
                // Plain Y-m-d string (not a Carbon timestamp) so history,
                // my-status and the clock-in payload share one date shape.
                'date'        => $a->date?->toDateString(),
                'clock_in'    => $a->time_in ? $a->time_in->toTimeString() : null,
                'clock_out'   => $a->time_out ? $a->time_out->toTimeString() : null,
                'total_hours' => ($a->time_in && $a->time_out) ? $a->time_in->diff($a->time_out)->format('%H:%I') : null,
            ]);

        return response()->json(['history' => $history]);
    }
}